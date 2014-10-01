<?php

namespace Icap\PortfolioBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Icap\NotificationBundle\Entity\NotifiableInterface;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioEvaluator;

class PortfolioRemoveEvaluatorEvent extends LogGenericEvent implements NotifiableInterface
{
    const ACTION = 'portfolio-remove_evaluator';

    /**
     * @var \Icap\PortfolioBundle\Entity\Portfolio
     */
    protected $portfolio;

    /**
     * @var \Icap\PortfolioBundle\Entity\PortfolioEvaluator
     */
    protected $portfolioEvaluator;

    /**
     * Constructor.
     *
     * @param Portfolio          $portfolio
     * @param PortfolioEvaluator $portfolioEvaluator
     */
    public function __construct(Portfolio $portfolio, PortfolioEvaluator $portfolioEvaluator)
    {
        $this->portfolio          = $portfolio;
        $this->portfolioEvaluator = $portfolioEvaluator;

        $user     = $portfolio->getUser();

        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->portfolio->getTitleWidget();

        parent::__construct(
            self::ACTION,
            array(
                'owner' => array(
                    'lastName'  => $user->getLastName(),
                    'firstName' => $user->getFirstName()
                ),
                'portfolio' => array(
                    'id'    => $this->portfolio->getId(),
                    'title' => $titleWidget->getTitle(),
                    'slug'  => $titleWidget->getSlug()
                )
            ),
            $portfolioEvaluator->getUser(),
            null,
            null,
            null,
            null,
            $user
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_ADMIN);
    }

    /**
     * Get sendToFollowers boolean.
     *
     * @return boolean
     */
    public function getSendToFollowers()
    {
        return true;
    }

    /**
     * Get includeUsers array of user ids.
     *
     * @return array
     */
    public function getIncludeUserIds()
    {
        return array($this->getReceiver()->getId());
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        return array();
    }

    /**
     * Get actionKey string.
     *
     * @return string
     */
    public function getActionKey()
    {
        return $this::ACTION;
    }

    /**
     * Get iconKey string.
     *
     * @return string
     */
    public function getIconKey()
    {
        return "portfolio";
    }

    /**
     * Get details
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $receiver = $this->getReceiver();

        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->portfolio->getTitleWidget();

        $notificationDetails = array(
            'portfolio' => array(
                'id'    => $this->portfolio->getId(),
                'title' => $titleWidget->getTitle(),
                'slug'  => $titleWidget->getSlug()
            ),
            'evaluator'  => array(
                'id'        => $receiver->getId(),
                'publicUrl' => $receiver->getPublicUrl(),
                'lastName'  => $receiver->getLastName(),
                'firstName' => $receiver->getFirstName()
            )
        );

        return $notificationDetails;
    }

    /**
     * Get if event is allowed to create notification or not
     *
     * @return boolean
     */
    public function isAllowedToNotify()
    {
        return true;
    }
}
