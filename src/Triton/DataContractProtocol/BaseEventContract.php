<?php
namespace Triton\DataContractProtocol;

use Triton\Entities\Event\EventField;
use Triton\Entities\Recipient\RecipientField;
use Triton\Entities\Variable\Context;
use Triton\Entities\Variable\VariableField;
use Triton\Entities\Variable\Reserved;
use Triton\Exceptions\BadDescriptionException;
use Triton\Exceptions\BadRecipientException;
use Triton\Exceptions\BadTimeStampException;
use Triton\Exceptions\BadVarException;
use Triton\Exceptions\TypeMismatchException;

class BaseEventContract {

    const APP_CONTRACTS_NAMESPACE = 'Triton\\AppContracts\\';

	/**
	 * Recipient
	 *
	 * @var array
	 */
	private $recipient = null;

	/**
	 * Variables map. Contains only var name, test_value & description
	 *
	 * @var array
	 */
	private $varsSchema = [];

	/**
	 * Vars data. Contains only var name & value
	 *
	 * @var array
	 */
	private $varsContainer = [];

	/**
	 * Event description
	 *
	 * @var string
	 */
	private $description = '';

	/**
	 * When event occurred
	 *
	 * @var int
	 */
	private $timestamp = null;

    /**
     * Check variable type when true
     *
     * @var bool
     */
	private $strictMode = false;

	/**
	 * Data-contract short class name is event id
	 *
	 * @return string
	 */
	public final function getEventId()
	{
		return basename(str_replace('\\', '/', static::class));
	}

    /**
     * Set target recipient
     *
     * @param $data
     * @return $this
     * @throws \Exception
     */
	public final function setRecipient($data)
	{
        try {
            if (empty($data)) {
                throw new \Exception('Empty data');
            }

            if (!is_array($data)) {
                throw new \Exception('Data must be an array');
            }

            foreach (RecipientField::allRequired() as $field) {
                if (!isset($data[$field])) {
                    throw new \Exception("$field field is required");
                }
            }

            $this->recipient = $data;

            return $this;
        } catch (\Exception $e) {
            throw new BadRecipientException('Failed to set recipient. Reason:' . $e->getMessage());
        }
	}


	/**
	 * Returns recipient
	 *
	 * @return null
	 */
	public function getRecipient()
	{
		return $this->recipient;
	}


	/**
	 * Set event description
	 *
	 * @param $description
	 * @return $this
	 */
	protected final function setDescription($description)
	{
		$this->description = strip_tags(trim($description));
		return $this;
	}


	/**
	 * Return event description
	 *
	 * @return string
	 */
	public final function getDescription()
	{
		return $this->description;
	}


	/**
	 * Self validation
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public final function isValid()
	{
		try {
			if (empty($this->description)) {
				throw new BadDescriptionException('Empty description');
			}

			if (empty($this->recipient)) {
				throw new BadRecipientException('Empty recipient');
			}

			if (!empty($this->timestamp) && !is_int($this->timestamp)) {
				throw new BadTimeStampException('Timestamp must be valid Unix Timestamp integer value');
			}

			foreach ($this->varsSchema as $varName => $var) {
				if (!array_key_exists($varName, $this->varsContainer)) {
					throw new BadVarException("Please set '$varName'" );
				}

				if (!isset($var[VariableField::TEST_VALUE])) {
					throw new BadVarException("Empty test value. Var: '$varName'");
				}

				if (empty($var[VariableField::DESCRIPTION])) {
					throw new BadVarException("Empty description. Var: '$varName'");
				}
			}

			unset($varName);
			unset($var);

			foreach ($this->varsContainer as $varName => $var) {
				if (!array_key_exists($varName, $this->varsSchema)) {
					throw new BadVarException("Var '$varName' is not described in schema");
				}
			}

		} catch (\Exception $e) {
			throw new $e('Data contract validation failed. Reason: ' . $e->getMessage());
		}

		return true;
	}


	/**
	 * Main marshalling method
	 *
	 * @return array
	 * @throws \Exception
	 */
	public final function toArray()
	{
		try {
            $data = null;

			if ($this->isValid()) {
                $data[EventField::EVENT_ID] = $this->getEventId();
                $data[EventField::RECIPIENT] = $this->getRecipient();
                $data[EventField::VARS] = $this->getVars();
                $data[EventField::TIMESTAMP] = $this->setTimestamp(time())->getTimestamp();
                $data[EventField::STRICT_MODE] = $this->strictMode;
            }
            return $data;

		} catch (\Exception $e) {
			throw new $e('Failed to pack data-contract. Reason: ' . $e->getMessage());
		}
	}

	/**
	 * Main unmarshaling method
	 *
	 * @param $source
	 * @return $this
	 */
	public final function fromArray($source)
	{
		//Fill recipient
        $this->setRecipient(empty($source[EventField::RECIPIENT]) ? null : $source[EventField::RECIPIENT]);

		//Fill vars
		if (!empty($source[EventField::VARS]) && is_array($source[EventField::VARS])) {
			foreach($source[EventField::VARS] as $name => $value) {
				$this->setVar($name,$value);
			}
		}

		//Timestamp
        $this->setTimestamp(empty($source[EventField::TIMESTAMP]) ? null : $source[EventField::TIMESTAMP]);

		//Strictness
		if (!empty($source[EventField::STRICT_MODE]) && (true == $source[EventField::STRICT_MODE])) {
		    $this->mustBeStrict();
        }

		return $this;
	}

	/**
	 * Returns vars without meta-info. Only names & values
	 *
	 * @return array
	 */
	public final function getVars()
	{
		$vars = [];
		foreach ($this->varsContainer as $name => $var) {
			$vars[$name] = $var[VariableField::VALUE];
		}

		return $vars;
	}

	/**
	 * Returns vars filled by test values
	 *
	 * @return array
	 */
	public final function getTestVars()
	{
		$vars = [];
		foreach ($this->varsSchema as $name => $var) {
			$vars[$name] = $var[VariableField::TEST_VALUE];
		}

		return $vars;
	}


	/**
	 * Define variable
	 *
	 * @param $name
	 * @param $testValue
	 * @param $description
	 * @return $this
	 * @throws \Exception
	 */
	protected final function defineVar($name, $testValue, $description)
	{
	    try {
            $name = trim($name);
            $description = htmlspecialchars(trim($description));

            if (empty($name)) {
                throw new \Exception('Name is required');
            }

            if (!isset($testValue)) {
                throw new \Exception('Test value is required');
            }

            if ( empty($description)) {
                throw new \Exception('Description is required');
            }

            if (array_key_exists($name, $this->varsSchema)) {
                throw new \Exception('Variable already exists in schema');
            }

            if (in_array($name, Reserved::all())) {
                throw new \Exception('Variable name is reserved');
            }

            if (!is_scalar($testValue) && !is_array($testValue)) {
                throw new \Exception('Only scalar values and arrays allowed as test-value');
            }

            if (!preg_match('~^[a-zA-Z_][a-zA-Z0-9_]*$~', $name)) {
                throw new \Exception('Not valid PHP variable name');
            }

            $this->varsSchema[$name] = [
                VariableField::CONTEXT      => Context::EVENT,
                VariableField::TEST_VALUE   => $testValue,
                VariableField::DESCRIPTION  => $description
            ];
            return $this;

        } catch (\Exception $e) {
	        throw new \Exception('Failed to add variable. Reason:' . $e->getMessage());
        }

	}

	/**
	 * Set variable
	 *
	 * @param $name
	 * @param $value
	 * @return $this
	 * @throws \Exception
	 */
	public final function setVar($name, $value)
	{
		$name = trim($name);
		if (is_string($value)) {
			$value = trim($value);
		}

		if (empty($name)) {
			throw new BadVarException('Failed to set variable. Name is empty');
		}

		if (!array_key_exists($name, $this->varsSchema)) {
			throw new BadVarException("Failed to set variable. Variable '$name' is not defined in data contract schema");
		}

		if (!is_null($value)) {
            if (!is_scalar($value) && !is_array($value)) {
                throw new BadVarException('Only scalar values, arrays and null allowed as value. ' . gettype($value) . ' given');
            }


            if ($this->strictMode) {
                //Strict type check
                if (!$this->sameType($this->varsSchema[$name][VariableField::TEST_VALUE], $value)) {
                    throw new TypeMismatchException("Actual value of $name must have same type as test value");
                }

                //Check on double set

                if (isset($this->varsContainer[$name])) {
                    throw new BadVarException("Variable $name already set");
                }
            } else {
                //Flexible check
                if (!$this->similarType($this->varsSchema[$name][VariableField::TEST_VALUE], $value)) {
                    throw new TypeMismatchException("Actual value of $name should have similar type as test value");
                }
            }
        }


		$this->varsContainer[$name][VariableField::VALUE] = $value;

		return $this;
	}

	/**
	 * Return vars metadata
	 *
	 * @return array
	 */
	public final function describeVars()
	{
		return $this->varsSchema;
	}

	/**
	 * Returns event timestamp
	 *
	 * @return int|null
	 */
	public function getTimestamp()
	{
		return $this->timestamp;
	}

	/**
	 * Set event timestamp
	 *
	 * @param int $timestamp
	 * @return $this
	 */
	public function setTimestamp($timestamp)
	{
		$this->timestamp = $timestamp;
		return $this;
	}

    /**
     * Set strict mode on
     */
	protected function mustBeStrict()
    {
        $this->strictMode = true;
        return $this;
    }

    /**
     * Finds whether a variables has same internal type
     *
     * @param $v1
     * @param $v2
     * @return bool
     */
	private function sameType($v1, $v2)
    {
        return gettype($v1) == gettype($v2);
    }

    /**
     * Finds whether a variables has similar internal type
     *
     * @param $v1
     * @param $v2
     * @return bool
     */
    private function similarType($v1, $v2)
    {
        return (
            (is_array($v1) && is_array($v2)) ||
            (is_bool($v1) && is_bool($v2)) ||
            (is_numeric($v1) && is_numeric($v2)) ||
            (is_string($v1) && is_string($v2))
        );
    }

}