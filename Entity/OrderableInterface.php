<?php

namespace Claroline\CoreBundle\Entity;

interface OrderableInterface
{
    /**
     * Returns an array of orderable properties names.
     */
    public function getOrderableFields();
}
