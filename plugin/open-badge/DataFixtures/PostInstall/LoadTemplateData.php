<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\DataFixtures\PostInstall;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTemplateData extends AbstractFixture implements ContainerAwareInterface
{
    public function load(ObjectManager $om)
    {
        $translator = $this->container->get('translator');
        $parameters = $this->container->get('Claroline\CoreBundle\API\Serializer\ParametersSerializer')->serialize([Options::SERIALIZE_MINIMAL]);

        $templateTypeRepo = $om->getRepository(TemplateType::class);

        $templateType = $templateTypeRepo->findOneBy(['name' => 'badge_certificate']);

        if ($templateType) {
            foreach ($parameters['locales']['available'] as $locale) {
                $template = new Template();
                $template->setType($templateType);
                $template->setName('badge_certificate');
                $template->setLang($locale);
                $content = '<div style="background-color: #f2ede7; padding: 20px;">'.
                    '<div style="text-align: left;"><strong>%issuer_name%</strong></div>'.
                    '<br /><hr /><br />'.
                    '<h1 style="text-align: center;">%badge_image% %badge_name%</h1>'.
                    '<div style="text-align: center;">%badge_description%</div>'.
                    '<br /><hr /><br />'.
                    '<div style="text-align: center;">'.$translator->trans('badge_awarded_to', [], 'template', $locale).'</div>'.
                    '<h2 style="text-align: center;">%first_name% %last_name%</h2>'.
                    '</div>';
                $template->setContent($content);
                $om->persist($template);
            }
            $templateType->setDefaultTemplate('badge_certificate');
            $om->persist($templateType);
        }

        $om->flush();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
