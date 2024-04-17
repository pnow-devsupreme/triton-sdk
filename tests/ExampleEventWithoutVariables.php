<?php

namespace Tests\Events;

use Triton\DataContractProtocol\BaseEventContract;

/**
 * Class ExampleEventWithoutVariables
 * @package Tests\Events
 */
class ExampleEventWithoutVariables extends BaseEventContract {

	public function __construct()
	{
		$this->setDescription('Some times you need an event without any variables');
	}

}