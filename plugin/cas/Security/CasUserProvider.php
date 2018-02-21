<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/8/17
 */

namespace Claroline\CasBundle\Security;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CasBundle\Entity\CasUser;
use Claroline\CasBundle\Repository\CasUserRepository;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Repository\UserRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class CasUserProvider.
 *
 * @DI\Service("claroline.cas.user_provider")
 */
class CasUserProvider implements UserProviderInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var Session */
    private $session;
    /** @var UserRepository */
    private $userRepo;
    /** @var CasUserRepository */
    private $casUserRepo;
    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;

    /**
     * CasUserProvider constructor.
     *
     * @DI\InjectParams({
     *   "om"                       = @DI\Inject("claroline.persistence.object_manager"),
     *   "session"                  = @DI\Inject("session"),
     *   "platformConfigHandler"    = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * @param ObjectManager                $om
     * @param Session                      $session
     * @param PlatformConfigurationHandler $platformConfigHandler
     */
    public function __construct(
        ObjectManager $om,
        Session $session,
        PlatformConfigurationHandler $platformConfigHandler
    ) {
        $this->om = $om;
        $this->session = $session;
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->casUserRepo = $om->getRepository('ClarolineCasBundle:CasUser');
        $this->platformConfigHandler = $platformConfigHandler;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $casUser = $this->casUserRepo->findOneBy(['casId' => $username]);
        if (!empty($casUser)) {
            return $casUser->getUser();
        }

        if ($this->platformConfigHandler->getParameter('direct_third_party_authentication')) {
            $user = $this->userRepo->loadUserByUsername($username);
            $casUser = new CasUser($username, $user);
            $this->om->persist($casUser);
            $this->om->flush();

            return $user;
        }
        $this->session->set('CAS_AUTHENTICATION_USER_ID', $username);

        throw new UsernameNotFoundException(
            sprintf('Unable to find an active user identified by "%s".', $username)
        );
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->userRepo->refreshUser($user);
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $this->userRepo->supportsClass($class);
    }
}
