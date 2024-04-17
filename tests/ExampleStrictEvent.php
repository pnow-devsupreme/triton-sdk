<?php

namespace Tests\Events;

use Triton\DataContractProtocol\BaseEventContract;

/**
 * Class ExampleStrictEvent
 * @package Tests\Events
 */
class ExampleStrictEvent extends BaseEventContract {

    public function __construct()
    {
        $this->setDescription('This event is strict. All actual values must be same type as test ones')
             ->mustBeStrict()
             ->defineVar('string_var', 'yes, I am string', 'Some string')
             ->defineVar('int_var', 25, 'Some integer. Not float!')
             ->defineVar('float_var', 3.14, 'Some float. Not int!')
             ->defineVar('bool_var', true, 'Some boolean. Not int or something else!')
             ->defineVar('array_var', [1,2,3], 'Some array');

    }

}