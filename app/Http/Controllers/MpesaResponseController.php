<?php

namespace App\Http\Controllers;

use App\Events\PaymentDone;
use App\Http\Resources\ApiResource;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentDoneService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaResponseController extends BaseController
{

    public function __construct(
        protected ApiResource $apiResource,
        protected readonly PaymentDoneService $paymentService

    ) {
        parent::__construct($apiResource);
    }

    public function validation(Request $request)
    {
        try{
        Log::info('Validation endpoint hit');
        Log::info($request->all());
        return true;

        } catch (Exception $ex) {
                info($ex->getMessage());
                return $this->apiResource->error('Server error.', 500);
                }
    }

    public function confirmation(Request $request)
    {
        try{
        Log::info('confirmation endpoint hit');
        Log::info($request->all());
        return true;

        } catch (Exception $ex) {
                info($ex->getMessage());
                return $this->apiResource->error('Server error.', 500);
                }
    }

    public function reversal(Request $request)
    {
        try{
        Log::info(' reversal endpoint hit');
        Log::info($request->all());

        return true;

        } catch (Exception $ex) {
                info($ex->getMessage());
                return $this->apiResource->error('Server error.', 500);
                }
    }




    public function stkPushResponse($user_id, $transaction_id, Request $request)
    {
        try{
        Log::info('STK endpoint hit');

        $transaction_id = $this->mpesaResponse($user_id)[1];

        $response = null;

        Log::info('stkPushResponse Endpoint Hit');
        Log::info($request->Body['stkCallback']['ResultCode']);

        $response['desc'] = $request->Body['stkCallback']['ResultDesc'];
        $response['code'] = $request->Body['stkCallback']['ResultCode'];

        if ($request->Body['stkCallback']) {
            if ($request->Body['stkCallback']['ResultCode'] == 0) {
                Log::info($request->Body['stkCallback']['ResultCode']);
                if ($request->Body['stkCallback']['CallbackMetadata']) {

                    $callback     = $request->Body['stkCallback']['CallbackMetadata'];
                    $responsedata = $this->formatMPESAResponse($callback);

                    $transaction = Transaction::updateOrCreate(
                        [
                            'transaction_id' => $transaction_id,
                        ],
                        [
                            'transaction_response' => $response['desc'],
                            'transaction_meta'     => json_encode($responsedata),
                            'status'               => true,
                        ]
                    );

                    $finalizepayment_result = $this->paymentService->index($transaction);
                    info($finalizepayment_result);

                    if (! $finalizepayment_result) {
                        return false;


                    }

                    PaymentDone::dispatch($transaction);
                    return true;


                } else {
                    info("request->Body['stkCallback']['CallbackMetadata']");
                    return false;


                }
            } else {
                info("request->Body['stkCallback']['ResultCode'] == 0");
                return false;


            }
        } else {
            info("request->Body['stkCallback']");
            return false;
        }

        } catch (Exception $ex) {
                info($ex->getMessage());
                return $this->apiResource->error('Server error.', 500);
                }
    }

  /**
     * @OA\Get(
     *     path="/api/v1/payments/mpesa/status/{transaction_id}",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     summary="Check M-PESA payment status",
     *     description="Polls the status of an M-PESA STK Push payment.",
     *     operationId="pollMpesaPaymentResult",
     *     @OA\Parameter(
     *         name="transaction_id",
     *         in="path",
     *         required=true,
     *         description="M-PESA Checkout Request ID",
     *         @OA\Schema(type="string", example="ws_CO_123456789")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment status retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="status", type="string", example="completed"),
     *                 @OA\Property(property="phone", type="string", example="254712345678"),
     *                 @OA\Property(property="amount", type="number", example=100.00),
     *                 @OA\Property(property="receipt_number", type="string", example="QHJ123456789")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment request not found"
     *     )
     * )
     */
    public function pollMpesaPaymentResult($payment_checkout_id)
    {

        try {
            $result =  $this->paymentService->pollMpesaPaymentResult($payment_checkout_id);

            if (isset($result["error"])) {
                return $this->apiResource->error($result["message"], $result["httpCode"], $result["error"]);

            }
        } catch (Exception $ex) {
            return $this->apiResource->error("Server error", 500, $ex->getMessage());
        }
    }
    public function formatMPESAResponse($request)
    {
        try{
        Log::info($request->Body['stkCallback']['ResultCode']);
        if ($request->Body['stkCallback']['CallbackMetadata']) {

            $callback = $request->Body['stkCallback']['CallbackMetadata'];
            if ($callback['Item'][0]['Name'] == 'Amount') {
                $response['amount'] = $callback['Item'][0]['Value'];
            }
            if ($callback['Item'][1]['Name'] == 'MpesaReceiptNumber') {
                $response['mpesaReceiptNumber'] = $callback['Item'][1]['Value'];
            }

            if ($callback['Item'][2]['Name'] == 'TransactionDate') {
                $response['transactionDate'] = $callback['Item'][2]['Value'];
            }
            if ($callback['Item'][3]['Name'] == 'PhoneNumber') {
                $response['phoneNumber'] = $callback['Item'][3]['Value'];
            }
            return $response;
        } else {
            info("request->Body['stkCallback']['CallbackMetadata']");
            return false;


        }  } catch (Exception $ex) {
                info($ex->getMessage());
                return $this->apiResource->error('Server error.', 500);
                }
    }

    public function retailSTKPushResponse($user_id, $transaction_id, Request $request)
    {
        try{
        # code...
        Log::info('STK endpoint hit');

        $user = User::where('id', $user_id)->first();

        $transaction = $this->mpesaResponse($transaction_id)[1];

        $response = null;

        Log::info('stkPushResponse Endpoint Hit');
        Log::info($request->Body['stkCallback']['ResultCode']);

        $response['desc'] = $request->Body['stkCallback']['ResultDesc'];
        $response['code'] = $request->Body['stkCallback']['ResultCode'];

        if ($request->Body['stkCallback']) {
            if ($request->Body['stkCallback']['ResultCode'] == 0) {
                Log::info($request->Body['stkCallback']['ResultCode']);
                if ($request->Body['stkCallback']['CallbackMetadata']) {

                    $callback = $request->Body['stkCallback']['CallbackMetadata'];
                    if ($callback['Item'][0]['Name'] == 'Amount') {
                        $response['amount'] = $callback['Item'][0]['Value'];
                    }
                    if ($callback['Item'][1]['Name'] == 'MpesaReceiptNumber') {
                        $response['mpesaReceiptNumber'] = $callback['Item'][1]['Value'];
                    }

                    if ($callback['Item'][2]['Name'] == 'TransactionDate') {
                        $response['transactionDate'] = $callback['Item'][2]['Value'];
                    }
                    if ($callback['Item'][3]['Name'] == 'PhoneNumber') {
                        $response['phoneNumber'] = $callback['Item'][3]['Value'];
                    }

                    $transaction->updateOrCreate(
                        [
                            'transaction_id' => $transaction->transaction_id,
                        ],
                        [
                            'transaction_response' => $response['desc'],
                            'transaction_meta'     => json_encode($response),
                            'status'               => true,
                        ]
                    );
                    $finalizepayment_result = $this->paymentService->index($transaction);
                    info($finalizepayment_result);

                    if (! $finalizepayment_result) {
                        return false;

                    }

                    PaymentDone::dispatch($transaction);
                    return true;


                } else {
                    info("request->Body['stkCallback']['CallbackMetadata']");
                    return false;


                }
            } else {
                info("request->Body['stkCallback']['ResultCode'] == 0");
                return false;


            }
        } else {
            info("request->Body['stkCallback']");
            return false;


        } } catch (Exception $ex) {
                info($ex->getMessage());
                return $this->apiResource->error('Server error.', 500);
                }
    }

    public function queryResult($id, Request $request)
    {
        try{
        Log::info(' queryResult endpoint hit');
        Log::info($request->all());
        $result                              = $request->all()['Result'];
        $response                            = null;
        $name                                = null;
        $amount                              = null;
        $B2CUtilityAccountAvailableFunds     = null;
        $B2CWorkingAccountAvailableFunds     = null;
        $B2CChargesPaidAccountAvailableFunds = null;

        $transaction = $this->mpesaResponse($id)[1];

        if ($result['ResultCode'] == 0) {
            $resultparams = $result['ResultParameters'];
            $resultparam1 = $resultparams['ResultParameter'];
            $response     = $resultparam1;
            if ($resultparam1['0']['Key'] = "TransactionAmount") {
                $amount = $resultparam1['0']['Value'];
            }
            if ($resultparam1['3']['Key'] = "B2CChargesPaidAccountAvailableFunds") {
                $B2CChargesPaidAccountAvailableFunds = $resultparam1['3']['Value'];
            }
            if ($resultparam1['4']['Key'] = "ReceiverPartyPublicName") {
                $name = $resultparam1['4']['Value'];
            }

            if ($resultparam1['6']['Key'] = "B2CUtilityAccountAvailableFunds") {
                $B2CUtilityAccountAvailableFunds = $resultparam1['6']['Value'];
            }
            if ($resultparam1['7']['Key'] = "B2CWorkingAccountAvailableFunds") {
                $B2CWorkingAccountAvailableFunds = $resultparam1['7']['Value'];
            }
            $transaction->updateOrCreate(
                [
                    'transaction_id' => $transaction->transaction_id,
                ],
                [
                    'transaction_response' => $result['ResultDesc'],
                    'transaction_meta'     => json_encode($response),
                    'status'               => true,
                ]
            );
            $finalizepayment_result = $this->paymentService->index($transaction);
            info($finalizepayment_result);

            if (! $finalizepayment_result) {
                return false;


            }

            PaymentDone::dispatch($transaction);
        } else {
            info("There is an error desc : " . $result['ResultDesc']);
            $transaction->updateOrCreate(
                [
                    'transaction_id' => $transaction->transaction_id,
                ],
                [
                    'transaction_response' => $result['ResultDesc'],
                    'transaction_meta'     => json_encode($response),
                    'status'               => false,
                ]
            );
            return $this->apiResource->error("Payment failed", 500);


        }

        return true;

        } catch (Exception $ex) {
                info($ex->getMessage());
                return $this->apiResource->error('Server error.', 500);
                }
    }

    public function queryConfirmation($id, Request $request)
    {
        try{
        Log::info(' queryConfirmation endpoint hit');
        Log::info($request->all());

        return true;

        } catch (Exception $ex) {
                info($ex->getMessage());
                return $this->apiResource->error('Server error.', 500);
                }
    }
}
