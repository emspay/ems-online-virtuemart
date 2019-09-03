<?php

namespace GingerPayments\Payment\Order\Transaction;

use Assert\Assertion as Guard;
use GingerPayments\Payment\Common\StringBasedValueObject;

final class CreditorHolderCity
{
	use StringBasedValueObject;

	/**
	 * @param string $value
	 */
	private function __construct($value)
	{
		Guard::notBlank($value, 'Creditor Holder City cannot be blank');

		$this->value = $value;
	}
}