<?php

namespace Triton\AppContracts;

use Triton\DataContractProtocol\BaseEventContract;


class ExampleEvent extends BaseEventContract {

	public function __construct()
	{
		$this->setDescription('This is an example event')
             ->defineVar('name', 'Vita', 'User name')
			 ->defineVar('balance', 5700.07, 'User balance');
	}

}