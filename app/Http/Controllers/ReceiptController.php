<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Retail;
use App\Models\SaleTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\ItemRepository;
use App\Repositories\SalesRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * @OA\Tag(
 *     name="Receipts",
 *     description="API Endpoints for generating receipts and invoices"
 * )
 */
class ReceiptController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/v1/receipts/print-from-terminal",
     *     tags={"Receipts"},
     *     summary="Generate and print a receipt from POS terminal",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"retail_id","sale_transaction_id","transaction_id","customer_id","type","invoice_id"},
     *             @OA\Property(property="retail_id", type="integer", example=1),
     *             @OA\Property(property="sale_transaction_id", type="integer", example=12),
     *             @OA\Property(property="transaction_id", type="integer", example=34),
     *             @OA\Property(property="customer_id", type="integer", example=56),
     *             @OA\Property(property="type", type="string", example="cash"),
     *             @OA\Property(property="invoice_id", type="integer", example=78)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Receipt generated successfully"),
     *     @OA\Response(response=400, description="Validation failed")
     * )
     */
    public function printReceiptFromTerminal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'retail_id'           => 'required|exists:retails,id',
            'sale_transaction_id' => 'required|exists:sale_transactions,id',
            'transaction_id'      => 'required|exists:transactions,id',
            'customer_id'         => 'required|exists:customers,id',
            'type'                => 'required|string',
            'invoice_id'          => 'required|exists:invoices,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad Request', ["errors" => $validator->errors()]);
        }

        $retail          = Retail::find($request->retail_id);
        $saleTransaction = SaleTransaction::find($request->sale_transaction_id);
        $transaction     = Transaction::find($request->transaction_id);
        $customer        = Customer::find($request->customer_id);
        $user            = $this->user();
        $invoice         = Invoice::find($request->invoice_id);

        $result = $this->generateReceipt(
            $retail, $user, $customer,
            $saleTransaction, $transaction, $invoice, $request->type
        );

        if (! $result) {
            return $this->sendError('Bad Request', ["errors" => ['receipt' => ['Receipt could not be generated']]]);
        }

        return $this->sendResponse(["receipt" => $result], "Receipt generated successfully.");
    }

    /**
     * Generate and store a new receipt for a sale transaction.
     *
     * @param Retail $retail
     * @param User $user
     * @param Customer|null $customer
     * @param SaleTransaction $saleTransaction
     * @param Transaction $transaction
     * @param Invoice $invoice
     * @param string $type
     * @return Receipt|false
     */
    public function generateReceipt(
        Retail $retail,
        User $user,
        Customer $customer = null,
        SaleTransaction $saleTransaction,
        Transaction $transaction,
        Invoice $invoice,
        $type
    ) {
        $receipt_number = "";

        do {
            $receipt_number = Receipt::generateReceiptNumber();
            $validator      = Validator::make(['receipt_number' => $receipt_number], [
                'receipt_number' => 'required|unique:receipts,receipt_number',
            ]);
        } while ($validator->fails());

        $receipt = $retail->receipts()->create([
            'user_id'             => $user->id,
            'customer_id'         => $customer->id ?? null,
            'sale_transaction_id' => $saleTransaction->id,
            'transaction_id'      => $transaction->id,
            'invoice_id'          => $invoice->id,
            'type'                => $type,
            'receipt_number'      => $receipt_number,
        ]);

        return $receipt ?: false;
    }

    /**
     * Generate and store a new invoice for a transaction.
     *
     * @param Retail $retail
     * @param Customer|null $customer
     * @param User $user
     * @param Transaction $transaction
     * @param SaleTransaction $saleTransaction
     * @param string $type
     * @return Invoice|false
     */
    public function generateInvoice(
        Retail $retail,
        $customer = null,
        User $user,
        Transaction $transaction,
        SaleTransaction $saleTransaction,
        $type
    ) {
        $invoice_number = "";

        do {
            $invoice_number = Invoice::generateInvoiceNumber();
            $validator      = Validator::make(['invoice_number' => $invoice_number], [
                'invoice_number' => 'required|unique:invoices,invoice_number',
            ]);
        } while ($validator->fails());

        $invoice = $retail->invoices()->create([
            "customer_id"         => $customer->id ?? null,
            "user_id"             => $user->id,
            "transaction_id"      => $transaction->id,
            "sale_transaction_id" => $saleTransaction->id,
            "type"                => $type,
            "invoice_number"      => $invoice_number,
            "invoice_amount"      => $transaction->total_amount,
            "status"              => Invoice::PENDING_STATUS,
            "is_active"           => true,
        ]);

        return $invoice ?: false;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/receipts/pdf/{sale_transaction_id}",
     *     tags={"Receipts"},
     *     summary="Generate and download a PDF receipt",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="sale_transaction_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="PDF generated and returned"),
     *     @OA\Response(response=404, description="Sale transaction not found")
     * )
     */
    public function generateReceiptPDF(
        Retail $retail,
        SaleTransaction $saleTransaction,
        Customer $customer = null,
        Employee $employee,
        Receipt $receipt,
        Invoice $invoice
    ) {
        $item_repository = new ItemRepository($retail);
        $sales_items     = $item_repository->getSalesItems($retail, $saleTransaction);

        $sale_repository       = new SalesRepository($retail);
        $sale_transaction_data = $sale_repository->getReceiptTotals($retail, $saleTransaction);

        $data = [
            'items'    => $sales_items,
            "customer" => $customer,
            "employee" => $employee,
            "receipt"  => $receipt,
            "invoice"  => $invoice,
            'retail'   => $retail,
            'subtotal' => $sale_transaction_data['sub_total'],
            'vat'      => $sale_transaction_data['VAT'],
            'total'    => $sale_transaction_data['total'],
            'paid'     => $sale_transaction_data['paid'],
            'change'   => $sale_transaction_data['change'],
        ];

        $data['qrcode'] = QrCode::size(100)->generate(json_encode([
            'receipt_number' => $receipt->receipt_number,
            'invoice_number' => $invoice->invoice_number,
        ]));

        $pdf = Pdf::loadView('pdfs.receipt', $data)->setPaper('A6', 'portrait');
        return $pdf->download('receipt.pdf');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/receipts/print",
     *     tags={"Receipts"},
     *     summary="Print receipt (placeholder)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Receipt print initiated")
     * )
     */
    public function printReceipt()
    {
        // Placeholder for actual printing logic
        return $this->sendResponse([], "Receipt printing initiated.");
    }
}
