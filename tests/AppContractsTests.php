<?php


use Triton\DataContractProtocol\BaseEventContract;


/**
 * App (user created) contracts tests
 *
 * Class AppContractsTests
 */
class AppContractsTests extends \PHPUnit\Framework\TestCase {

    /**
     * @var array
     */
    private $contracts = [];


    public function setUp()
    {
        $this->contracts = $this->findContracts();
    }


    /**
     * Can we instantiate contracts ?
     */
    public function testInstantiateEachDataContract()
    {
        foreach ($this->contracts as $dcClassName) {
            $fullClassName = BaseEventContract::APP_CONTRACTS_NAMESPACE . $dcClassName;
            $dcInstance = new $fullClassName;
            $this->assertInstanceOf(BaseEventContract::class, $dcInstance);
            unset($dcInstance);
        }
    }


    /**
     * Scan src dir & return classnames to test
     *
     * @return array
     */
    private function findContracts()
    {
        $contracts = [];
        $appContractsDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src/Triton/AppContracts';

        if (is_dir($appContractsDir) && is_readable($appContractsDir)) {
            foreach (new DirectoryIterator($appContractsDir) as $fileInfo) {
                /* @var $fileInfo SplFileInfo */
                if ($fileInfo->isDot() || !$fileInfo->isFile() || !$fileInfo->isReadable() || ($fileInfo->getExtension() != 'php')) {
                    continue;
                } else {
                    $className = $fileInfo->getBasename('.php');
                    $contracts[] = $className;
                }
            }
        }

        return $contracts;
    }

}