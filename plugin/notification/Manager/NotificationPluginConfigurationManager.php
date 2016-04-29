<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/14/15
 */

namespace Icap\NotificationBundle\Manager;

use Doctrine\ORM\EntityManager;
use Icap\NotificationBundle\Entity\NotificationPluginConfiguration;
use Icap\NotificationBundle\Exception\InvalidNotificationFormException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NotificationPluginConfigurationManager.
 *
 * @DI\Service("icap.notification.manager.plugin_configuration")
 */
class NotificationPluginConfigurationManager
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $notificationPluginConfigurationRepository;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @DI\InjectParams({
     *      "em"            = @DI\Inject("doctrine.orm.entity_manager"),
     *      "formFactory"   = @DI\Inject("form.factory")
     * })
     */
    public function __construct(EntityManager $em, FormFactoryInterface $formFactory)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->notificationPluginConfigurationRepository =
            $em->getRepository('IcapNotificationBundle:NotificationPluginConfiguration');
    }

    /**
     * @return NotificationPluginConfiguration|null
     */
    public function getConfigOrEmpty()
    {
        $result = $this->notificationPluginConfigurationRepository->findAll();
        $config = null;
        if (count($result) > 0) {
            $config = $result[0];
        } else {
            $config = new NotificationPluginConfiguration();
        }

        return $config;
    }

    /**
     * @param NotificationPluginConfiguration $config
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm(NotificationPluginConfiguration $config = null)
    {
        if ($config === null) {
            $config = $this->getConfigOrEmpty();
        }
        $form = $this->formFactory->create(
            'icap_notification_type_pluginConfiguration',
            $config
        );

        return $form;
    }

    public function processForm(Request $request)
    {
        $form = $this->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $config = $form->getData();
            $this->em->persist($config);
            $this->em->flush();

            return $form;
        }

        throw new InvalidNotificationFormException('invalid_parameters', $form);
    }
}
