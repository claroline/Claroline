<?php

namespace Icap\PortfolioBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Widget\WidgetType;
use Icap\PortfolioBundle\Event\WidgetTypeCreateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MigrationUpdater extends Updater
{
    public function postInstall(EntityManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $widgetTypeCreateEvent = new WidgetTypeCreateEvent();

        $eventDispatcher->dispatch('icap_portfolio_widget_type_create', $widgetTypeCreateEvent);

        /** @var \Icap\PortfolioBundle\Repository\Widget\WidgetTypeRepository $widgetTypeRepository */
        $widgetTypeRepository = $entityManager->getRepository('IcapPortfolioBundle:Widget\WidgetType');

        $widgetType = $widgetTypeCreateEvent->getWidgetType();

        $existedWidgetType = $widgetTypeRepository->createQueryBuilder('widgetType')
            ->where('widgetType.name = :badgetWidgetTypeName')
            ->setParameter('badgetWidgetTypeName', $widgetType->getName())
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $existedWidgetType) {
            $entityManager->persist($widgetType);
            $this->log($widgetType->getName().' widget type created.');
        }

        $entityManager->flush();
    }
}
