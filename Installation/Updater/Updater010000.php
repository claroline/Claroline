<?php
namespace Icap\PortfolioBundle\Installation\Updater;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;

class Updater010000
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postUpdate()
    {
        $this->UpdateWidgetType();
    }

    public function UpdateWidgetType(){
        /** @var \Icap\PortfolioBundle\Entity\Widget\WidgetType|null $widgetType */
        $widgetType = $this->entityManager->getRepository("IcapPortfolioBundle:Widget\\WidgetType")->findOneBy(array('name' => 'badges'));

        if (null === $widgetType) {
            $widgetType
                ->setName('badges')
                ->setIcon('trophy')
                ->setIsUnique(false)
                ->setIsDeletable(true);

            $this->entityManager->persist($widgetType);
            $this->entityManager->flush();
        }
    }

}