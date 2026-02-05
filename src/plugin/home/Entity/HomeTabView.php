<?php

namespace Claroline\HomeBundle\Entity;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\AbstractUserView;
use Claroline\HomeBundle\Finder\HomeTabViewType;
use Claroline\HomeBundle\Repository\HomeTabViewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table('claro_home_tab_view')]
#[ORM\Entity(repositoryClass: HomeTabViewRepository::class)]
#[CrudEntity(finderClass: HomeTabViewType::class)]
class HomeTabView extends AbstractUserView
{
    #[ORM\JoinColumn(name: 'home_tab_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: HomeTab::class)]
    private ?HomeTab $homeTab = null;

    public function getSubject(): HomeTab
    {
        return $this->homeTab;
    }

    /** @param HomeTab $subject */
    public function setSubject(object $subject): void
    {
        $this->homeTab = $subject;
    }
}
