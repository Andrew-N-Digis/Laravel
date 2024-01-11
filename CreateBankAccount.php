<?php

declare(strict_types=1);

namespace App\Listeners\PaymentSystem;

use MangoPay\Address;
use MangoPay\BankAccount;
use MangoPay\Libraries\Exception;
use App\Factory\BankAccountFactory;
use Illuminate\Support\Facades\Log;
use App\Entity\Payment\MangopayUser;
use MangoPay\BankAccountDetailsIBAN;
use App\Events\CreatePaymentSystemBankAccount;
use App\Exceptions\PaymentSystemCustomException;
use App\Service\PaymentSystem\PaymentSystemInterface;
use App\Repository\Payment\PaymentUserRepositoryInterface;

final class CreateBankAccount
{
    public function __construct(
        private PaymentSystemInterface $paymentSystem,
        private PaymentUserRepositoryInterface $paymentUserRepository
    ) {
    }

    public function handle(CreatePaymentSystemBankAccount $event): void
    {
        $api = $this->paymentSystem->api;

        Log::channel('mangopay')->info('[seller] bank account trying to create...');

        try {
            $result = $api->Users->CreateBankAccount(
                $event->user()->identifier,
                BankAccountFactory::create($event->user())
            );

            $this->paymentUserRepository->update(
                $event->user(),
                [
                    'bank_account_id' => $result->Id
                ]
            );

            Log::channel('mangopay')->info(\sprintf('[seller] bank account created, local id: %s', $event->user()->id));
        } catch (Exception $throwable) {
            Log::channel('mangopay')->error(
              \sprintf('[seller] bank account not created, local id %s !',  $event->user()->id), 
              (array)$throwable->GetErrorDetails()->Errors
            );

            throw new PaymentSystemCustomException((array)$throwable->GetErrorDetails()->Errors);
        }
    }
}