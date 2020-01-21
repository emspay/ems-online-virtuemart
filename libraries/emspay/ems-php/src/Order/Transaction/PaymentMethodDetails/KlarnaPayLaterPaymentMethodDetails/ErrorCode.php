<?php

namespace GingerPayments\Payment\Order\Transaction\PaymentMethodDetails\KlarnaPayLaterPaymentMethodDetails;

use Assert\Assertion as Guard;
use GingerPayments\Payment\Common\StringBasedValueObject;

final class ErrorCode
{
    use StringBasedValueObject;

    /**
     * @param string $value
     */
    private function __construct($value)
    {
        Guard::notBlank($value, 'Klarna Pay Later error_code can not be blank.');

        $this->value = $value;
    }
}
