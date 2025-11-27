<?php
namespace App\Http\Controllers;

use App\Helpers\Billing\Charges;
use App\Helpers\Billing\PaymentGatewayContract;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\ReceiptController;
use App\Http\Resources\ApiResource;
use App\Http\Resources\StoreFileResource;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Retail;
use App\Models\SaleTransaction;
use App\Models\Transaction;
use App\Repositories\ItemRepository;
use App\Repositories\SalesRepository;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalePaymentController extends BaseController
{




     public function __construct(
        ApiResource $apiResource,
        private readonly PaymentService $paymentService
    ) {
        parent::__construct($apiResource);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PaymentGatewayContract $payment, Charges $charges)
    {
        //
        try {

            $request->validate([
                'transaction' => ['required'],
            ]);
            $validator = Validator::make([$request->transaction, 'transaction'], [
                [
                    'transaction_id' => ['required'],
                    'amount'         => ['required'],
                    'gateway'        => ['required'],
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError('Bad request', ['errors' => $validator->errors()]);
            }

            $transaction_model = new Transaction();
            $retail            = $this->getAccount();
            $sale_transaction  = $this->getAccount()->saleTransactions()->where('transaction_id', $request->transaction['transaction_id'])->first();
            $cost              = 0;
            $transaction       = $this->paymentService->storeTransaction(
                $request->gateway,
                null,
                1,
                $request->customer['phone_number'] ?? '',
                $this->user()->phone_number,
                $request->transaction['amount'],
                $transaction_model->getTransactionType($request->gateway),
                $cost,
                "SALES",
                "Retail Goods Payment",
                $sale_transaction->id
            );

            if (! $sale_transaction) {
                return $this->sendError($sale_transaction, "Transaction not found", 500);
            }
            $balance = $request->paid_amount - $sale_transaction->transaction_amount;
            if ($request->gateway == "CASH") {
                $result = $retail->saleTransactions()->where("transaction_id", $request->transaction['transaction_id'])->update(
                    [
                        "pay_status"  => $sale_transaction->transaction_amount <= $request->transaction['amount'] ? true : false,
                        "paid_amount" => $request->paid_amount,
                        "balance"     => $balance,
                    ]
                );

                if (! $result) {
                    return $this->sendError($sale_transaction, "Sorry! Could not store the sale stransaction");
                }

            } else if ($request->gateway == "MPESA") {

                $request->validate([
                    "customer" => "required",
                ]);
                Validator::make($request->customer, [
                    [
                        'phone_number' => ['required'],
                    ],
                ]);

                $charge = $charges->all($request->gateway, $request->transaction, $request->customer);
                $result = $payment->charge($transaction);
                if (! $result) {
                    return $this->sendError($sale_transaction, "Could not send request");
                }

                if (array_key_exists('errorCode', (array) $result)) {
                    return $this->sendError($transaction, $result);
                }
            }
            if (! $transaction) {
                return $this->sendError($transaction, "Sorry! Could not store transaction");
            }

            $sale_transaction = SaleTransaction::where('id', $sale_transaction->id)->first();
            $retail           = $this->getAccount();
            $retail           = Retail::where('id', $retail['id'])->first();
            $user             = $this->user();
            $customer         = Customer::where('phone_number', $request->phone_number)->first();

            $receiptController = new ReceiptController();
            $invoice           = $receiptController->generateInvoice
                ($retail, $customer, $user, $transaction,
                $sale_transaction, Invoice::CASH_TYPE
            );

            $receipt_type = Receipt::CASH_TYPE;

            $employee = null;
            if ($user->is_retail_employee) {
                $employee = $user->employee;
            }

            $receipt          = $receiptController->generateReceipt($retail, $user, $customer, $sale_transaction, $transaction, $invoice, $receipt_type);
            $transaction_data = $this->generateReceiptPDF($retail, $sale_transaction, $customer, $employee, $receipt, $invoice);

            return $this->sendResponse([
                "transaction"      => $transaction,
                "sale_transaction" => $sale_transaction,
                "receipt"          => $receipt,
                "invoice"          => $invoice,
                'customer'         => $customer,
                "items"            => $transaction_data['items'],
                "subtotal"         => $transaction_data['subtotal'],
                "discount"         => $transaction_data['discount'],
                "vat"              => $transaction_data['vat'],
                "total"            => $transaction_data['total'],
                "paid"             => $transaction_data["paid"],
                "change"           => $transaction_data['change'],
            ], "The transaction is successfull");
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), "Sorry! Error, could not store transaction");
        }
    }

    public function generateReceiptPDF(Retail $retail, SaleTransaction $saleTransaction, Customer $customer = null, Employee $employee = null, Receipt $receipt, Invoice $invoice)
    {

        $item_repository = new ItemRepository($retail);
        $sales_items     = $item_repository->getSalesItems($retail, $saleTransaction);

        $sale_repository = new SalesRepository($retail);
        $sales_totals    = $sale_repository->getReceiptTotals($retail, $saleTransaction);
        $data            = [
            'items'    => $sales_items,
            "customer" => $customer,
            "employee" => $employee,
            "receipt"  => $receipt,
            "invoice"  => $invoice,
            'retail'   => $retail,
            'subtotal' => $sales_totals['sub_total'],
            'vat'      => $sales_totals['VAT'],
            'discount' => $sales_totals['total_discount'],
            'total'    => $sales_totals['total'],
            'paid'     => $sales_totals['paid'],
            'change'   => (double) $sales_totals['change'],
        ];

        return $data;
    }

    public function checkForPayment(Request $request)
    {
        $payment_transaction = $request->transaction;
        $transaction         = $this->getAccount()->accountTransactions()->where('transaction_id', $payment_transaction['transaction_id'])
            ->where('amount', $payment_transaction['amount'])->first();

        if (! $transaction) {
            return $this->sendError($transaction, 'error, could not find the transaction.');
        }

        return $this->sendResponse($transaction, ' success, transaction found.');

    }

    public function closeTransaction($trans_id)
    {
        # code...
        $transaction = $this->getAccount()->salesTransactions()->where('transaction_id', $trans_id)->first();
        if (! $transaction) {
            return $this->sendError("error", $transaction);
        }

        if ($transaction->balance < 0) {
            return $this->sendError("error", $transaction);
        }

        // dd($transaction);
        $retail       = $this->getAccount();
        $transactions = $retail->salesTransactions()->updateOrCreate(
            ["transaction_id" => $trans_id],
            [
                "on_hold"    => false,
                "pay_status" => true,
                "is_active"  => false,
            ]
        );
        $transaction = $this->getAccount()->salesTransactions()->where('transaction_id', $transactions->transaction_id)->first();

        $revenue_result = $this->saveRevenue($transaction->paid_amount);
        if (! $revenue_result) {
            return "false";
        }

        return $this->sendResponse($transaction, "The transaction is successfull");
    }

}
