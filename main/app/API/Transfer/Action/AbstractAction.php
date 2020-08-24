<?php

namespace Claroline\AppBundle\API\Transfer\Action;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;

abstract class AbstractAction
{
    const MODE_CREATE = 'create';
    const MODE_UPDATE = 'update';
    const MODE_DELETE = 'delete';
    const MODE_DEFAULT = 'default';

    use LoggableTrait;

    abstract public function execute(array $data, &$successData = []);

    //better explain the structure
    abstract public function getSchema(/*array $options = [], array $extra = []*/);

    /**
     * return an array with the following element:
     * - section
     * - action
     * - action name.
     */
    abstract public function getAction();

    public function supports($format, array $options = []/*, array $extra = []*/)
    {
        if (in_array(Options::WORKSPACE_IMPORT, $options)) {
            return false;
        }

        return in_array($format, ['json', 'csv']);
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

    public function getOptions()
    {
        return [];
    }

    public function getSamples($format)
    {
        return [];
    }

    public function getExtraDefinition(array $options = [], array $extra = [])
    {
        return [];
    }

    public function export()
    {
        throw new \Exception('I don\'t plan to implements you anytime soon');
    }
}
