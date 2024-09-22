<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Installation\DataFixtures;

use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateContent;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\InstallationBundle\Fixtures\PostInstallInterface;
use Claroline\InstallationBundle\Fixtures\PostUpdateInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

abstract class AbstractTemplateFixture extends AbstractFixture implements PostInstallInterface, PostUpdateInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected Environment $twig;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->twig = $container->get('twig');
    }

    /**
     * Get the name of the template type for which we want to load some default templates.
     */
    abstract protected static function getTemplateType(): string;

    /**
     * Get the list of default templates to create.
     */
    abstract protected function getSystemTemplates(): array;

    public function load(ObjectManager $manager): void
    {
        $toLoad = $this->getSystemTemplates();
        foreach ($toLoad as $templateName => $templateContents) {
            $this->loadSystemTemplate($manager, $templateName, $templateContents);
        }

        $manager->flush();
    }

    private function loadSystemTemplate(ObjectManager $om, string $templateName, array $contents): void
    {
        $this->logger->info(sprintf('DataFixtures : Load system template "%s" for type "%s"', $templateName, static::getTemplateType()));

        $templateTypeRepo = $om->getRepository(TemplateType::class);

        /** @var TemplateType $templateType */
        $templateType = $templateTypeRepo->findOneBy(['name' => static::getTemplateType()]);
        if (empty($templateType)) {
            $this->logger->warning(sprintf('DataFixtures : Template type %s does not exist.', static::getTemplateType()));

            return;
        }

        $template = $om->getRepository(Template::class)->findOneBy([
            'type' => $templateType,
            'name' => $templateName,
            'system' => true,
        ]);

        if (empty($template)) {
            // initialize new template
            $template = new Template();
            $template->setName($templateName);
            $template->setType($templateType);
            $template->setSystem(true);

            $om->persist($template);
        }

        // load default contents for the template
        foreach ($contents as $lang => $content) {
            $templateContent = $template->getTemplateContent($lang);
            if (empty($templateContent)) {
                $templateContent = new TemplateContent();
                $template->addTemplateContent($templateContent);
                $om->persist($templateContent);
            }

            $templateContent->setTitle($content['title'] ?? null);
            $templateContent->setContent($content['content'] ?? null);
            $templateContent->setLang($lang);
        }

        // set the default template for the type if missing
        if (empty($templateType->getDefaultTemplate())) {
            $templateType->setDefaultTemplate($template->getName());
            $om->persist($templateType);
        }
    }
}
