<?php
namespace App\PaymentGateways\B2BPayments;

use App\PaymentGateways\IpayData;

class IpayPayments extends IpayData
{
    private function __construct(public $amount, public $user, public $narration)
    {
        $this->baseUrl = env('IPAY_BASE_URL');
    }

    //http request
    public function makeHttp($url, $params)
    {

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL            => $url,
                CURLOPT_HEADER         => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $params,

            ]

        );
        $curl_response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($curl_response);
        return $response;
    }

    //sending money

    public function sendMoney($amount, $account, $phone_no)
    {
        // available channels = mpesapaybill $ mpesatill
        $channel = "pesalink";
        $data    = $this->ipayData($amount, $account, $phone_no);

        $vid       = $data['vid'];
        $reference = $data['reference'];
        $account   = $data['account'];
        $amount    = $data['amount'];

        $key = 'demoCHANGED';

        $datastring = $vid . $reference . $account . $amount;

        $generated_hash = hash_hmac('sha256', $datastring, $key);

        $body              = [];
        $body['vid']       = $vid;
        $body['reference'] = $reference;
        $body['account']   = $account;
        $body['amount']    = $amount;
        $body['hash']      = $generated_hash;

        $params = http_build_query($body, '', '&');

        //dd($params);

        $url = $this->baseUrl . "external/send/" . $channel;

        $responseData = $this->makeHttp($url, $params);

        return $responseData;
    }

    public function transactionStatus($amount, $account, $phone_no)
    {
        $data = $this->ipayData($amount, $account, $phone_no);

        $vid       = $data['vid'];
        $reference = $data['reference'];

        $key = 'demoCHANGED';

        $datastring = $vid . $reference;

        $generated_hash = hash_hmac('sha256', $datastring, $key);

        $body              = [];
        $body['vid']       = $vid;
        $body['reference'] = $reference;
        $body['hash']      = $generated_hash;

        $params = http_build_query($body, '', '&');

        //dd($params);

        $url = $this->baseUrl . "transaction/status";

        $responseData = $this->makeHttp($url, $params);

        return $responseData;
    }
}
