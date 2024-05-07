<?php

namespace Claroline\ClacoFormBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncHighMessageInterface;

class AssignCategory implements AsyncHighMessageInterface
{
    public function __construct(
        private readonly int $categoryId
    ) {
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }
}
