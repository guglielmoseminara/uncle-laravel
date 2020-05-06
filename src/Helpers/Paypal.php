<?php

namespace UncleProject\UncleLaravel\Helpers;

use Mockery\Exception;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Exception\PayPalConnectionException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


class Paypal {

    protected $api_context;

    public function __construct($client_id = null, $secret = null , $settings = null) {

        if(!$client_id) $client_id = config('paypal.client_id');
        if(!$secret) $secret = config('paypal.secret');
        if(!$settings) $settings = config('paypal.settings');

        $this->api_context = new ApiContext(new OAuthTokenCredential(
            $client_id,
            $secret
        ));

        $this->api_context->setConfig($settings);
    }

    public function getPayment($items, $transaction_description, $transaction_currency_code, $transaction_amount, $return_url, $cancel_url ){

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $paypal_item_list = array();
        foreach($items as $item)
        {
            $paypal_item = new Item();
            $paypal_item->setName($item['description'])/** item name **/
                ->setCurrency($transaction_currency_code)
                ->setQuantity($item['quantity'])
                ->setPrice($item['amount']);

            array_push($paypal_item_list, $paypal_item);
        }

        /** unit price **/

        $item_list = new ItemList();
        $item_list->setItems($paypal_item_list);

        $amount = new Amount();
        $amount->setCurrency($transaction_currency_code)
            ->setTotal($transaction_amount);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription($transaction_description);

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl($return_url)/** Specify return URL **/
            ->setCancelUrl($cancel_url);

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));

        try {
            $payment->create($this->api_context);
        } catch (PayPalConnectionException $ex) {
            throw new AccessDeniedHttpException("Paypal Error: ".json_decode($ex->getData())->details[0]->issue);
        }

        return [
            'id' => $payment->getId(),
            'approvalUrl' => $payment->getApprovalLink()
        ];
    }

    public function isTransactionApproved($paypal_payment_id, $paypal_payer_id){

        if(isset($paypal_payment_id) && isset($paypal_payer_id)){
            try {

                $payment = Payment::get($paypal_payment_id, $this->api_context);
                $execution = new PaymentExecution();
                $execution->setPayerId($paypal_payer_id);

                /**Execute the payment **/
                $result = $payment->execute($execution, $this->api_context);

                dd($result);

                if ($result->getState() == 'approved') {
                    return true;
                }
                else {
                    return false;
                }
            }
            catch(PayPalConnectionException $ex){
                dd($ex);
            } catch (Exception $ex) {
                dd($ex);
            }


        }
        else {
            return false;
        }

    }
}
