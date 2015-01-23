<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Workspace;

use \RuntimeException;
use Claroline\CoreBundle\Library\Transfert\Resolver;
use Claroline\CoreBundle\Entity\User;

class Configuration
{
    private $workspaceName;
    private $workspaceCode;
    private $workspaceDescription;
    private $displayable = false;
    private $selfRegistration = false;
    private $registrationValidation = false;
    private $selfUnregistration = false;
    private $templateFile;
    private $extractPath;
    private $owner = null;

    public function __construct($path)
    {
        $this->templateFile = $path;

        //Default.zip is the template used for creating users.
        //Therefore we don't want to extract it every time.
        if (strpos($path, 'default.zip')) {
            $rootPath = str_replace('default.zip', '', $path);
            $extractPath = $rootPath . "default";

            if (!is_dir($this->extractPath)) {
                $archive = new \ZipArchive();
                if (true === $code = $archive->open($path)) {
                    $this->extract($extractPath, $archive);
                }
            }
        } else {
            $archive = new \ZipArchive();
            if (true === $code = $archive->open($path)) {
                $extractPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
                $this->extract($extractPath, $archive);
                //set the default properties of the workspace here if we can find them.
            } else {
                throw new \Exception(
                    "Couldn't open template archive '{$path}' (error {$code})"
                );
            }
        }
    }

    /**
     * Assume the archive is already opened.
     *
     * @param $extractPath
     * @param $archive
     */
    private function extract($extractPath, $archive)
    {
        $res = $archive->extractTo($extractPath);
        $archive->close();
        $this->setExtractPath($extractPath);
        $resolver = new Resolver($extractPath);
        $this->data = $resolver->resolve();
    }

    /**
     * @todo this method is useless (constructor should be enough now)
     */
    public static function fromTemplate($templateFile)
    {
        return new self($templateFile);
    }

    public function getArchive()
    {
        return $this->templateFile;
    }

    public function setWorkspaceType($type)
    {
        $this->workspaceType = $type;
    }

    public function getWorkspaceType()
    {
        return $this->workspaceType;
    }

    public function setWorkspaceName($name)
    {
        $this->workspaceName = $name;
    }

    public function getWorkspaceName()
    {
        return $this->workspaceName;
    }

    public function check()
    {
        if (!is_string($this->workspaceName) || 0 === strlen($this->workspaceName)) {
            throw new RuntimeException('Workspace name must be a non empty string');
        }
    }

    public function setWorkspaceCode($workspaceCode)
    {
        $this->workspaceCode = $workspaceCode;
    }

    public function getWorkspaceCode()
    {
        return $this->workspaceCode;
    }

    public function setWorkspaceDescription($workspaceDescription)
    {
        $this->workspaceDescription = $workspaceDescription;
    }

    public function getWorkspaceDescription()
    {
        return $this->workspaceDescription;
    }

    public function setArchive($templateFile)
    {
        $this->templateFile = $templateFile;
    }

    public function setDisplayable($displayable)
    {
        $this->displayable = $displayable;
    }

    public function isDisplayable()
    {
        return $this->displayable;
    }

    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration = $selfRegistration;
    }

    public function getSelfRegistration()
    {
        return $this->selfRegistration;
    }

    public function setSelfUnregistration($selfUnregistration)
    {
        $this->selfUnregistration = $selfUnregistration;
    }

    public function getSelfUnregistration()
    {
        return $this->selfUnregistration;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setExtractPath($path)
    {
        $this->extractPath = $path;
    }

    public function getExtractPath()
    {
        return $this->extractPath;
    }

    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function getRegistrationValidation()
    {
        return $this->registrationValidation;
    }

    public function setRegistrationValidation($registrationValidation)
    {
        $this->registrationValidation = $registrationValidation;
    }
}
