<?php

namespace Claroline\ClacoFormBundle\Messenger\Message;

class AssignCategory
{
    /** @var string */
    private $categoryId;

    public function __construct(string $categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }
}
