<?php

namespace GingerPayments\Payment\Order\Transaction;

use GingerPayments\Payment\Common\ChoiceBasedValueObject;

final class PaymentMethod
{
    use ChoiceBasedValueObject;

    /**
     * Possible payment methods
     */
    const IDEAL = 'ideal';
    const CREDIT_CARD = 'credit-card';
    const BANK_TRANSFER = 'bank-transfer';
    const KLARNA_PAY_NOW = 'klarna-pay-now';
    const BANCONTACT = 'bancontact';
    const KLARNA_PAY_LATER = 'klarna-pay-later';
    const PAYPAL = 'paypal';
    const PAYCONIQ = 'payconiq';
    const AFTERPAY = 'afterpay';
    const APPLE_PAY = 'apple-pay';

    /**
     * @return array
     */
    public function possibleValues()
    {
        return [
            self::IDEAL,
            self::CREDIT_CARD,
            self::BANK_TRANSFER,
            self::KLARNA_PAY_NOW,
            self::BANCONTACT,
            self::KLARNA_PAY_LATER,
            self::PAYPAL,
            self::PAYCONIQ,
            self::AFTERPAY,
            self::APPLE_PAY,
        ];
    }

    /**
     * @return bool
     */
    public function isIdeal()
    {
        return $this->value === self::IDEAL;
    }

    /**
     * @return bool
     */
    public function isCreditCard()
    {
        return $this->value === self::CREDIT_CARD;
    }

    /**
     * @return bool
     */
    public function isBankTransfer()
    {
        return $this->value === self::BANK_TRANSFER;
    }

    /**
     * @return bool
     */
    public function isKlarnaPayNow()
    {
        return $this->value === self::KLARNA_PAY_NOW;
    }

    /**
     * @return bool
     */
    public function isBancontact()
    {
        return $this->value === self::BANCONTACT;
    }

    /**
     * @return bool
     */
    public function isKlarnaPayLater()
    {
        return $this->value === self::KLARNA_PAY_LATER;
    }

    /**
     * @return bool
     */
    public function isPayPal()
    {
        return $this->value === self::PAYPAL;
    }

    /**
     * @return bool
     */
    public function isPayconiq()
    {
        return $this->value === self::PAYCONIQ;
    }

    /**
     * @return bool
     */
    public function isAfterPay()
    {
        return $this->value === self::AFTERPAY;
    }

    public function isApplePay()
    {
        return $this->value === self::APPLE_PAY;
    }
}
