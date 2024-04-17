<?php

namespace Tests\Events;

use Triton\DataContractProtocol\BaseEventContract;




/**
 * Example data contract
 *
 * Class ExampleEvent
 * @package Tests\Events
 */
//Class name is used fot event identification. Do not duplicate them. Each data contract must extend  BaseEventContract class
class ExampleEvent extends BaseEventContract {

	public function __construct()
	{
		//Set event description. It will be shown in Triton interface.
		$this->setDescription('This is an example data contract. Here you can describe when your event occurs')
			 //Variables is the main idea. This vars will be available in Triton, so user will be able to use them
			 //You should add var name (unique in contract context), test value (used for testing templates) and short description
			 ->defineVar('username', 'John Doe', 'User nick name')
		     ->defineVar('balance', 100.00, 'User balance (float)')
		     ->defineVar('homeLink', 'http://mycoolsite.com', 'Link to home page')
		     ->defineVar('blogLink', 'http://mycoolsite.com/blog', 'Link to blog')
		     ->defineVar('accountLink', 'http://mycoolsite.com/account/7', 'Link to user account')
		     ->defineVar('lastActivityDate', '2015-12-25', 'Last activity date')
		     ->defineVar('userAge', 18, 'Age (integer)')
		     ->defineVar('isPremium', true, 'Is user premium ? (bool)')
			 ->defineVar('someStruct', ['opt1', 'opt2' , 'opt3' => ['subOpt1', 'subOpt2']], 'Arrays are supported including multidimensional')
		     ->defineVar('whatAboutJson', '{"country":"Russia","city":"Rostov-on-Don","company":"Везет всем"}', 'Json data also supported');
		//That's it !
	}

}