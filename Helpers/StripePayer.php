<?php

namespace UncleProject\UncleLaravel\Helpers;

use Cartalyst\Stripe\Stripe;
use Cartalyst\Stripe\Exception\MissingParameterException;
use Cartalyst\Stripe\Exception\CardErrorException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StripePayer {

    protected $stripe;

    public function __construct($stripe_secret = null, $stripe_api_version = null) {

        if(!$stripe_secret) $stripe_secret = config('services.stripe.secret');
        if(!$stripe_api_version) $stripe_api_version = config('services.stripe.version');

        $this->stripe = Stripe::make($stripe_secret, $stripe_api_version);
    }

    public function makePayment($number, $expiryMonth, $expiryYear, $cvv, $amount, $currency, $description)
    {
        try {

            $token = $this->stripe->tokens()->create([
                'card' => [
                    'number' => $number,
                    'exp_month' => $expiryMonth,
                    'exp_year' => $expiryYear,
                    'cvc' => $cvv,
                ],
            ]);

            if (!isset($token['id'])) {
                throw new HttpException(500,"Token card not generated");
            }

            $charge = $this->stripe->charges()->create([
                'card' => $token['id'],
                'currency' => $currency,
                'amount' => $amount,
                'description' => $description
            ]);

            return $charge;

        }
        catch(CardErrorException $e) {
            throw new HttpException(500,$e->getMessage());
        }
        catch(MissingParameterException $e) {
            throw new HttpException(500,$e->getMessage());
        }
        catch (Exception $e) {
            throw new HttpException(500,$e->getMessage());
        }
    }

    public function makePaymentWithMethod($customerId, $methodId, $amount, $currency, $description)
    {
        try {

            $pi = $this->stripe->paymentIntents()->create([
                'customer' => $customerId,
                'payment_method' => $methodId,
                'currency' => $currency,
                'amount' => $amount,
                'description' => $description
            ]);

            $confirm = $this->stripe->paymentIntents()->confirm($pi['id'],
                ['payment_method' => $methodId]
            );

            return $confirm;

        }
        catch(CardErrorException $e) {
            throw new HttpException(500,$e->getMessage());
        }
        catch(MissingParameterException $e) {
            throw new HttpException(500,$e->getMessage());
        }
        catch (Exception $e) {
            throw new HttpException(500,$e->getMessage());
        }
    }

    public function registerCustomer($email)
    {
        try {

            $customer = $this->stripe->customers()->create([
                'email' => $email,
            ]);

            return $customer['id'];

        }
        catch(CardErrorException $e) {
            throw new HttpException(500,$e->getMessage());
        }
        catch(MissingParameterException $e) {
            throw new HttpException(500,$e->getMessage());
        }
        catch (Exception $e) {
            throw new HttpException(500,$e->getMessage());
        }
    }

    public function registerPaymentMethod($number, $expiryMonth, $expiryYear, $cvc)
    {
        try {

            return $this->stripe->paymentMethods()->create([
                'type' => 'card',
                'card' => [
                    'number' => $number,
                    'exp_month' => $expiryMonth,
                    'exp_year' => $expiryYear,
                    'cvc' => $cvc,
                ],
            ]);
        }
        catch(CardErrorException $e) {
            throw new HttpException(500,$e->getMessage());
        }
        catch(MissingParameterException $e) {
            throw new HttpException(500,$e->getMessage());
        }
        catch (Exception $e) {
            throw new HttpException(500,$e->getMessage());
        }
    }

    public function updatePaymentMethod($paymentId, $number, $expiryMonth, $expiryYear, $cvc)
    {
        try {

            return $this->stripe->paymentMethods()->update($paymentId,
                [
                    'card' => [
                        //'number' => $number,
                        'exp_month' => $expiryMonth,
                        'exp_year' => $expiryYear,
                        //'cvc' => $cvc,
                    ],
                ]
            );
        }
        catch(CardErrorException $e) {
            throw new HttpException(500,$e->getMessage());
        }
        catch(MissingParameterException $e) {
            throw new HttpException(500,$e->getMessage());
        }
        catch (Exception $e) {
            throw new HttpException(500,$e->getMessage());
        }
    }

    public function attachMethodToCustomer($customerId, $methodId)
    {
        try {

            $paymentMethod = $this->stripe->paymentMethods()->attach($methodId, $customerId);
        }
        catch(CardErrorException $e) {
            throw new HttpException(500,$e->getMessage());
        }
        catch(MissingParameterException $e) {
            throw new HttpException(500,$e->getMessage());
        }
        catch (Exception $e) {
            throw new HttpException(500,$e->getMessage());
        }
    }

}
