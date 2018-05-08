<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Profile\ProfileLinksEvent;
use Claroline\CoreBundle\Manager\FacetManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Controller of the user profile.
 *
 * @todo move remaining method in correct controllers
 */
class ProfileController extends Controller
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var FacetManager */
    private $facetManager;

    /**
     * @DI\InjectParams({
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "facetManager"    = @DI\Inject("claroline.manager.facet_manager")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param StrictDispatcher      $eventDispatcher
     * @param FacetManager          $facetManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        StrictDispatcher $eventDispatcher,
        FacetManager $facetManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->facetManager = $facetManager;
    }

    /**
     * @EXT\Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function myProfileWidgetAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ('anon.' === $user) {
            return ['isAnon' => true];
        } else {
            $facets = $this->facetManager->getVisibleFacets();
            $fieldFacetValues = $this->facetManager->getFieldValuesByUser($user);
            $fieldFacets = $this->facetManager->getVisibleFieldForCurrentUserFacets();
            $profileLinksEvent = new ProfileLinksEvent($user, $request->getLocale());
            $publicProfilePreferences = $this->facetManager->getVisiblePublicPreference();
            $this->eventDispatcher->dispatch(
                'profile_link_event',
                $profileLinksEvent
            );
            $desktopBadgesEvent = new DisplayToolEvent();
            $this->eventDispatcher->dispatch(
                'list_all_my_badges',
                $desktopBadgesEvent
            );

            //Test profile completeness
            $totalVisibleFields = count($fieldFacets);
            $totalFilledVisibleFields = count(array_filter($fieldFacetValues));
            if ($publicProfilePreferences['baseData']) {
                ++$totalVisibleFields;
                if (!empty($user->getDescription())) {
                    ++$totalFilledVisibleFields;
                }
            }
            if ($publicProfilePreferences['phone']) {
                ++$totalVisibleFields;
                if (!empty($user->getPhone())) {
                    ++$totalFilledVisibleFields;
                }
            }

            $completion = 0 === $totalVisibleFields ? null : round($totalFilledVisibleFields / $totalVisibleFields * 100);
            $links = $profileLinksEvent->getLinks();

            return [
                'user' => $user,
                'publicProfilePreferences' => $publicProfilePreferences,
                'facets' => $facets,
                'fieldFacetValues' => $fieldFacetValues,
                'fieldFacets' => $fieldFacets,
                'links' => $links,
                'badges' => $desktopBadgesEvent->getContent(),
                'completion' => $completion,
                'isAnon' => false,
            ];
        }
    }
}
