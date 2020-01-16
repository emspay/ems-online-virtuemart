<?php

namespace GingerPayments\Payment\Order\Transaction\PaymentMethodDetails\KlarnaPayNowPaymentMethodDetails;

use Assert\Assertion as Guard;
use GingerPayments\Payment\Common\StringBasedValueObject;

final class TransactionId
{
    use StringBasedValueObject;

    /**
     * @param string $value
     */
    private function __construct($value)
    {
        Guard::notBlank($value, 'Klarna Pay Now transaction ID cannot be blank');

        $this->value = $value;
    }
}
