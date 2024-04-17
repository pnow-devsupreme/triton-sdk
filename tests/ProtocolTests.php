<?php


use Tests\Events\ExampleEvent;
use Tests\Events\ExampleEventWithoutVariables;
use Tests\Events\ExampleStrictEvent;
use Triton\DataContractProtocol\BaseEventContract;
use Triton\Entities\Event\EventField;
use Triton\Entities\Recipient\RecipientField;


/**
 * Protocol tests
 *
 * Class General
 */
class ProtocolTests extends \PHPUnit\Framework\TestCase {


    /**
     * Event must serialize only protocol fields
     */
    public function testCustomEventFields()
    {
        $dcInstance1 = new ExampleEvent();

        $dcInstance1->setRecipient([
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1
        ]);

        $testVars = $dcInstance1->getTestVars();
        foreach ($testVars as $varName => $varValue) {
            $dcInstance1->setVar($varName, $varValue);
        }

        $packedContract = $dcInstance1->toArray();

        $customFields = [
            'string' => 'look_at_me_i_am_custom',
            'int1' => 120391,
            'int2' => -120391,
            'zero' => 0,
            'float1' => 3.14,
            'float2' => -3.14,
            'boolean1' => true,
            'boolean2' => false,
            'array1' => [1, 2, 3],
            'array2' => ['a' => 1, 'b' => 2, 'c' => 3],
            'array3' => ['a' => 1, 'b' => ['b1' => 11, 'b2' => 12], 'c' => 3]
        ];

        foreach ($customFields as $field => $value) {
            $packedContract[$field] = $value;
            $this->assertArrayHasKey($field, $packedContract);
        }

        $dcInstance2 = new ExampleEvent();
        $dcInstance2->fromArray($packedContract);
        $unpackedContract = $dcInstance2->toArray();

        foreach ($customFields as $field => $value) {
            $this->assertArrayNotHasKey($field, $unpackedContract);
        }

        foreach (EventField::all() as $fieldName) {
            $this->assertArrayHasKey($fieldName, $packedContract);
        }
    }


    /**
     * Variable type should be resistant to serialization\unserialization
     */
    public function testEventPreservesVariablesTypes()
    {
        $dcInstance1 = new ExampleEvent();

        $dcInstance1->setVar('username', 'John Doe')
            ->setVar('balance', 100.01)
            ->setVar('homeLink', 'http://mycoolsite.com')
            ->setVar('blogLink', 'http://mycoolsite.com/blog')
            ->setVar('accountLink', 'http://mycoolsite.com/account/7')
            ->setVar('lastActivityDate', '2015-12-25')
            ->setVar('userAge', 18)
            ->setVar('isPremium', true)
            ->setVar('someStruct', ['opt1', 'opt2', 'opt3' => ['subOpt1', 'subOpt2']])
            ->setVar('whatAboutJson', '{"country":"Russia","city":"Rostov-on-Don","company":"Везет всем"}');


        $dcInstance1->setRecipient([
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1,
        ]);

        $testVars = $dcInstance1->getTestVars();
        foreach ($testVars as $varName => $varValue) {
            $dcInstance1->setVar($varName, $varValue);
        }

        $transportable = serialize($dcInstance1->toArray());

        $dcInstance2 = new ExampleEvent();
        /* @var $dcInstance2 BaseEventContract */
        $dcInstance2->fromArray(unserialize($transportable));

        foreach ($dcInstance2->getVars() as $varName => $varValue) {
            $this->assertInternalType(gettype($dcInstance2->getTestVars()[$varName]), $varValue);
        }

    }


    /**
     * Recipient entity may contain optional fields
     */
    public function testRecipientOptionalFields()
    {
        $dcInstance1 = new ExampleEvent();


        $recipientData = [
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1
        ];

        $i = 0;
        $customFieldsCount = rand(1, 10);

        for ($i = 0; $i < $customFieldsCount; $i++) {
            $recipientData["custom_field_$i"] = "custom_value_$i";
        }

        $dcInstance1->setRecipient($recipientData);

        $testVars = $dcInstance1->getTestVars();
        foreach ($testVars as $varName => $varValue) {
            $dcInstance1->setVar($varName, $varValue);
        }

        $transportable = serialize($dcInstance1->toArray());

        $dcInstance2 = new ExampleEvent();
        /* @var $dcInstance2 BaseEventContract */

        $dcInstance2->fromArray(unserialize($transportable));

        $this->assertSameSize($dcInstance2->getRecipient(), $recipientData);
    }

    /**
     * Variables count should be equal to test values
     */
    public function testVarSchemaConsistency()
    {

        $dcInstance = new ExampleEvent();


        $dcInstance->setRecipient([
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1
        ]);

        $testVars = $dcInstance->getTestVars();
        foreach ($testVars as $varName => $varValue) {
            $dcInstance->setVar($varName, $varValue);
        }

        $this->assertSameSize($dcInstance->getTestVars(), $dcInstance->getVars());
    }

    /**
     * Empty recipient is not allowed
     *
     * @expectedException Triton\Exceptions\BadRecipientException
     */
    public function testEmptyRecipient()
    {
        $event = new Tests\Events\ExampleEvent();

        $event->setVar('username', 'John Doe')
            ->setVar('balance', 100.00)
            ->setVar('homeLink', 'http://mycoolsite.com')
            ->setVar('blogLink', 'http://mycoolsite.com/blog')
            ->setVar('accountLink', 'http://mycoolsite.com/account/7')
            ->setVar('lastActivityDate', '2015-12-25')
            ->setVar('userAge', 18)
            ->setVar('isPremium', true)
            ->setVar('someStruct', ['opt1', 'opt2', 'opt3' => ['subOpt1', 'subOpt2']])
            ->setVar('whatAboutJson', '{"country":"Russia","city":"Rostov-on-Don","company":"Везет всем"}');

        $event->toArray();
    }

    /**
     * Recipient entity must contain some required fields
     *
     * @expectedException Triton\Exceptions\BadRecipientException
     */
    public function testBadRecipient()
    {
        $event = new Tests\Events\ExampleEvent();

        $event->setRecipient([
            'there is no' => 1,
            'required fields' => 2
        ])->setVar('username', 'John Doe')
            ->setVar('balance', 100.00)
            ->setVar('homeLink', 'http://mycoolsite.com')
            ->setVar('blogLink', 'http://mycoolsite.com/blog')
            ->setVar('accountLink', 'http://mycoolsite.com/account/7')
            ->setVar('lastActivityDate', '2015-12-25')
            ->setVar('userAge', 18)
            ->setVar('isPremium', true)
            ->setVar('someStruct', ['opt1', 'opt2', 'opt3' => ['subOpt1', 'subOpt2']])
            ->setVar('whatAboutJson', '{"country":"Russia","city":"Rostov-on-Don","company":"Везет всем"}');

        $event->toArray();
    }

    /**
     * Events without variables allowed
     */
    public function testEventWithoutVars()
    {
        $event = new Tests\Events\ExampleEventWithoutVariables();

        $event->setRecipient([
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1
        ]);

        $a = $event->toArray();
        $this->assertArrayHasKey(EventField::VARS, $a);
        $this->assertEmpty($a[EventField::VARS]);
    }

    /**
     * You can not set variable that absent in schema
     *
     * @expectedException \Triton\Exceptions\BadVarException
     */
    public function testSetUndefinedVar()
    {
        $event = new Tests\Events\ExampleEventWithoutVariables();

        $event->setRecipient([
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1
        ])->setVar('undefined_var', [1, 2, 3]);

        $event->toArray();
    }

    /**
     * In non strict mode (by default)
     * variables actual values should  have similar
     * types with test ones
     */
    public function testNonStrictEvent()
    {
        $event = new Tests\Events\ExampleEvent();

        $event->setRecipient([
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1
        ])->setVar('username', 'John Doe')
            ->setVar('balance', 45)//May be float or int
            ->setVar('homeLink', '569045')//May be string
            ->setVar('blogLink', '453456')
            ->setVar('accountLink', '5')
            ->setVar('lastActivityDate', 'vxcvc')
            ->setVar('userAge', 18.9875)
            ->setVar('isPremium', true)//Must be boolean
            ->setVar('someStruct', ['opt1', 'opt2', 'opt3' => ['subOpt1', 'subOpt2']])//Must be array
            ->setVar('whatAboutJson', '{"country":"Russia","city":"Rostov-on-Don","company":"Везет всем"}');

        $a = $event->toArray();

        $this->assertArrayHasKey(EventField::VARS, $a);
        $this->assertEquals(count($a[EventField::VARS]), 10);
        $this->assertEquals(count($a[EventField::RECIPIENT]), 2);
    }

    /**
     * Check type restrictions in strict mode
     *
     * @expectedException \Triton\Exceptions\TypeMismatchException
     */
    public function testTypeCheckInStrictMode()
    {
        $event = new ExampleStrictEvent();

        $event->setRecipient([
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1
        ])->setVar('string_var', 11)
            ->setVar('int_var', 25.3)
            ->setVar('float_var', 2)
            ->setVar('bool_var', 1)
            ->setVar('array_var', [1, 2, 3]);
    }


    /**
     * Only scalars and arrays are supported
     *
     * @expectedException \Triton\Exceptions\BadVarException
     */
    public function testUnsupportedTypes()
    {
        $event = new Tests\Events\ExampleEvent();

        $event->setRecipient([
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1
        ])->setVar('username', tmpfile())
            ->setVar('balance', 100.00)
            ->setVar('homeLink', 'http://mycoolsite.com')
            ->setVar('blogLink', 'http://mycoolsite.com/blog')
            ->setVar('accountLink', 'http://mycoolsite.com/account/7')
            ->setVar('lastActivityDate', '2015-12-25')
            ->setVar('userAge', 18)
            ->setVar('isPremium', true)
            ->setVar('someStruct', new stdClass())
            ->setVar('whatAboutJson', '{"country":"Russia","city":"Rostov-on-Don","company":"Везет всем"}');

        $event->toArray();
    }

    /**
     * In strict mode you should not set same var multiple times
     *
     * @expectedException \Triton\Exceptions\BadVarException
     */
    public function testMultipleSetIStrictMode()
    {
        $event = new ExampleStrictEvent();

        $event->setRecipient([
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1
        ])->setVar('string_var', 'yes, I am string')
            ->setVar('int_var', 25)
            ->setVar('int_var', 25)//Oops...
            ->setVar('float_var', 3.14)
            ->setVar('bool_var', true)
            ->setVar('array_var', [1, 2, 3]);
    }

    /**
     * Null is allowed for every variable
     *
     */
    public function testNullableValue()
    {
        $event = new Tests\Events\ExampleEvent();

        $event->setRecipient([
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1
        ])->setVar('username', null)
            ->setVar('balance', null)
            ->setVar('homeLink', null)
            ->setVar('blogLink', null)
            ->setVar('accountLink', null)
            ->setVar('lastActivityDate', null)
            ->setVar('userAge', null)
            ->setVar('isPremium', null)
            ->setVar('someStruct', null)
            ->setVar('whatAboutJson', null);

        $a = $event->toArray();
        $this->assertArrayHasKey(EventField::VARS, $a);
        foreach($a[EventField::VARS] as $varName => $varValue) {
            $this->assertNull($varValue);
        }
    }


    /**
     * Do contracts pack right way ?
     *
     * @throws Exception
     */
    public function testPack()
    {
        //1. Instantiate
        $dcInstance = new ExampleEvent();


        //2 Fill
        $dcInstance->setRecipient([
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1
        ]);

        $testVars = $dcInstance->getTestVars();
        foreach ($testVars as $varName => $varValue) {
            $dcInstance->setVar($varName, $varValue);
        }

        //3 Pack
        $transportable = $dcInstance->toArray();

        //4 Test
        foreach (EventField::all() as $fieldName) {
            $this->assertArrayHasKey($fieldName, $transportable);
        }
    }

    /**
     * Event id must be same as short class name
     */
    public function testEventId()
    {
        $dc1 = new ExampleEventWithoutVariables();
        $dc1->setRecipient([
            RecipientField::EMAIL => 'test@test.com',
            RecipientField::USER_ID => 1
        ]);

        $transportable = serialize($dc1->toArray());

        $dc2 = new ExampleEventWithoutVariables();
        $dc2->fromArray(unserialize($transportable));

        $this->assertEquals($dc2->getEventId(), 'ExampleEventWithoutVariables');


    }
}