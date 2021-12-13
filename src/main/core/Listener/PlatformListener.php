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
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\VersionManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlatformListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    private $translator;

    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var VersionManager */
    private $versionManager;

    /** @var TempFileManager */
    private $tempManager;

    /** @var LocaleManager */
    private $localeManager;

    /**
     * The list of public routes of the application.
     * NB. This is not the best place to declare it.
     *
     * @var array
     */
    const PUBLIC_ROUTES = [
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
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        PlatformConfigurationHandler $config,
        VersionManager $versionManager,
        TempFileManager $tempManager,
        LocaleManager $localeManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->config = $config;
        $this->versionManager = $versionManager;
        $this->tempManager = $tempManager;
        $this->localeManager = $localeManager;
    }

    /**
     * Sets the platform language.
     */
    public function setLocale(RequestEvent $event)
    {
        if ($event->isMasterRequest()) {
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
        if (!$event->isMasterRequest() || in_array($event->getRequest()->get('_route'), static::PUBLIC_ROUTES)) {
            return;
        }

        // checks platform restrictions
        if ($this->config->getParameter('restrictions.disabled')) {
            throw new HttpException(503, 'Platform is not available (Platform is disabled).');
        }

        $dates = $this->config->getParameter('restrictions.dates');
        if (!empty($dates)) {
            $now = new \DateTime();
            if (!empty($dates[0]) && DateNormalizer::normalize($now) < $dates[0]) {
                throw new HttpException(503, 'Platform is not available (Platform start date not reached).');
            }

            if (!empty($dates[1]) && DateNormalizer::normalize($now) > $dates[1]) {
                throw new HttpException(503, 'Platform is not available (Platform end date reached).');
            }
        }

        // checks platform maintenance
        if (MaintenanceHandler::isMaintenanceEnabled() || $this->config->getParameter('maintenance.enable')) {
            // only disable for non admin
            // TODO : it may break the impersonation mode
            if (!$this->isAdmin()) {
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
    public function displayVersionChangeLogs(GenericDataEvent $event)
    {
        if (!$this->config->getParameter('changelogMessage.enabled')) {
            // connection message is disabled, nothing to do
            return;
        }

        $roles = $this->config->getParameter('changelogMessage.roles');
        if (empty(array_intersect($this->tokenStorage->getToken()->getRoleNames(), $roles))) {
            // current user cannot see the changelog with its current roles
            return;
        }

        // check if we still are in the display period
        $installationDate = $this->versionManager->getInstallationDate($this->versionManager->getCurrentMinor());
        if (empty($installationDate)) {
            return;
        }

        $period = new \DateInterval($this->config->getParameter('changelogMessage.duration'));
        $endDate = $installationDate->add($period);
        $now = new \DateTime();
        if ($now > $endDate) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        $locale = $this->localeManager->getLocale($user);
        $content = $this->versionManager->getChangelogs($locale).'<br/>'.'<br/>';
        $content .= '<em>'.$this->translator->trans('platform_changelog_display', ['%roles%' => implode(', ', $this->config->getParameter('changelogMessage.roles'))], 'platform').'</em>';

        $event->setResponse([
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
        ]);
    }

    private function isAdmin()
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            return in_array(PlatformRoles::ADMIN, $token->getRoleNames());
        }

        return false;
    }
}
