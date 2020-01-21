<?php

namespace GingerPayments\Payment\Tests\Order\Transaction\PaymentMethodDetails;

use GingerPayments\Payment\Order\Transaction\PaymentMethodDetails\KlarnaPayNowPaymentMethodDetails;

final class SofortPaymentMethodDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateFromAnArray()
    {
        $paymentDetails = KlarnaPayNowPaymentMethodDetails::fromArray(
            [
                'transaction_id' => 'some-unique-id-abc123',
                'consumer_name' => 'FA de Vries',
                'consumer_iban' => 'NL91ABNA0417164300',
                'consumer_bic' => 'ABNANL2A'
            ]
        );

        $this->assertInstanceOf(
            'GingerPayments\Payment\Order\Transaction\PaymentMethodDetails\KlarnaPayNowPaymentMethodDetails',
            $paymentDetails
        );

        $this->assertEquals('some-unique-id-abc123', (string) $paymentDetails->transactionId());
        $this->assertEquals('FA de Vries', (string) $paymentDetails->consumerName());
        $this->assertEquals('NL91ABNA0417164300', (string) $paymentDetails->consumerIban());
        $this->assertEquals('ABNANL2A', (string) $paymentDetails->consumerBic());
    }

    /**
     * @test
     */
    public function itShouldSetMissingValuesToNull()
    {
        $paymentDetails = KlarnaPayNowPaymentMethodDetails::fromArray([]);

        $this->assertNull($paymentDetails->transactionId());
        $this->assertNull($paymentDetails->consumerName());
        $this->assertNull($paymentDetails->consumerIban());
        $this->assertNull($paymentDetails->consumerBic());
    }

    /**
     * @test
     */
    public function itShouldConvertToArray()
    {
        $array = [
            'transaction_id' => 'some-unique-id-abc123',
            'consumer_name' => 'FA de Vries',
            'consumer_iban' => 'NL91ABNA0417164300',
            'consumer_bic' => 'ABNANL2A'
        ];

        $this->assertEquals(
            $array,
            KlarnaPayNowPaymentMethodDetails::fromArray($array)->toArray()
        );
    }
}
