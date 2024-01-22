<?php

namespace Claroline\PrivacyBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateContent;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;
use Claroline\PrivacyBundle\Manager\PrivacyManager;
use Doctrine\Persistence\ObjectRepository;

class Updater141000 extends Updater
{
    private PlatformConfigurationHandler $config;
    private PrivacyManager $privacyManager;
    private ObjectManager $om;
    private ObjectRepository $templateTypeRepo;

    public function __construct(
        PlatformConfigurationHandler $config,
        PrivacyManager $privacyManager,
        ObjectManager $om
    ) {
        $this->config = $config;
        $this->privacyManager = $privacyManager;
        $this->om = $om;
        $this->templateTypeRepo = $om->getRepository(TemplateType::class);
    }

    public function postUpdate(): void
    {
        $tosTextFr = $this->config->getParameter('tos.text.fr');
        $tosTextEn = $this->config->getParameter('tos.text.en');
        $tosEnabled = $this->config->getParameter('tos.enabled');

        $privacyParameters = $this->privacyManager->getParameters();
        $privacyParameters->setTosEnabled($tosEnabled);

        $template = new Template();
        $template->setName('terms_of_service');
        $template->setType($this->templateTypeRepo->findOneBy(['name' => 'terms_of_service']));

        $templateContentFr = new TemplateContent();
        $templateContentFr->setLang('fr');
        $templateContentFr->setTitle('Conditions d\'utilisation');
        $templateContentFr->setContent($tosTextFr);
        $templateContentFr->setTemplate($template);

        $this->om->persist($templateContentFr);

        $templateContentEn = new TemplateContent();
        $templateContentEn->setLang('en');
        $templateContentEn->setTitle('Terms of Service');
        $templateContentEn->setContent($tosTextEn);
        $templateContentEn->setTemplate($template);

        $this->om->persist($templateContentEn);

        $template->addTemplateContent($templateContentFr);
        $template->addTemplateContent($templateContentEn);

        $this->om->persist($template);
        $this->om->flush();

        $privacyParameters->setTosTemplate($template);
        $this->privacyManager->updateParameters($privacyParameters);
    }
}
