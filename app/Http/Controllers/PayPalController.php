<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalController extends Controller
{
    /**
     * create transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function createTransaction()
    {
        return view('transaction');
    }

    /**
     * process transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function processTransaction(Request $request)
    {
        $provider = new PayPalClient;
        $mode ="sandbox";
        $client_id='Aa_EWLFsqKyLpLkb7x4DXKCoy9m7FxS_GbZO28y7ZVfFmzH39VJ4IwNfPNP5I-BJW_PztrWGFrpFk0K5';
        $secret='EI9Dlp0X8-Vmr3OY0cwl0yVOuPyhzHDEelzk-hYg1bqgjYhPbIOys9hxJwfNHTrei2Ucr6u5_BEB6wyg';

        $config = [
            'mode'    => $mode, // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.
            'sandbox' => [
                'client_id'         => $client_id,
                'client_secret'     => $secret,
                'app_id'            => 'APP-80W284485P519543T',
            ],
            'live' => [
                'client_id'         => '',
                'client_secret'     => '',
                'app_id'            => '',
            ],

            'payment_action' => "sale", // Can only be 'Sale', 'Authorization' or 'Order'
            'currency'       => "USD",
            'notify_url'     => '', // Change this accordingly for your application.
            'locale'         => "en_US", // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
            'validate_ssl'   => true, // Validate SSL when creating api client.
        ];


        $provider->setApiCredentials($config);
        $paypalToken = $provider->getAccessToken();
        $request_id = 'create-product-'.time();
        $data=[
            "name"=> "Audio Streaming Service",
            "description"=> "Audio2 streaming service",
            "type"=> "SERVICE",
            "category"=> "SOFTWARE"
        ];
        $product = $provider->createProduct($data, $request_id);

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('successTransaction'),
                "cancel_url" => route('cancelTransaction'),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => "4.00"
                    ],

                    "description" => "Nirujan The payment transaction description."
                ]

            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {

            // redirect to approve href
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }

            return redirect()
                ->route('createTransaction')
                ->with('error', 'Something went wrong.');

        } else {
            return redirect()
                ->route('createTransaction')
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }
    }

    /**
     * success transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function successTransaction(Request $request)
    {
        $provider = new PayPalClient;
        $mode ="sandbox";
        $client_id='Aa_EWLFsqKyLpLkb7x4DXKCoy9m7FxS_GbZO28y7ZVfFmzH39VJ4IwNfPNP5I-BJW_PztrWGFrpFk0K5';
        $secret='EI9Dlp0X8-Vmr3OY0cwl0yVOuPyhzHDEelzk-hYg1bqgjYhPbIOys9hxJwfNHTrei2Ucr6u5_BEB6wyg';

        $config = [
            'mode'    => $mode, // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.
            'sandbox' => [
                'client_id'         => $client_id,
                'client_secret'     => $secret,
                'app_id'            => 'APP-80W284485P519543T',
            ],
            'live' => [
                'client_id'         => '',
                'client_secret'     => '',
                'app_id'            => '',
            ],

            'payment_action' => "sale", // Can only be 'Sale', 'Authorization' or 'Order'
            'currency'       => "USD",
            'notify_url'     => '', // Change this accordingly for your application.
            'locale'         => "en_US", // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
            'validate_ssl'   => true, // Validate SSL when creating api client.
        ];
        $provider->setApiCredentials($config);
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);
        $product_id = '72255d4849af8ed6e0df1173';
        $product = $provider->showProductDetails($product_id);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            return redirect()
                ->route('createTransaction')
                ->with('success', 'Transaction complete.');
        } else {
            return redirect()
                ->route('createTransaction')
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }
    }

    /**
     * cancel transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelTransaction(Request $request)
    {
        return redirect()
            ->route('createTransaction')
            ->with('error', $response['message'] ?? 'You have canceled the transaction.');
    }
}
