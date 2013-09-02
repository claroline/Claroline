<?php

namespace Claroline\CoreBundle\Library\Installation;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;

class AdditionalInstaller extends ContainerAware
{
    public function preInstall()
    {
        $templateDirectory = $this->container->getParameter('claroline.param.templates_directory');
        $defaultPath = "{$templateDirectory}default.zip";
        $translator = $this->container->get('translator');
        $translator->setLocale(
            $this->container->get('claroline.config.platform_config_handler')->getParameter('locale_language')
        );
        TemplateBuilder::buildDefault($defaultPath, $translator);
    }
}
