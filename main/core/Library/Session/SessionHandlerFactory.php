<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Session;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

/**
 * @DI\Service("claroline.session.handler_factory")
 */
class SessionHandlerFactory
{
    private $configHandler;
    private $sessionPath;
    private $dbUsername;
    private $dbPassword;
    private $dbName;

    /**
     * @DI\InjectParams({
     *     "configHandler"  = @DI\Inject("claroline.config.platform_config_handler"),
     *     "sessionPath"    = @DI\Inject("%session.save_path%"),
     *     "dbUsername"     = @DI\Inject("%database_user%"),
     *     "dbPassword"     = @DI\Inject("%database_password%"),
     *     "dbName"         = @DI\Inject("%database_name%")
     * })
     */
    public function __construct(
        PlatformConfigurationHandler $configHandler,
        $sessionPath,
        $dbUsername,
        $dbPassword,
        $dbName
    ) {
        $this->configHandler = $configHandler;
        $this->sessionPath = $sessionPath;
        $this->dbPassword = $dbPassword;
        $this->dbUsername = $dbUsername;
        $this->dbName = $dbName;
    }

    public function getHandler()
    {
        $type = $this->configHandler->getParameter('session_storage_type');

        if ($type === 'native') {
            return new NativeFileSessionHandler($this->sessionPath);
        }

        if ($type === 'native_php') {
            return new NativeSessionHandler();
        }

        if ($type === 'claro_pdo' || $type === 'pdo') {
            if ($type === 'pdo') {
                $dsn = $this->configHandler->getParameter('session_db_dsn');
                $username = $this->configHandler->getParameter('session_db_user');
                $password = $this->configHandler->getParameter('session_db_password');
                $dbOptions['db_table'] = $this->configHandler->getParameter('session_db_table');
                $dbOptions['db_id_col'] = $this->configHandler->getParameter('session_db_id_col');
                $dbOptions['db_data_col'] = $this->configHandler->getParameter('session_db_data_col');
                $dbOptions['db_time_col'] = $this->configHandler->getParameter('session_db_time_col');
            } else {
                $dsn = 'mysql:dbname='.$this->dbName;
                $username = $this->dbUsername;
                $password = $this->dbPassword;
                $dbOptions['db_table'] = 'claro_session';
                $dbOptions['db_id_col'] = 'session_id';
                $dbOptions['db_data_col'] = 'session_data';
                $dbOptions['db_time_col'] = 'session_time';
            }

            $pdo = new \PDO($dsn, $username, $password);
            $pdo->setAttribute(3, 2);

            return new PdoSessionHandler($pdo, $dbOptions);
        }
    }
}
