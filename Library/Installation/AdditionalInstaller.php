<?php

namespace Claroline\CoreBundle\Library\Installation;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;

class AdditionalInstaller extends ContainerAware
{
    public function preInstall()
    {
        $defaultTemplatePath = $this->container->getParameter('kernel.root_dir') . '/../templates/default.zip';
        $translator = $this->container->get('translator'); // useless
        TemplateBuilder::buildDefault($defaultTemplatePath, $translator);
    }
}
