<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.session_manager")
 */
class SessionManager
{
    public function validate($parameters)
    {
        $errors = array();

        if ($parameters['session_storage_type'] === 'pdo') {
            $dsn = $parameters['session_db_dsn'];
            $username = $parameters['session_db_user'];
            $password = $parameters['session_db_password'];

            try {
                $pdo = new \PDO($dsn, $username, $password);
                $pdo->setAttribute(3, 2);
            } catch (\PDOException $e) {
                $errors[] = 'database_connection_failed';
            }
        }

        return $errors;
    }
}