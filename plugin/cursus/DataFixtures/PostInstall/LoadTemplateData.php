<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\DataFixtures\PostInstall;

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

        $sessionInvitationType = $templateTypeRepo->findOneBy(['name' => 'session_invitation']);
        $eventInvitationType = $templateTypeRepo->findOneBy(['name' => 'session_event_invitation']);
        $sessionCertificateMailType = $templateTypeRepo->findOneBy(['name' => 'session_certificate_mail']);
        $eventCertificateMailType = $templateTypeRepo->findOneBy(['name' => 'session_event_certificate_mail']);
        $adminCertificateMailType = $templateTypeRepo->findOneBy(['name' => 'admin_certificate_mail']);

        if ($sessionInvitationType) {
            foreach ($parameters['locales']['available'] as $locale) {
                $template = new Template();
                $template->setType($sessionInvitationType);
                $template->setName('session_invitation');
                $template->setLang($locale);
                $template->setTitle($translator->trans('session_invitation', [], 'template', $locale));
                $content = '%session_name%<br/>';
                $content .= '[%session_start% -> %session_end%]<br/>';
                $content .= '%session_description%';
                $template->setContent($content);
                $om->persist($template);
            }
            $sessionInvitationType->setDefaultTemplate('session_invitation');
            $om->persist($sessionInvitationType);
        }
        if ($eventInvitationType) {
            foreach ($parameters['locales']['available'] as $locale) {
                $template = new Template();
                $template->setType($eventInvitationType);
                $template->setName('session_event_invitation');
                $template->setLang($locale);
                $template->setTitle($translator->trans('session_event_invitation', [], 'template', $locale));
                $content = '%event_name%<br/>';
                $content .= '[%event_start% -> %event_end%]<br/>';
                $content .= '%event_description%<br/><br/>';
                $content .= '%event_location_address%<br/>';
                $content .= '%event_location_extra%';
                $template->setContent($content);
                $om->persist($template);
            }
            $eventInvitationType->setDefaultTemplate('session_event_invitation');
            $om->persist($eventInvitationType);
        }
        if ($sessionCertificateMailType) {
            foreach ($parameters['locales']['available'] as $locale) {
                $template = new Template();
                $template->setType($sessionCertificateMailType);
                $template->setName('session_certificate_mail');
                $template->setLang($locale);
                $template->setTitle($translator->trans('session_certificate_email_title', [], 'cursus', $locale));

                $content = '<div>'.$translator->trans('session_certificate_email', [], 'cursus', $locale).'</div>';
                $content .= '<br/>';
                $content .= '<a href="%certificate_link%">'.$translator->trans('certificate', [], 'cursus', $locale).'</a>';
                $template->setContent($content);
                $om->persist($template);
            }
            $sessionCertificateMailType->setDefaultTemplate('session_certificate_mail');
            $om->persist($sessionCertificateMailType);
        }
        if ($eventCertificateMailType) {
            foreach ($parameters['locales']['available'] as $locale) {
                $template = new Template();
                $template->setType($eventCertificateMailType);
                $template->setName('session_event_certificate_mail');
                $template->setLang($locale);
                $template->setTitle($translator->trans('session_event_certificate_email_title', [], 'cursus', $locale));

                $content = '<div>'.$translator->trans('session_event_certificate_email', [], 'cursus', $locale).'</div>';
                $content .= '<br/>';
                $content .= '<a href="%certificate_link%">'.$translator->trans('certificate', [], 'cursus', $locale).'</a>';
                $template->setContent($content);
                $om->persist($template);
            }
            $eventCertificateMailType->setDefaultTemplate('session_event_certificate_mail');
            $om->persist($eventCertificateMailType);
        }
        if ($adminCertificateMailType) {
            foreach ($parameters['locales']['available'] as $locale) {
                $template = new Template();
                $template->setType($adminCertificateMailType);
                $template->setName('admin_certificate_mail');
                $template->setLang($locale);
                $template->setTitle($translator->trans('new_certificates', [], 'cursus', $locale));

                $content = '<div>'.$translator->trans('new_certificates', [], 'cursus', $locale).'</div>';
                $content .= '%certificates_link%';
                $template->setContent($content);
                $om->persist($template);
            }
            $adminCertificateMailType->setDefaultTemplate('admin_certificate_mail');
            $om->persist($adminCertificateMailType);
        }

        $om->flush();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
