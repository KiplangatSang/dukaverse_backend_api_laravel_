<?php

namespace App\PaymentGateways\C2BPayments;

use App\PaymentGateways\IpayData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MpesaSTKPush extends IpayData
{
    private $consumerKey;
    private $consumerSecret;
    private $shortcode;
    private $passkey;
    // private $baseUrl;

    public function __construct()
    {
        $this->consumerKey = config('services.mpesa.consumer_key');
        $this->consumerSecret = config('services.mpesa.consumer_secret');
        $this->shortcode = config('services.mpesa.shortcode');
        $this->passkey = config('services.mpesa.passkey');
        $this->baseUrl = config('services.mpesa.base_url', 'https://sandbox.safaricom.co.ke');
    }

    /**
     * Generate M-PESA access token
     *
     * @return string|null
     */
    private function generateAccessToken()
    {
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials',
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . $credentials,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            Log::error('M-PESA access token generation failed', ['error' => $err]);
            return null;
        }

        $result = json_decode($response);
        return $result->access_token ?? null;
    }

    /**
     * Initiate STK Push
     *
     * @param string $phoneNumber
     * @param float $amount
     * @param string $accountReference
     * @param string $transactionDesc
     * @return array
     */
    public function initiateSTKPush($phoneNumber, $amount, $accountReference, $transactionDesc = 'Payment')
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                throw new \Exception('Failed to generate access token');
            }

            // Format phone number
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);

            // Generate timestamp and password
            $timestamp = date('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int) $amount,
                'PartyA' => $phoneNumber,
                'PartyB' => $this->shortcode,
                'PhoneNumber' => $phoneNumber,
                'CallBackURL' => config('app.url') . '/api/v1/payments/mpesa/callback',
                'AccountReference' => $accountReference,
                'TransactionDesc' => $transactionDesc
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->baseUrl . '/mpesa/stkpush/v1/processrequest',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                Log::error('M-PESA STK Push failed', ['error' => $err, 'phone' => $phoneNumber]);
                return [
                    'success' => false,
                    'error' => $err
                ];
            }

            $result = json_decode($response, true);

            if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
                // Store checkout request ID for polling
                $checkoutRequestId = $result['CheckoutRequestID'];
                Cache::put('mpesa_stk_' . $checkoutRequestId, [
                    'phone' => $phoneNumber,
                    'amount' => $amount,
                    'account_reference' => $accountReference,
                    'status' => 'pending',
                    'timestamp' => now()
                ], now()->addMinutes(15)); // Cache for 15 minutes

                Log::info('M-PESA STK Push initiated successfully', [
                    'checkout_request_id' => $checkoutRequestId,
                    'phone' => $phoneNumber,
                    'amount' => $amount
                ]);

                return [
                    'success' => true,
                    'checkout_request_id' => $checkoutRequestId,
                    'response_code' => $result['ResponseCode'],
                    'response_description' => $result['ResponseDescription'],
                    'customer_message' => $result['CustomerMessage']
                ];
            } else {
                Log::error('M-PESA STK Push failed', [
                    'response' => $result,
                    'phone' => $phoneNumber
                ]);

                return [
                    'success' => false,
                    'error' => $result['ResponseDescription'] ?? 'Unknown error',
                    'response' => $result
                ];
            }

        } catch (\Exception $e) {
            Log::error('M-PESA STK Push exception', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Query STK Push payment status
     *
     * @param string $checkoutRequestId
     * @return array
     */
    public function querySTKStatus($checkoutRequestId)
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                throw new \Exception('Failed to generate access token');
            }

            $timestamp = date('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->baseUrl . '/mpesa/stkpushquery/v1/query',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                return [
                    'success' => false,
                    'error' => $err
                ];
            }

            $result = json_decode($response, true);

            // Update cache with latest status
            $cachedData = Cache::get('mpesa_stk_' . $checkoutRequestId, []);
            if (!empty($cachedData)) {
                $cachedData['status'] = $this->mapResponseCode($result['ResponseCode'] ?? null);
                $cachedData['last_checked'] = now();
                Cache::put('mpesa_stk_' . $checkoutRequestId, $cachedData, now()->addMinutes(15));
            }

            return [
                'success' => true,
                'response_code' => $result['ResponseCode'] ?? null,
                'response_description' => $result['ResponseDescription'] ?? null,
                'result_code' => $result['ResultCode'] ?? null,
                'result_desc' => $result['ResultDesc'] ?? null,
                'status' => $this->mapResponseCode($result['ResponseCode'] ?? null)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle M-PESA callback
     *
     * @param array $callbackData
     * @return array
     */
    public function handleCallback($callbackData)
    {
        try {
            Log::info('M-PESA callback received', $callbackData);

            if (!isset($callbackData['Body']['stkCallback'])) {
                return ['success' => false, 'error' => 'Invalid callback data'];
            }

            $stkCallback = $callbackData['Body']['stkCallback'];
            $checkoutRequestId = $stkCallback['CheckoutRequestID'];
            $resultCode = $stkCallback['ResultCode'];
            $resultDesc = $stkCallback['ResultDesc'];

            // Update cache
            $cachedData = Cache::get('mpesa_stk_' . $checkoutRequestId, []);
            if (!empty($cachedData)) {
                $cachedData['status'] = $resultCode == 0 ? 'completed' : 'failed';
                $cachedData['result_code'] = $resultCode;
                $cachedData['result_desc'] = $resultDesc;
                $cachedData['callback_received'] = now();

                if ($resultCode == 0 && isset($stkCallback['CallbackMetadata']['Item'])) {
                    // Extract transaction details
                    foreach ($stkCallback['CallbackMetadata']['Item'] as $item) {
                        switch ($item['Name']) {
                            case 'Amount':
                                $cachedData['amount'] = $item['Value'];
                                break;
                            case 'MpesaReceiptNumber':
                                $cachedData['receipt_number'] = $item['Value'];
                                break;
                            case 'TransactionDate':
                                $cachedData['transaction_date'] = $item['Value'];
                                break;
                            case 'PhoneNumber':
                                $cachedData['phone_number'] = $item['Value'];
                                break;
                        }
                    }
                }

                Cache::put('mpesa_stk_' . $checkoutRequestId, $cachedData, now()->addHours(24));
            }

            return [
                'success' => true,
                'checkout_request_id' => $checkoutRequestId,
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'status' => $resultCode == 0 ? 'completed' : 'failed'
            ];

        } catch (\Exception $e) {
            Log::error('M-PESA callback processing failed', [
                'error' => $e->getMessage(),
                'callback_data' => $callbackData
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payment status from cache
     *
     * @param string $checkoutRequestId
     * @return array
     */
    public function getPaymentStatus($checkoutRequestId)
    {
        $cachedData = Cache::get('mpesa_stk_' . $checkoutRequestId);

        if (!$cachedData) {
            return [
                'success' => false,
                'error' => 'Payment request not found or expired'
            ];
        }

        return [
            'success' => true,
            'status' => $cachedData['status'] ?? 'unknown',
            'phone' => $cachedData['phone'] ?? null,
            'amount' => $cachedData['amount'] ?? null,
            'account_reference' => $cachedData['account_reference'] ?? null,
            'receipt_number' => $cachedData['receipt_number'] ?? null,
            'transaction_date' => $cachedData['transaction_date'] ?? null,
            'result_code' => $cachedData['result_code'] ?? null,
            'result_desc' => $cachedData['result_desc'] ?? null,
            'timestamp' => $cachedData['timestamp'] ?? null,
            'last_checked' => $cachedData['last_checked'] ?? null
        ];
    }

    /**
     * Format phone number to international format
     *
     * @param string $phoneNumber
     * @return string
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

        // Handle different formats
        if (strlen($phoneNumber) == 9 && substr($phoneNumber, 0, 1) == '7') {
            // Format: 7XXXXXXXX -> 2547XXXXXXXX
            return '254' . $phoneNumber;
        } elseif (strlen($phoneNumber) == 10 && substr($phoneNumber, 0, 1) == '0') {
            // Format: 07XXXXXXXX -> 2547XXXXXXXX
            return '254' . substr($phoneNumber, 1);
        } elseif (strlen($phoneNumber) == 12 && substr($phoneNumber, 0, 3) == '254') {
            // Already in correct format
            return $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Map M-PESA response codes to status
     *
     * @param string|null $responseCode
     * @return string
     */
    private function mapResponseCode($responseCode)
    {
        if ($responseCode === null) {
            return 'unknown';
        }

        switch ($responseCode) {
            case '0':
                return 'completed';
            case '1':
                return 'failed';
            case '1032':
                return 'cancelled';
            case '1037':
                return 'timeout';
            default:
                return 'pending';
        }
    }
}
