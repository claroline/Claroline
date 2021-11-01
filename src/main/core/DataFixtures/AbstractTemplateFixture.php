<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures;

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateContent;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

abstract class AbstractTemplateFixture extends AbstractFixture implements ContainerAwareInterface, LoggerAwareInterface
{
    use LoggableTrait;

    /** @var Environment */
    protected $twig;

    public function setContainer(ContainerInterface $container = null)
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

    public function load(ObjectManager $om)
    {
        $toLoad = $this->getSystemTemplates();
        foreach ($toLoad as $templateName => $templateContents) {
            $this->loadSystemTemplate($om, $templateName, $templateContents);
        }

        $om->flush();
    }

    private function loadSystemTemplate(ObjectManager $om, string $templateName, array $contents)
    {
        $this->log(sprintf('DataFixtures : Load system template "%s" for type "%s"', $templateName, static::getTemplateType()));

        $templateTypeRepo = $om->getRepository(TemplateType::class);

        /** @var TemplateType $templateType */
        $templateType = $templateTypeRepo->findOneBy(['name' => static::getTemplateType()]);
        if (empty($templateType)) {
            $this->log(sprintf('DataFixtures : Template type %s does not exist.', static::getTemplateType()), LogLevel::WARNING);

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
