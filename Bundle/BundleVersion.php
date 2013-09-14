<?php

namespace Claroline\InstallationBundle\Bundle;

class BundleVersion
{
    private $version;
    private $prettyVersion;
    private $dbVersion;

    public function __construct($version, $prettyVersion, $dbVersion = null)
    {
        $this->version = $version;
        $this->prettyVersion = $prettyVersion;
        $this->dbVersion = $dbVersion;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getPrettyVersion()
    {
        return $this->prettyVersion;
    }

    public function getDbVersion()
    {
        return $this->dbVersion;
    }
}
