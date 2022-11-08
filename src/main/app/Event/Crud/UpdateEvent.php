<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Event\Crud;

use Claroline\AppBundle\API\Utils\ArrayUtils;

class UpdateEvent extends CrudEvent
{
    /** @var array */
    private $data;
    /** @var array */
    private $oldData;

    public function __construct($object, array $options, array $data = [], array $oldData = [])
    {
        parent::__construct($object, $options);

        $this->data = $data;
        $this->oldData = $oldData;
    }

    public function getData(?string $dataPath = null)
    {
        if (!empty($dataPath)) {
            return ArrayUtils::get($this->data, $dataPath);
        }

        return $this->data;
    }

    public function getOldData(?string $dataPath = null)
    {
        if (!empty($dataPath)) {
            return ArrayUtils::get($this->oldData, $dataPath);
        }

        return $this->oldData;
    }

    /**
     * Checks if a prop of the target entity has been changed by the update.
     */
    public function hasPropertyChanged(string $serializedPath, string $entityGetter): bool
    {
        $oldProp = ArrayUtils::get($this->oldData, $serializedPath);
        $newProp = $this->object->{$entityGetter}();

        if ($oldProp !== $newProp) {
            return true;
        }

        return false;
    }
}
