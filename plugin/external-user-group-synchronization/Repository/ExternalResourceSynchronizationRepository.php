<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 5/9/17
 */

namespace Claroline\ExternalSynchronizationBundle\Repository;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;

class ExternalResourceSynchronizationRepository
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    private $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->initializeConnection();
    }

    public function findTableNames()
    {
        return $this->conn->getSchemaManager()->listTableNames();
    }

    public function findViewNames()
    {
        $views = $this->conn->getSchemaManager()->listViews();

        return empty($views) ? [] : array_keys($views);
    }

    public function findColumnNames($table)
    {
        $cols = $this->conn->getSchemaManager()->listTableColumns($table);

        return empty($cols) ? [] : array_keys($cols);
    }

    public function findUsers($max = -1, $page = -1, $omitNulls = false)
    {
        $qb = $this->createUserQueryBuilder();

        if (is_null($qb)) {
            return [];
        }

        if ($omitNulls) {
            $this->omitNullUserValues($qb);
        }

        if ($max > 0) {
            $qb->setMaxResults($max)->setFirstResult($max * max(0, $page));
        }

        return $qb->execute()->fetchAll();
    }

    public function countUsers($omitNulls = false)
    {
        $qb = $this->conn->createQueryBuilder();
        $userConf = (isset($this->config['user_config'])) ? $this->config['user_config'] : [];
        $fields = (isset($userConf['fields'])) ? $userConf['fields'] : [];

        if (empty($userConf) || empty($fields)) {
            return null;
        }

        if ($omitNulls) {
            $this->omitNullUserValues($qb);
        }

        $qb
            ->select('COUNT('.$fields['id'].') AS nb')
            ->from($userConf['table']);

        return intval($qb->execute()->fetch()['nb']);
    }

    public function findGroups($search = null, $max = -1)
    {
        $qb = $this->createGroupQueryBuilder();

        if (is_null($qb)) {
            return [];
        }

        if (!empty($search)) {
            $replaceName = 'replace( replace( replace( replace( '.
                $this->config['group_config']['fields']['group_name'].
                ', \'"\', \'\'), \'.\', \'\'), \'-\', \'\'), \'\\\'\', \'\')';
            $replaceSearch = preg_replace('/[\'"\.-]/', '', $search);
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like($replaceName, ':search'),
                    $qb->expr()->like($this->config['group_config']['fields']['code'], ':search')
                ))
                ->setParameter('search', '%'.$replaceSearch.'%');
        }

        if ($max > 0) {
            $qb->setMaxResults($max);
        }

        return $qb->execute()->fetchAll();
    }

    public function findOneGroupById($id)
    {
        return $this->createGroupQueryBuilder()
            ->andWhere($this->config['group_config']['fields']['id'].' = :id')
            ->setParameter('id', $id)
            ->execute()
            ->fetch();
    }

    public function findUserIdsByGroupId($groupId)
    {
        $qb = $this->createUserGroupQueryBuilder();

        if (is_null($qb)) {
            return [];
        }

        $qb
            ->where($this->config['group_config']['user_group_config']['fields']['id_group'].' = :id')
            ->setParameter('id', $groupId);
        $res = $qb->execute()->fetchAll();

        return empty($res) ? [] : array_column($res, 'id_user');
    }

    private function initializeConnection()
    {
        $connectionParams = array_intersect_key($this->config, [
            'host' => null,
            'port' => null,
            'dbname' => null,
            'driver' => null,
            'user' => null,
            'password' => null,
        ]);
        $this->conn = DriverManager::getConnection($connectionParams);
    }

    private function createUserQueryBuilder()
    {
        $qb = $this->conn->createQueryBuilder();
        $userConf = (isset($this->config['user_config'])) ? $this->config['user_config'] : [];
        $fields = (isset($userConf['fields'])) ? $userConf['fields'] : [];

        if (empty($userConf) || empty($fields)) {
            return null;
        }

        $qb
            ->select(
                $fields['id'].' AS id',
                $fields['username'].' AS username',
                $fields['first_name'].' AS first_name',
                $fields['last_name'].' AS last_name',
                $fields['email'].' AS email',
                (empty($fields['code']) ? 'NULL' : $fields['code']).' AS code'
            )
            ->from($userConf['table']);

        return $qb;
    }

    private function createGroupQueryBuilder()
    {
        $qb = $this->conn->createQueryBuilder();
        $groupConf = (isset($this->config['group_config'])) ? $this->config['group_config'] : [];
        $fields = (isset($groupConf['fields'])) ? $groupConf['fields'] : [];

        if (empty($groupConf) || empty($fields)) {
            return null;
        }

        $qb
            ->select(
                $fields['id'].' AS id',
                $fields['group_name'].' AS name',
                (empty($fields['type']) ? 'NULL' : $fields['type']).' AS type',
                (empty($fields['code']) ? 'NULL' : $fields['code']).' AS code',
                (empty($fields['count']) ? 'NULL' : $fields['count']).' AS user_count'
            )
            ->from($groupConf['table']);

        return $qb;
    }

    private function createUserGroupQueryBuilder()
    {
        $qb = $this->conn->createQueryBuilder();
        $userGroupConf = (
            isset($this->config['group_config']) &&
            isset($this->config['group_config']['user_group_config'])
        ) ? $this->config['group_config']['user_group_config'] : [];
        $fields = (isset($userGroupConf['fields'])) ? $userGroupConf['fields'] : [];

        if (empty($userGroupConf) || empty($fields)) {
            return null;
        }

        $qb
            ->select(
                $fields['id_user'].' AS id_user',
                $fields['id_group'].' AS id_group'
            )
            ->from($userGroupConf['table']);

        return $qb;
    }

    private function omitNullUserValues(QueryBuilder $qb)
    {
        $userConf = (isset($this->config['user_config'])) ? $this->config['user_config'] : [];
        $fields = (isset($userConf['fields'])) ? $userConf['fields'] : [];
        if (empty($userConf) || empty($fields)) {
            return null;
        }

        $qb
            ->andWhere($qb->expr()->isNotNull($fields['username']))
            ->andWhere($qb->expr()->isNotNull($fields['email']))
            ->andWhere($qb->expr()->isNotNull($fields['first_name']))
            ->andWhere($qb->expr()->isNotNull($fields['last_name']));
    }
}
