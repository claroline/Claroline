<?php
/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 17/12/15
 * Time: 17:48.
 */

namespace Icap\BadgeBundle\Hydrators;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

class PairHydrator extends AbstractHydrator
{
    /**
     * Hydrates all rows from the current statement instance at once.
     *
     * @return array
     */
    protected function hydrateAllData()
    {
        $result = array();
        foreach ($this->_stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->hydrateRowData($row, $result);
        }

        return $result;
    }

    protected function hydrateRowData(array $row, array &$result)
    {
        if (count($row) == 0) {
            return false;
        }

        if (count($row) == 2) {
            $keys = array_keys($row);
            $value = [$row[$keys[0]], $row[$keys[1]]];
        } else {
            $value = $row;
        }

        $result[] = $value;
    }
}
