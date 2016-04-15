<?php

/**
 * Services for the badges linked with the exercices
 * To display the badge obtained by an user in his list of copies.
 */

namespace UJM\ExoBundle\Services\classes;

use Doctrine\Bundle\DoctrineBundle\Registry;

class BadgeExoService
{
    private $doctrine;

    /**
     * Constructor.
     *
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection;
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * to return infos badges for an exercise and an user.
     *
     *
     * @param int    $userId     id User
     * @param int    $resourceId id Claroline Resource
     * @param string $locale     given by container->getParameter('locale') FR, EN ....
     *
     * @return \Icap\BadgeBundle\Entity\Badge[]
     */
    public function badgesInfoUser($userId, $resourceId, $locale)
    {
        $em = $this->doctrine->getManager();
        $badgesInfoUser = array();
        $i = 0;

        $exoBadges = $this->getBadgeLinked($resourceId);
        foreach ($exoBadges as $badge) {
            $brs = $this->getBadgeRules($badge);
            if (count($brs) == 1) {
                $trans = $this->getBadgeTrans($badge, $locale);
                $badgesInfoUser[$i]['badgeName'] = $trans->getName();

                $userBadge = $this->getUserBadge($userId, $badge);
                if ($userBadge) {
                    $badgesInfoUser[$i]['issued'] = $userBadge->getIssuedAt();
                } else {
                    $badgesInfoUser[$i]['issued'] = -1;
                }

                ++$i;
            }
        }

        return $badgesInfoUser;
    }

    /**
     * to return badges linked with the exercise.
     *
     *
     * @param int $resourceId id Claroline Resource
     *
     * @return \Icap\BadgeBundle\Entity\Badge[]
     */
    private function getBadgeLinked($resourceId)
    {
        $badges = array();
        $em = $this->doctrine->getManager();
        $badgesRules = $em->getRepository('IcapBadgeBundle:BadgeRule')
                          ->findBy(array('resource' => $resourceId));

        foreach ($badgesRules as $br) {
            $badge = $em->getRepository('IcapBadgeBundle:Badge')
                          ->findBy(array('id' => $br->getAssociatedBadge(), 'deletedAt' => null));
            if ($badge) {
                $badges[] = $br->getAssociatedBadge();
            }
        }

        return $badges;
    }

    /**
     * get badge's rules.
     *
     *
     * @param \Icap\BadgeBundle\Entity\Badge $badge
     *
     * @return \Icap\BadgeBundle\Entity\BadgeRule[]
     */
    private function getBadgeRules($badge)
    {
        $em = $this->doctrine->getManager();
        $badgeRules = $em->getRepository('IcapBadgeBundle:BadgeRule')
                         ->findBy(array(
                              'associatedBadge' => $badge->getId(),
                           ));

        return $badgeRules;
    }

    /**
     * get badge translation.
     *
     *
     * @param \Icap\BadgeBundle\Entity\Badge $badge
     * @param string                         $locale given by container->getParameter('locale') FR, EN ....
     *
     * @return \Icap\BadgeBundle\Entity\BadgeTranslation
     */
    private function getBadgeTrans($badge, $locale)
    {
        $em = $this->doctrine->getManager();
        $badgeTranslation = $em->getRepository('IcapBadgeBundle:BadgeTranslation')
                               ->findOneBy(array(
                                    'badge' => $badge->getId(),
                                    'locale' => $locale,
                                 ));

        return $badgeTranslation;
    }

    /**
     * To verify if an user obtained the badge.
     *
     *
     * @param int                            $userId id User
     * @param \Icap\BadgeBundle\Entity\Badge $badge
     *
     * @return \Icap\BadgeBundle\Entity\UserBadge
     */
    private function getUserBadge($userId, $badge)
    {
        $em = $this->doctrine->getManager();
        $userBadge = $em->getRepository('IcapBadgeBundle:UserBadge')
                        ->findOneBy(array(
                                    'user' => $userId,
                                    'badge' => $badge->getId(),
                                   ));

        return $userBadge;
    }
}
