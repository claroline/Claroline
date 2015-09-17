<?php

namespace Icap\PortfolioBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogNotRepeatableInterface;
use Icap\PortfolioBundle\Entity\Portfolio;

class PortfolioViewEvent extends LogGenericEvent implements LogNotRepeatableInterface
{
    const ACTION = 'portfolio-view';

    /**
     * @var \Icap\PortfolioBundle\Entity\Portfolio
     */
    protected $portfolio;

    /**
     * @param Portfolio $portfolio
     */
    public function __construct(Portfolio $portfolio)
    {
        $this->portfolio = $portfolio;

        $user = $portfolio->getUser();

        parent::__construct(
            self::ACTION,
            array(
                'owner' => array(
                    'lastName'  => $user->getLastName(),
                    'firstName' => $user->getFirstName()
                ),
                'portfolio' => array(
                    'id'    => $this->portfolio->getId(),
                    'title' => $this->portfolio->getTitle(),
                    'slug'  => $this->portfolio->getSlug()
                )
            ),
            null,
            null,
            null,
            null,
            null,
            $user,
            null,
            null,
            $this->portfolio->getId()
        );
    }

    public function getLogSignature()
    {
        return self::ACTION . '_' . $this->portfolio->getId();
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_ADMIN);
    }
}
 