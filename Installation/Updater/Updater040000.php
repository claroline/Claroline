<?php
namespace Icap\PortfolioBundle\Installation\Updater;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Widget\WidgetType;

class Updater040000
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

    public function UpdateWidgetType() {
        /** @var \Icap\PortfolioBundle\Entity\Widget\WidgetType|null $widgetType */
        $widgetType = $this->entityManager->getRepository("IcapPortfolioBundle:Widget\\WidgetType")->findOneBy(array('name' => 'experience'));

        if (null === $widgetType) {
            $widgetType = new WidgetType();
            $widgetType
                ->setName('experience')
                ->setIcon('briefcase')
                ->setIsUnique(false)
                ->setIsDeletable(true);

            $this->entityManager->persist($widgetType);
            $this->entityManager->flush();
        }
    }

}