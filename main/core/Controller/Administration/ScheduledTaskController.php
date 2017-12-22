<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\CoreBundle\API\FinderProvider;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('tasks_scheduling')")
 */
class ScheduledTaskController extends Controller
{
    private $finder;
    private $configHandler;

    /**
     * @DI\InjectParams({
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "finder"        = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param PlatformConfigurationHandler $configHandler
     * @param FinderProvider               $finder
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        FinderProvider $finder
    ) {
        $this->configHandler = $configHandler;
        $this->finder = $finder;
    }

    /**
     * Displays scheduled tasks management.
     *
     * @EXT\Template
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'isCronConfigured' => $this->configHandler->hasParameter('is_cron_configured') && $this->configHandler->getParameter('is_cron_configured'),
            'tasks' => $this->finder->search(
                'Claroline\CoreBundle\Entity\Task\ScheduledTask', [
                    'limit' => 20,
                    'sortBy' => 'name',
                ]
            ),
        ];
    }
}
