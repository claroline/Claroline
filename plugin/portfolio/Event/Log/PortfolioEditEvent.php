<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 11/14/17
 * Time: 1:59 PM.
 */

namespace Icap\PortfolioBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Icap\PortfolioBundle\Entity\Portfolio;

class PortfolioEditEvent extends LogGenericEvent implements NotifiableInterface
{
    const ACTION = 'portfolio-edit';

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
            [
                'owner' => [
                    'lastName' => $user->getLastName(),
                    'firstName' => $user->getFirstName(),
                ],
                'portfolio' => [
                    'id' => $this->portfolio->getId(),
                    'title' => $this->portfolio->getTitle(),
                    'slug' => $this->portfolio->getSlug(),
                ],
            ],
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

    /**
     * Get sendToFollowers boolean.
     *
     * @return bool
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
        $userIds = [];
        foreach ($this->portfolio->getPortfolioGuides() as $guide) {
            $userIds[] = $guide->getUser()->getId();
        }

        return $userIds;
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        return [];
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
        return 'portfolio';
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $notificationDetails = [
            'portfolio' => [
                'id' => $this->portfolio->getId(),
                'title' => $this->portfolio->getTitle(),
                'slug' => $this->portfolio->getSlug(),
            ],
        ];

        return $notificationDetails;
    }

    /**
     * Get if event is allowed to create notification or not.
     *
     * @return bool
     */
    public function isAllowedToNotify()
    {
        return true;
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_ADMIN];
    }
}
