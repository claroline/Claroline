<?php

namespace Claroline\WebInstaller;

use Symfony\Component\Yaml\Yaml;
use Claroline\CoreBundle\Library\Installation\Settings\DatabaseSettings;
use Claroline\CoreBundle\Library\Installation\Settings\MailingSettings;
use Claroline\CoreBundle\Library\Installation\Settings\PlatformSettings;

class Writer
{
    private $templateFile;
    private $mainFile;
    private $platformFile;
    private $installFlagFile;

    public function __construct(
        $mainTemplateFile,
        $mainParameterFile,
        $platformParameterFile,
        $installFlagFile
    )
    {
        $this->templateFile = $mainTemplateFile;
        $this->mainFile = $mainParameterFile;
        $this->platformFile = $platformParameterFile;
        $this->installFlagFile = $installFlagFile;
    }

    public function writeParameters(ParameterBag $parameters)
    {
        $this->writeMainParameters(
            $parameters->getDatabaseSettings(),
            $parameters->getPlatformSettings(),
            $parameters->getMailingSettings()
        );
        $this->writePlatformParameters($parameters->getPlatformSettings());
    }

    public function writeInstallFlag()
    {
        file_put_contents($this->installFlagFile, "<?php\n\nreturn true;\n");
    }

    private function writeMainParameters(
        DatabaseSettings $dbSettings,
        PlatformSettings $platformSettings,
        MailingSettings $mailSettings
    )
    {
        $defaultTemplateContent = file_get_contents($this->templateFile);
        $defaultParameters = Yaml::parse($defaultTemplateContent);
        $parameters = array(
            'database_driver' => $dbSettings->getDriver(),
            'database_host' => $dbSettings->getHost(),
            'database_name' => $dbSettings->getName(),
            'database_user' => $dbSettings->getUser(),
            'database_password' => $dbSettings->getPassword(),
            'database_port' => $dbSettings->getPort(),
            'mailer_transport' => $mailSettings->getTransport(),
            'mailer_encryption' => $mailSettings->getTransportOption('encryption'),
            'mailer_auth_mode' => $mailSettings->getTransportOption('auth_mode'),
            'mailer_host' => $mailSettings->getTransportOption('host'),
            'mailer_port' => $mailSettings->getTransportOption('port'),
            'mailer_user' => $mailSettings->getTransportOption('username'),
            'mailer_password' => $mailSettings->getTransportOption('password'),
            'locale' => $platformSettings->getLanguage(),
            'secret' => md5(rand(0, 10000000))
        );
        $parameters = array_merge($defaultParameters['parameters'], $parameters);
        $this->doWrite(array('parameters' => $parameters), $this->mainFile);
    }

    private function writePlatformParameters(PlatformSettings $settings)
    {
        $this->doWrite(
            array(
                'name' => $settings->getName(),
                'support_email' => $settings->getSupportEmail(),
                'locale_language' => $settings->getLanguage()
            ),
            $this->platformFile
        );
    }

    private function doWrite(array $parameters, $file)
    {
        file_put_contents($file, Yaml::dump($parameters));
    }
}
