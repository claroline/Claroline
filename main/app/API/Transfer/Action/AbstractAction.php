<?php

namespace Claroline\AppBundle\API\Transfer\Action;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;

abstract class AbstractAction
{
    const MODE_CREATE = 'create';
    const MODE_UPDATE = 'update';
    const MODE_DEFAULT = 'default';

    use LoggableTrait;

    abstract public function execute(array $data);

    //better explain the structure
    abstract public function getSchema();

    /**
     * return an array with the following element:
     * - section
     * - action
     * - action name.
     */
    abstract public function getAction();

    public function supports($format)
    {
        return in_array($format, ['csv', 'json']);
    }

    public function getBatchSize()
    {
        return 100;
    }

    public function clear(ObjectManager $om)
    {
        return;
    }

    public function getConfig()
    {
        return [];
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function getMode()
    {
        return self::MODE_DEFAULT;
    }

    public function export()
    {
        throw new \Exception("I don't plan to implements you anytime soon");
    }
}
