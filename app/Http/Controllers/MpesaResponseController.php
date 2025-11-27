<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MpesaResponseController extends Controller
{

    public function validation(Request $request)
    {
        Log::info('Validation endpoint hit');
        Log::info($request->all());
        return true;
    }

    public function confirmation(Request $request)
    {
        Log::info('confirmation endpoint hit');
        Log::info($request->all());
        return true;
    }

    public function reversal(Request $request)
    {
        Log::info(' reversal endpoint hit');
        Log::info($request->all());

        return true;
    }

    public function stkPushResponse($user_id, $transaction_id, Request $request)
    {
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
                    $paymentrepo            = new PaymentDoneRepository($transaction);
                    $finalizepayment_result = $paymentrepo->index();
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

    }
    public function formatMPESAResponse($request)
    {
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
        }

    }

    public function retailSTKPushResponse($user_id, $transaction_id, Request $request)
    {
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
                    $paymentrepo            = new PaymentDoneRepository($transaction);
                    $finalizepayment_result = $paymentrepo->index();
                    info($finalizepayment_result);

                    if (! $finalizepayment_result) {
                        return false;
                    }

                    // PaymentDone::dispatch($transaction);
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
    }

    public function queryResult($id, Request $request)
    {
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
            $paymentrepo            = new PaymentDoneRepository($transaction);
            $finalizepayment_result = $paymentrepo->index();
            info($finalizepayment_result);

            if (! $finalizepayment_result) {
                return false;
            }

            PaymentDone::dispatch($transaction);
        } else {
            info("There is an error desc : " . $result['ResultDesc']);
            return redirect("/testmpesa")->with('error', $result['ResultDesc']);
        }

        return true;
    }

    public function queryConfirmation($id, Request $request)
    {
        Log::info(' queryConfirmation endpoint hit');
        Log::info($request->all());

        return true;
    }

}
