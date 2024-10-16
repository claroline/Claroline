<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\VersionManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlatformListener
{
    private AuthorizationCheckerInterface $authorization;

    private TokenStorageInterface $tokenStorage;

    private TranslatorInterface $translator;

    private PlatformConfigurationHandler $config;

    private VersionManager $versionManager;

    private TempFileManager $tempManager;

    private LocaleManager $localeManager;

    private RoutingHelper $routingHelper;

    /**
     * The list of public routes of the application.
     * NB. This is not the best place to declare it.
     *
     * @var array
     */
    public const PUBLIC_ROUTES = [
        // to let admin log in
        'claro_security_login',
        // to be able to render client UI
        'claro_index',
        'fos_js_routing_js',
        // to have access to debug tools
        '_wdt',
        '_profiler',

        // open reactivation routes
        'apiv2_platform_extend',
        'apiv2_platform_enable',
    ];

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        PlatformConfigurationHandler $config,
        VersionManager $versionManager,
        TempFileManager $tempManager,
        LocaleManager $localeManager,
        RoutingHelper $routingHelper
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->config = $config;
        $this->versionManager = $versionManager;
        $this->tempManager = $tempManager;
        $this->localeManager = $localeManager;
        $this->routingHelper = $routingHelper;
    }

    /**
     * Sets the platform language.
     */
    public function setLocale(RequestEvent $event)
    {
        if ($event->isMainRequest()) {
            $request = $event->getRequest();

            $locale = $this->localeManager->getUserLocale($request);
            $request->setLocale($locale);
        }
    }

    /**
     * Checks the app availability before displaying the platform.
     */
    public function checkAvailability(RequestEvent $event)
    {
        if (!$event->isMainRequest() || in_array($event->getRequest()->get('_route'), static::PUBLIC_ROUTES)) {
            return;
        }

        // checks platform restrictions
        if ($this->config->getParameter('restrictions.disabled')) {
            throw new HttpException(503, 'Platform is not available (Platform is disabled).');
        }

        // checks platform maintenance
        if (MaintenanceHandler::isMaintenanceEnabled() || $this->config->getParameter('maintenance.enable')) {
            // only disable for non admin
            // TODO : it may break the impersonation mode
            if (!$this->authorization->isGranted(PlatformRoles::ADMIN)) {
                throw new HttpException(503, 'Platform is not available (Platform is under maintenance).');
            }
        }
    }

    /**
     * Clears all temp files at the end of each request.
     */
    public function clearTemp()
    {
        $this->tempManager->clear();
    }

    /**
     * Display new version changelogs to administrators.
     */
    public function displayVersionChangeLogs(GenericDataEvent $event): void
    {
        $event->setResponse(array_merge(
            $this->getChangelogs(),
            $this->getSupportMessages(),
        ));
    }

    private function getChangelogs(): array
    {
        if (!$this->config->getParameter('changelogMessage.enabled')) {
            return [];
        }

        $roles = $this->config->getParameter('changelogMessage.roles');
        if (empty(array_intersect($this->tokenStorage->getToken()->getRoleNames(), $roles))) {
            return [];
        }

        $installationDate = $this->versionManager->getInstallationDate($this->versionManager->getCurrentMinor());
        if (empty($installationDate)) {
            return [];
        }

        $period = new \DateInterval($this->config->getParameter('changelogMessage.duration'));
        $endDate = $installationDate->add($period);
        $now = new \DateTime();
        if ($now > $endDate) {
            return [];
        }

        $user = $this->tokenStorage->getToken()->getUser();

        $locale = $this->localeManager->getLocale($user);
        $content = $this->versionManager->getChangelogs($locale).'<br/><br/>';
        $content .= '<em>'.$this->translator->trans('platform_changelog_display', [
            '%roles%' => implode(', ', $this->config->getParameter('changelogMessage.roles')),
            '%end_date%' => $endDate->format('d/m/Y'),
        ], 'platform').'</em>';

        return [
            [
                'id' => 'new-version',
                'title' => $this->translator->trans('platform_new_available_version', [], 'platform'),
                'type' => ConnectionMessage::TYPE_ALWAYS,
                'slides' => [[
                    'id' => 'new-version-changelog',
                    'title' => $this->translator->trans('platform_version', ['%version%' => $this->versionManager->getCurrentMinor()], 'platform'),
                    'content' => $content,
                    'order' => 0,
                ]],
            ],
        ];
    }

    private function getSupportMessages(): array
    {
        if (!$this->isAdmin() || $this->config->getParameter('help.support_email')) {
            return [];
        }

        $editUrl = $this->routingHelper->adminPath('parameters');

        return [
            [
                'id' => 'support-email-missing',
                'title' => $this->translator->trans('support_email_missing_title', [], 'platform'),
                'type' => ConnectionMessage::TYPE_ALWAYS,
                'slides' => [[
                    'id' => 'support-email-missing-message',
                    'title' => $this->translator->trans('support_email_missing_title', [], 'platform'),
                    'content' => $this->translator->trans('support_email_missing_content', ['%link%' => '<a href="'.$editUrl.'" target="_blank"><strong>'.$this->translator->trans('here', [], 'platform').'</strong></a>'], 'platform'),
                    'order' => 2,
                ]],
            ],
        ];
    }

    private function isAdmin(): bool
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            return in_array(PlatformRoles::ADMIN, $token->getRoleNames());
        }

        return false;
    }
}
