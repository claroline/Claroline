<?php

namespace Claroline\ClacoFormBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

class AssignCategory implements AsyncMessageInterface
{
    /** @var int */
    private $categoryId;

    public function __construct(int $categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }
}
