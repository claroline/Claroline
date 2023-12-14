<?php

namespace Claroline\CoreBundle\Event\DataSource;

use Claroline\AppBundle\Event\DataConveyorEventInterface;
use Claroline\AppBundle\Event\MandatoryEventInterface;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * An event which is fired when a DataSource is requested.
 *
 * The DataSource MUST populate the event and can be configured with an `options` array.
 */
class GetDataEvent extends Event implements MandatoryEventInterface, DataConveyorEventInterface
{
    /** @var string */
    private $context;

    /** @var Workspace */
    private $workspace;

    /** @var User */
    private $user;

    /**
     * A list of options to configure the DataSource.
     *
     * @var array
     */
    private $options;

    /**
     * The data returned by the source.
     */
    private $data;

    /**
     * Is the event correctly populated ?
     *
     * @var bool
     */
    private $populated = false;

    public function __construct(string $context, array $options = [], User $user = null, Workspace $workspace = null)
    {
        $this->context = $context;
        $this->options = $options;
        $this->user = $user;
        $this->workspace = $workspace;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Get the current options of the DataSource.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get the data provided by the DataSource.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the event data.
     */
    public function setData($data)
    {
        $this->data = $data;
        $this->populated = true;
    }

    /**
     * Check if the event is correctly populated.
     *
     * @return bool
     */
    public function isPopulated()
    {
        return $this->populated;
    }
}
