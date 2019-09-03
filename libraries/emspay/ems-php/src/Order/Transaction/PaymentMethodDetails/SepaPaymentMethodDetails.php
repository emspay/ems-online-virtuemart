<?php

namespace GingerPayments\Payment\Order\Transaction\PaymentMethodDetails;

use GingerPayments\Payment\Iban;
use GingerPayments\Payment\Order\Transaction\CreditorHolderName;
use GingerPayments\Payment\Order\Transaction\CreditorHolderCity;
use GingerPayments\Payment\Order\Transaction\CreditorHolderCountry;
use GingerPayments\Payment\Order\Transaction\Reference;
use GingerPayments\Payment\SwiftBic;
use GingerPayments\Payment\Order\Transaction\PaymentMethodDetails;
use GingerPayments\Payment\Order\Transaction\PaymentMethodDetails\ConsumerName;
use GingerPayments\Payment\Order\Transaction\PaymentMethodDetails\IdealPaymentMethodDetails\ConsumerAddress;
use GingerPayments\Payment\Order\Transaction\PaymentMethodDetails\IdealPaymentMethodDetails\ConsumerCity;
use GingerPayments\Payment\Order\Transaction\PaymentMethodDetails\IdealPaymentMethodDetails\ConsumerCountry;

final class SepaPaymentMethodDetails implements PaymentMethodDetails
{
    /**
     * @var Reference
     */
    private $reference;

    /**
     * @var Iban|null
     */
    private $consumerIban;
    
    /**
     * @var SwiftBic|null
     */
    private $consumerBic;

    /**
     * @var ConsumerName|null
     */
    private $consumerName;

    /**
     * @var ConsumerAddress|null
     */
    private $consumerAddress;

    /**
     * @var ConsumerCity|null
     */
    private $consumerCity;

	/**
	 * @var CreditorHolderName|null
	 */
	private $creditorHolderName;

	/**
	 * @var CreditorHolderCity|null
	 */
	private $creditorHolderCity;

	/**
	 * @var CreditorHolderCountry|null
	 */
	private $creditorHolderCountry;

    /**
     * @param array $details
     * @return SepaPaymentMethodDetails
     */
    public static function fromArray(array $details)
    {
        return new static(
            array_key_exists('consumer_name', $details)
                ? ConsumerName::fromString($details['consumer_name']) : null,
            array_key_exists('consumer_address', $details)
                ? ConsumerAddress::fromString($details['consumer_address']) : null,
            array_key_exists('consumer_city', $details)
                ? ConsumerCity::fromString($details['consumer_city']) : null,
            array_key_exists('consumer_country', $details)
                ? ConsumerCountry::fromString($details['consumer_country']) : null,
            array_key_exists('creditor_iban', $details)
                ? Iban::fromString($details['creditor_iban']) : null,
            array_key_exists('creditor_bic', $details)
                ? SwiftBic::fromString($details['creditor_bic']) : null,
            array_key_exists('reference', $details)
                ? Reference::fromString($details['reference']) : null,
	        array_key_exists('creditor_account_holder_name', $details)
		        ? CreditorHolderName::fromString($details['creditor_account_holder_name']) : null,
	        array_key_exists('creditor_account_holder_city', $details)
		        ? CreditorHolderCity::fromString($details['creditor_account_holder_city']) : null,
	        array_key_exists('creditor_account_holder_country', $details)
		        ? CreditorHolderCountry::fromString($details['creditor_account_holder_country']) : null
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'consumer_name' => ($this->consumerName() !== null) ? $this->consumerName()->toString() : null,
            'consumer_address' => ($this->consumerAddress() !== null) ? $this->consumerAddress()->toString() : null,
            'consumer_city' => ($this->consumerCity() !== null) ? $this->consumerCity()->toString() : null,
            'consumer_country' => ($this->consumerCountry() !== null) ? $this->consumerCountry()->toString() : null,
            'creditor_iban' => ($this->consumerIban() !== null) ? $this->consumerIban()->toString() : null,
            'creditor_bic' => ($this->consumerBic() !== null) ? $this->consumerBic()->toString() : null,
            'reference' => ($this->reference() !== null) ? $this->reference()->toString() : null,
            'creditor_account_holder_name' => ($this->creditorHolderName() !== null) ? $this->creditorHolderName()->toString() : null,
            'creditor_account_holder_city' => ($this->creditorHolderCity() !== null) ? $this->creditorHolderCity()->toString() : null,
            'creditor_account_holder_country' => ($this->creditorHolderCountry() !== null) ? $this->creditorHolderCountry()->toString() : null
        ];
    }

    /**
     * @return SwiftBic|null
     */
    public function consumerBic()
    {
        return $this->consumerBic;
    }

    /**
     * @return Iban|null
     */
    public function consumerIban()
    {
        return $this->consumerIban;
    }

    /**
     * @return consumerName|null
     */
    public function consumerName()
    {
        return $this->consumerName;
    }

    /**
     * @return Reference
     */
    public function reference()
    {
        return $this->reference;
    }

    /**
     * @return ConsumerAddress|null
     */
    public function consumerAddress()
    {
        return $this->consumerAddress;
    }

    /**
     * @return ConsumerCity|null
     */
    public function consumerCity()
    {
        return $this->consumerCity;
    }

    /**
     * @return ConsumerCountry|null
     */
    public function consumerCountry()
    {
        return $this->consumerCountry;
    }

	/**
	 * @return CreditorHolderName|null
	 */
	public function creditorHolderName()
	{
		return $this->creditorHolderName;
	}

	/**
	 * @return CreditorHolderCity|null
	 */
	public function creditorHolderCity()
	{
		return $this->creditorHolderCity;
	}

	/**
	 * @return CreditorHolderCountry|null
	 */
	public function creditorHolderCountry()
	{
		return $this->creditorHolderCountry;
	}

    /**
     * @param ConsumerName $consumerName
     * @param ConsumerAddress $consumerAddress
     * @param ConsumerCity $consumerCity
     * @param ConsumerCountry $consumerCountry
     * @param Iban $consumerIban
     * @param SwiftBic $consumerBic
     * @param Reference $reference
     * @param CreditorHolderName $creditorHolderName
     * @param CreditorHolderCity $creditorHolderCity
     * @param CreditorHolderCountry $creditorHolderCountry
     */
    private function __construct(
        ConsumerName $consumerName = null,
        ConsumerAddress $consumerAddress = null,
        ConsumerCity $consumerCity = null,
        ConsumerCountry $consumerCountry = null,
        Iban $consumerIban = null,
        SwiftBic $consumerBic = null,
        Reference $reference = null,
	    CreditorHolderName $creditorHolderName = null,
	    CreditorHolderCity $creditorHolderCity = null,
	    CreditorHolderCountry $creditorHolderCountry = null
    ) {
        $this->consumerName = $consumerName;
        $this->consumerAddress = $consumerAddress;
        $this->consumerCity = $consumerCity;
        $this->consumerCountry = $consumerCountry;
        $this->consumerIban = $consumerIban;
        $this->consumerBic = $consumerBic;
        $this->reference = $reference;
        $this->creditorHolderName = $creditorHolderName;
        $this->creditorHolderCity = $creditorHolderCity;
        $this->creditorHolderCountry = $creditorHolderCountry;
    }
}
