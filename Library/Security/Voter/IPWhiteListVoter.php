<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Yaml\Yaml;

/**
 * This voter grants access to admin users, whenever the attribute or the
 * class is. This means that administrators are seen by the AccessDecisionManager
 * as if they have all the possible roles and permissions on every object or class.
 *
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class IPWhiteListVoter implements VoterInterface
{
    //claroline.ip_range_white_list_file
    /**
     * @DI\InjectParams({
     *     "whiteListRange" = @DI\Inject("%claroline.ip_range_white_list_file%"),
     *     "whiteList"      = @DI\Inject("%claroline.ip_white_list_file%")
     * })
     */
    public function __construct($whiteListRange, $whiteList)
    {
        $this->whiteListRange = $whiteListRange;
        $this->whiteList      = $whiteList;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        return $this->isWhiteListed() ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_ABSTAIN;
    }

    protected function isWhiteListed()
    {
        if (file_exists($this->whiteList)) {
            $ips = Yaml::parse($this->whiteList);

            foreach ($ips as $ip) {
                if ($ip === $_SERVER['REMOTE_ADDR']) return true;
            }
        }

        if (file_exists($this->whiteListRange)) {
            $ranges = $ips = Yaml::parse($this->whiteListRange);

            foreach ($ranges as $range) {
                if ($this->validateRange($range['lower_bound'], $range['higher_bound'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }

    private function validateRange($lowerBound, $higherBound)
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        return (ip2long($ip) <= ip2long($higherBound) && ip2long($lowerBound) <= ip2long($ip));

    }
}
