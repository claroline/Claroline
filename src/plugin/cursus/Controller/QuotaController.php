<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CursusBundle\Entity\Quota;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Event\Log\LogSubscriptionSetStatusEvent;
use Claroline\CursusBundle\Manager\QuotaManager;
use Claroline\CursusBundle\Manager\SessionManager;
use Dompdf\Dompdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/cursus_quota")
 */
class QuotaController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var QuotaManager */
    private $quotaManager;
    /** @var SessionManager */
    private $sessionManager;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $config,
        ObjectManager $om,
        QuotaManager $quotaManager,
        SessionManager $sessionManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
        $this->om = $om;
        $this->quotaManager = $quotaManager;
        $this->sessionManager = $sessionManager;
    }

    public function getName()
    {
        return 'cursus_quota';
    }

    public function getClass()
    {
        return Quota::class;
    }

    public function getIgnore()
    {
        return ['copyBulk', 'doc', 'exist'];
    }

    protected function getDefaultHiddenFilters()
    {
        $filters = [];
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            /** @var User */
            $user = $this->tokenStorage->getToken()->getUser();

            // filter by organization
            if ($user instanceof User) {
                $organizations = $user->getOrganizations();
            } else {
                $organizations = $this->om->getRepository(Organization::class)->findBy(['default' => true]);
            }

            $filters['organizations'] = array_unique(array_reduce($organizations, function (array $output, Organization $organization) {
                $output[] = $organization->getUuid();
                foreach ($organization->getChildren() as $child) {
                    $output[] = $child->getUuid();
                }

                return $output;
            }, []));
        }

        return $filters;
    }

    /**
     * @Route("/{id}/statistics", name="apiv2_cursus_quota_statistics", methods={"GET"})
     * @EXT\ParamConverter("quota", class="Claroline\CursusBundle\Entity\Quota", options={"mapping": {"id": "uuid"}})
     */
    public function getStatisticsAction(Quota $quota): JsonResponse
    {
        $this->checkPermission('VALIDATE_SUBSCRIPTIONS', null, [], true);

        /** @var SessionUserRepository */
        $repo = $this->om->getRepository(SessionUser::class);
        $sessionUsers = $repo->findByOrganization($quota->getOrganization());
        $statistics = [
            'total' => count($sessionUsers),
            'pending' => array_reduce($sessionUsers, function ($accum, $subscription) {
                return $accum + (SessionUser::STATUS_PENDING == $subscription->getStatus() ? 1 : 0);
            }, 0),
            'refused' => array_reduce($sessionUsers, function ($accum, $subscription) {
                return $accum + (SessionUser::STATUS_REFUSED == $subscription->getStatus() ? 1 : 0);
            }, 0),
            'validated' => array_reduce($sessionUsers, function ($accum, $subscription) {
                return $accum + (SessionUser::STATUS_VALIDATED == $subscription->getStatus() ? 1 : 0);
            }, 0),
        ];
        if ($quota->useQuotas()) {
            $statistics['managed'] = array_reduce($sessionUsers, function ($accum, $subscription) {
                return $accum + (SessionUser::STATUS_MANAGED == $subscription->getStatus() ? 1 : 0);
            }, 0);
            $statistics['calculated'] = array_reduce($sessionUsers, function ($accum, $subscription) {
                return SessionUser::STATUS_MANAGED == $subscription->getStatus() ? $accum + $subscription->getSession()->getQuotaDays() : $accum;
            }, 0);
        } else {
            $statistics['total'] = array_reduce($sessionUsers, function ($accum, $subscription) {
                return $accum + (SessionUser::STATUS_MANAGED != $subscription->getStatus() ? 1 : 0);
            }, 0);
        }

        return new JsonResponse($statistics);
    }

    /**
     * @Route("/{id}/pdf", name="apiv2_cursus_quota_export", methods={"GET"})
     * @EXT\ParamConverter("quota", class="Claroline\CursusBundle\Entity\Quota", options={"mapping": {"id": "uuid"}})
     */
    public function exportAction(Quota $quota, Request $request): StreamedResponse
    {
        $this->checkPermission('VALIDATE_SUBSCRIPTIONS', null, [], true);

        $organization = $quota->getOrganization();

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'organization' => $organization,
        ];

        if (!$quota->useQuotas()) {
            $query['hiddenFilters']['ignored_status'] = SessionUser::STATUS_MANAGED;
        }

        $subscriptions = $this->finder->searchEntities(SessionUser::class, $query)['data'];

        $domPdf = new Dompdf([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'tempDir' => $this->config->getParameter('server.tmp_dir'),
        ]);

        $domPdf->loadHtml($this->quotaManager->generateFromTemplate($quota, $subscriptions, $request->getLocale()));

        // Render the HTML as PDF
        $domPdf->render();

        return new StreamedResponse(function () use ($domPdf) {
            echo $domPdf->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($quota->getOrganization()->getName()).'.pdf',
        ]);
    }

    /**
     * @Route("/{id}/subscriptions", name="apiv2_cursus_quota_list_subscriptions", methods={"GET"})
     * @EXT\ParamConverter("quota", class="Claroline\CursusBundle\Entity\Quota", options={"mapping": {"id": "uuid"}})
     */
    public function listSubscriptionsAction(Quota $quota, Request $request): JsonResponse
    {
        $this->checkPermission('VALIDATE_SUBSCRIPTIONS', null, [], true);

        $organization = $quota->getOrganization();

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'organization' => $organization,
        ];

        if (!$quota->useQuotas()) {
            $query['hiddenFilters']['ignored_status'] = SessionUser::STATUS_MANAGED;
        }

        $options = isset($query['options']) ? $query['options'] : [];

        $queryParams = $this->finder->parseQueryParams($query);
        $page = $queryParams['page'];
        $limit = $queryParams['limit'];
        $filters = $queryParams['filters'];
        $allFilters = $queryParams['allFilters'];
        $sortBy = $queryParams['sortBy'];

        $count = $this->finder->fetch(SessionUser::class, $allFilters, $sortBy, $page, $limit, true);
        $data = $this->finder->fetch(SessionUser::class, $allFilters, $sortBy, $page, $limit);

        if (0 < $count && empty($data)) {
            $page = 0 !== $limit ? ceil($count / $limit) - 1 : 1;
            $data = $this->finder->fetch(SessionUser::class, $allFilters, $sortBy, $page, $limit);
        }

        if (!empty($sortBy) && 'startDate' == $sortBy['property']) {
            usort($data, function (SessionUser $a, SessionUser $b) use ($sortBy) {
                return $sortBy['direction'] * ($a->getSession()->getStartDate()->getTimestamp() - $b->getSession()->getStartDate()->getTimestamp());
            });
        }

        $results = FinderProvider::formatPaginatedData($data, $count, $page, $limit, $filters, $sortBy);

        return new JsonResponse(array_merge($results, [
            'data' => array_map(function ($result) use ($options) {
                return $this->serializer->serialize($result, $options);
            }, $results['data']),
        ]));
    }

    /**
     * @Route("/{id}/subscriptions/{sid}", name="apiv2_cursus_subscription_status", methods={"PATCH"})
     * @EXT\ParamConverter("quota", class="Claroline\CursusBundle\Entity\Quota", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("sessionUser", class="Claroline\CursusBundle\Entity\Registration\SessionUser", options={"mapping": {"sid": "uuid"}})
     */
    public function setSubscriptionStatusAction(Quota $quota, SessionUser $sessionUser, Request $request): JsonResponse
    {
        $this->checkPermission('VALIDATE_SUBSCRIPTIONS', null, [], true);

        $remark = $request->query->get('remark', '');

        $status = $request->query->get('status', null);
        if (null == $status) {
            return new JsonResponse('The status is missing.', 500);
        }

        if ($status < SessionUser::STATUS_PENDING || $status > SessionUser::STATUS_MANAGED) {
            return new JsonResponse('The status don\'t have been updated.', 500);
        }

        if ($sessionUser->getStatus() != $status) {
            // Execute action, dispatch event, send mail, etc
            switch ($status) {
                case SessionUser::STATUS_VALIDATED:
                    $this->sessionManager->addUsers($sessionUser->getSession(), [$sessionUser->getUser()]);
                    break;
                case SessionUser::STATUS_MANAGED:
                    if (null == $quota || !$quota->useQuotas()) {
                        return new JsonResponse('The status don\'t can be changed to managed.', 500);
                    }
                    $this->sessionManager->addUsers($sessionUser->getSession(), [$sessionUser->getUser()]);
                    break;
                case SessionUser::STATUS_PENDING:
                case SessionUser::STATUS_REFUSED:
                    $this->sessionManager->removeUsers($sessionUser->getSession(), [$sessionUser]);
                    break;
            }

            $sessionUser->setRemark($remark);
            $sessionUser->setStatus($status);
            $this->om->persist($sessionUser);
            $this->om->flush();

            $this->eventDispatcher->dispatch(new LogSubscriptionSetStatusEvent($sessionUser), 'log');
            $this->quotaManager->sendSetStatusMail($sessionUser);
        }

        return new JsonResponse([
            'subscription' => $this->serializer->serialize($sessionUser),
        ]);
    }

    /**
     * @Route("/{id}/open", name="apiv2_cursus_quota_open", methods={"GET"})
     * @EXT\ParamConverter("quota", class="Claroline\CursusBundle\Entity\Quota", options={"mapping": {"id": "uuid"}})
     */
    public function openAction(Quota $quota): JsonResponse
    {
        return new JsonResponse([
            'quota' => $this->serializer->serialize($quota),
        ]);
    }
}
