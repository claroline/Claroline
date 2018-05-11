<?php

namespace Icap\OAuthBundle\Installation;

use Claroline\InstalationBundle\Additional\AdditinalInstaller as BaseInstaller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    private $logger;

    public function __construct()
    {
        $self = $this;
        $this->logger = function ($message) use ($self) {
            $self->log($message);
        };
    }

    public function preUpdate($currentVersion, $targetVersion)
    {
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
    }
}
