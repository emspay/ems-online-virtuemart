<?php

namespace GingerPayments\Payment\Order\Transaction;

use Assert\Assertion as Guard;
use GingerPayments\Payment\Common\StringBasedValueObject;

final class CreditorHolderCountry
{
	use StringBasedValueObject;

	/**
	 * @param string $value
	 */
	private function __construct($value)
	{
		Guard::notBlank($value, 'Creditor Holder Country cannot be blank');

		$this->value = $value;
	}
}