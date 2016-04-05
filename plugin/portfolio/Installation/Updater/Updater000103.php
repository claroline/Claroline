<?php
namespace Icap\PortfolioBundle\Installation\Updater;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;

class Updater000103
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
        $widgetType = $this->entityManager->getRepository("IcapPortfolioBundle:Widget\\WidgetType")->findOneBy(array('name' => 'presentation'));

        if (null !== $widgetType) {
            $widgetType
                ->setName('text')
                ->setIcon('align-left')
                ->setIsUnique(false);

            $this->entityManager->persist($widgetType);
            $this->entityManager->flush();
        }
    }

}