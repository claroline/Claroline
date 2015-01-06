<?php
namespace Icap\PortfolioBundle\Installation\Updater;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;

class Updater020003
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postUpdate()
    {
        $this->fixWidgetType();// Fix widget_type on abstract_widget, forget to do it in 0.1.3 additional installer
    }

    public function fixWidgetType(){
        /** @var \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[] $abstractWidgets */
        $abstractWidgets = $this->entityManager->getRepository("IcapPortfolioBundle:Widget\\AbstractWidget")->findOneBy(array('widget_type' => 'presentation'));

        foreach ($abstractWidgets as $abstractWidget) {
            $abstractWidget->setWidgetType('text');

            $this->entityManager->persist($abstractWidget);
        }

        $this->entityManager->flush();
    }

}