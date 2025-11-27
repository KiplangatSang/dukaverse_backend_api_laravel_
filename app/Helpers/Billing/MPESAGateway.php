<?php
namespace App\Helpers\Billing;

use App\Models\Retail;
use Exception;
use Illuminate\Http\Request;

class MPESAGateway implements PaymentGatewayContract
{
    protected $gateway;

    public $purposable_type, $purposable_id = null;
    private $currency                       = null;
    private $trans_type                     = null;
    private $discount                       = null;
    private $charge                         = null;

    public function __construct($currency, $trans_type)
    {
        $this->currency = $currency;
        if ($this->currency != "ksh") {
            abort("500");
        }

        $this->trans_type = $trans_type;
    }

    public function getReferenceCode($transaction)
    {
        # code...
        $retail               = $transaction->transactionable->first();
        $retail_trans_id      = $retail->id;
        $transaction_trans_id = $transaction->trans_id;
        $code                 = $retail_trans_id . 'D1U2K3' . $transaction_trans_id;
        return $code;
    }

    public function setCharge($amount)
    {
        # code...
        if (env("APP_DEBUG")) {
            $this->charge = 1;
        } else {
            $this->charge = $amount;
        }
    }

    public function charge($transaction, Retail $retail = null)
    {
        $this->registerUrls();
        $code        = $this->getReferenceCode($transaction);
        $callbackUrl = env('MPESA_TEST_URL') . '/api/v1/mpesa/stkpush/' . Auth::id() . '/dukaverse' . $transaction->transaction_id;
        if ($retail) {
            $callbackUrl = env('MPESA_TEST_URL') . '/api/v1/mpesa/stkpush/retail/' . $retail->id . '/dukaverse' . $transaction->transaction_id;
        }

        info($callbackUrl);

        $result = $this->stkPush($transaction, $callbackUrl);
        return $result;
    }

    public function pay($transaction)
    {
        // return $this->registerUrls();
    }

    public function setDiscount($amount)
    {
        # code...
        $this->discount = $amount;
    }

    public function transfer($transaction)
    {
        return false;
    }
    public function withdraw($transaction)
    {
        return $this->MpesaB2C($transaction);
    }
    public function deposit($transaction)
    {
        $this->registerUrls();
        $retail = $transaction->transactionable->first();

        $retail_trans_id      = $retail->id;
        $transaction_trans_id = $transaction->trans_id;
        $callbackUrl          = env('MPESA_TEST_URL') . '/api/v1/mpesa/stkpush/' . $retail_trans_id . 'D1U2K3' . $transaction_trans_id;
        $result               = $this->stkPush($transaction, $callbackUrl);
        return $result;
    }

    public function getAccessToken()
    {

        $url = env('MPESA_ENV') == 0
        ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
        : 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        try {
            $curl = curl_init($url);
            curl_setopt_array(
                $curl,
                [
                    CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=utf8'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HEADER         => false,
                    CURLOPT_USERPWD        => env('MPESA_CONSUMER_KEY') . ":" . env('MPESA_CONSUMER_SECRET'),
                ]
            );

            $response = json_decode(curl_exec($curl));
            curl_close($curl);
            if (! $response) {
                return false;
            }

            return $response->access_token;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /* get from   safaricom app*/
    public function makeHttp($url, $body)
    {
        $token = $this->getAccessToken();

        if (! $token) {
            return "false";
        }

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL            => $url,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization:Bearer ' . $token],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($body),

            ]

        );
        $curl_response = curl_exec($curl);
        curl_close($curl);
        if (! $curl_response) {
            return "error";
        }

        return $curl_response;
    }

    /*
    Regiser url
     */

    public function registerUrls()
    {
        $validaton    = env('MPESA_TEST_URL') . '/api/v1/mpesa/validation';
        $confirmation = env('MPESA_TEST_URL') . '/api/v1/mpesa/confirmation';
        $body         = [
            'ShortCode'       => env('MPESA_STK_SHORTCODE'),
            "ResponseType"    => "[Cancelled/Completed]",
            'ConfirmationURL' => $confirmation,
            'ValidationURL'   => $validaton,
        ];

        // dd($body);
        $url = env('MPESA_ENV') == 0
        ? 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl'
        : 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl';
        $response = $this->makeHttp($url, $body);
        if (! $response) {
            return "Error Registering Urls";
        }

        return $response;
    }

    //simulateTransaction
    public function simulateTransaction(Request $request)
    {

        $body = [
            'ShortCode'     => env('MPESA_SHORTCODE') != '000' ? env('MPESA_SHORTCODE') : 1111,
            'Msisdn'        => env('MPESA_TEST_MSISDN'),
            'Amount'        => $request->amount,
            'BillRefNumber' => $request->account,
            'CommandID'     => 'CustomerPayBillOnline',
        ];

        $url = env('MPESA_ENV') == 0
        ? 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate'
        : 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';

        $response = $this->makeHttp($url, $body);

        return $response;
    }

    public function stkPush($transaction, $callbackUrl)
    {

        $timeStamp = date('YmdHis');
        //
        $password = env('MPESA_STK_SHORTCODE') . env('MPESA_PASSKEY') . $timeStamp;
        $body     = [
            //business shortcode is store number in buy goods
            'BusinessShortCode' => env('MPESA_STK_SHORTCODE'),
            'Password'          => base64_encode($password),
            'Timestamp'         => $timeStamp,
            // 'TransactionType' => 'CustomerBuyGoodsOnline',
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => $transaction->amount,
            'PartyA'            => $transaction->sender_phone_number,
            'PartyB'            => env('MPESA_SHORTCODE'),
            'PhoneNumber'       => $transaction->sender_phone_number,
            'CallBackURL'       => $callbackUrl,
            'AccountReference'  => $transaction->sender_phone_number,
            'TransactionDesc'   => $transaction->message,
        ];

        $url = env('MPESA_ENV') == 0
        ? 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
        : 'https://api.safaricom.co.ke/stkpush/v1/processrequest';

        $response = $this->makeHttp($url, $body);
        if (! $response) {
            return false;
        }

        $result = json_decode($response);

        return $result;
    }

    public function MpesaB2B()
    {
        $resultUrl = env('MPESA_TEST_URL') . '/api/v1/mpesa/query/result';
        $queryUrl  = env('MPESA_TEST_URL') . '/api/v1/mpesa/query/confirmation';

        //https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest

        $body = [ # code...
            "Initiator"          => env('MPESA_B2C_INITIATOR'),
            "SecurityCredential" => env('MPESA_B2C_SECURITY'),
            "CommandID"          => "TransactionStatusQuery",
            "TransactionID"      => "QFS8HM23GI",
            "PartyA"             => env('MPESA_STK_SHORTCODE'),
            "IdentifierType"     => 4,
            "ResultURL"          => $resultUrl,
            "QueueTimeOutURL"    => $queryUrl,
            "Remarks"            => "Paid",
            "Occassion"          => "Paid",
        ];

        $url = env('MPESA_ENV') == 0
        ? 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query'
        : 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query';

        // dd($body);

        $response = $this->makeHttp($url, $body);
        if (! $response) {
            return "Error Reversing the money";
        }

        return $response;
    }

    public function MpesaB2C($transaction)
    {
        $code      = $this->getReferenceCode($transaction);
        $resultUrl = env('MPESA_TEST_URL') . '/api/v1/mpesa/query/result/' . $code;
        $queryUrl  = env('MPESA_TEST_URL') . '/api/v1/mpesa/query/confirmation/' . $code;
        //

        $body = [ # code..
            "InitiatorName"      => "testapi",
            "SecurityCredential" => "KM32i6ktcKhGh7+rA+KoycPmAFCTcWSB/cc4USTD3mNDECvEyZIKtLelCz46VFNVl9x7Bs85wacpjb3AN45UoTonetCKqrtkd/b02JHrWJVrVEJR9R0yQ4f35KJbTVyyU2NdT0nnq20i4bwKsjpERtQVhEVih1luk5nVM13zs+JfZfT9WhMiixb6SWLxAPBXf/dH58kW7h6SGoFbc+BKu9RuydV+Xx1ehlc0XhLiiQMrqczIcqlWDAbFoV0rpnA9s/WPlrJykFUVUqSUemwt5aZkzvK+A28VqFjGb3O4jyVZy0AB4hThTsSiBdmGaMMZ9EjGHuYdzWMIyMG838xS6A==",
            "CommandID"          => "BusinessPayment",
            "Amount"             => $transaction->amount,
            "PartyA"             => env('MPESA_SHORTCODE'),
            "PartyB"             => "$transaction->receiver_phone_number",
            "Remarks"            => $transaction->message,
            "QueueTimeOutURL"    => $queryUrl,
            "ResultURL"          => $resultUrl,
            "Occassion"          => $transaction->purpose,
        ];

        $url = env('MPESA_ENV') == 0
        ? 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest'
        : 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';

        // dd($body);

        $response = $this->makeHttp($url, $body);
        if (! $response) {
            return "Error Reversing the money";
        }

        return (array) @json_decode($response);
    }

    public function transactionQuery()
    {

        $resultUrl = env('MPESA_TEST_URL') . '/api/v1/mpesa/query/result';
        $queryUrl  = env('MPESA_TEST_URL') . '/api/v1/mpesa/query/confirmation';

        //

        $body = [ # code...
                           // "Initiator" =>  env('MPESA_B2C_INITIATOR'),
                           // "SecurityCredential" => env('MPESA_B2C_SECURITY'),
            "Initiator"          => "apitest361",
            "SecurityCredential" => "iDIurkCG4r9F7lCCNNkctseY9MgiddL6F1VmmJH1DSk5n5U4HPkE3gj1+UFNQ+3MUIzIkR7EgBov9/rqzeXJVb8JeTTD/10XSdTU5eJJeWsqI1VSXpIe/j4xa7HNuezxRJ/KtTWu3bdq9heozyXMn/5ErogjfwIlfQ4Bkhw8+9SEGoNkRA95Idp1PSg/ElPOVnrKuPFTf0HS/iTmuKvVBkY6oAFSObVx7DmqqoL2K4P8ZNh3EDVfQtCQGUnF1OLOab5X46wqVW5p7UJ/5kVFdN8Yuw172VNOjyNILjUXwqqAhS4WKXC28cXd4faYrG5E5AQfGGjNIlYRqWse8w23fw==",
            "CommandID"          => "TransactionStatusQuery",
            "TransactionID"      => "QFS8HM23GI",
            "PartyA"             => env('MPESA_STK_SHORTCODE'),
            "IdentifierType"     => 11,
            "ResultURL"          => $resultUrl,
            "QueueTimeOutURL"    => $queryUrl,
            "Remarks"            => "Paid",
            "Occassion"          => "Paid",
        ];

        //   echo(json_encode($body));

        $url = env('MPESA_ENV') == 0
        ? 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query'
        : 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query';

        // dd($body);

        $response = $this->makeHttp($url, $body);
        if (! $response) {
            return "Error Reversing the money";
        }

        return $response;
    }
}
