<?php

namespace Claroline\ClacoFormBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Manager\CategoryManager;
use Claroline\ClacoFormBundle\Messenger\Message\AssignCategory;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Assign a Category to all the eligible ClacoForm entries.
 */
class AssignCategoryHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly CategoryManager $categoryManager
    ) {
    }

    public function __invoke(AssignCategory $assignCategory): void
    {
        // retrieve the category to check
        $category = $this->om->getRepository(Category::class)->find($assignCategory->getCategoryId());

        if (empty($category)) {
            return;
        }

        // get all the entries of the parent ClacoForm
        $entries = $this->om->getRepository(Entry::class)->findBy([
            'clacoForm' => $category->getClacoForm(),
        ]);

        if (empty($entries)) {
            return;
        }

        $this->om->startFlushSuite();

        foreach ($entries as $entry) {
            $this->categoryManager->manageCategory($category, $entry);
        }

        $this->om->endFlushSuite();
    }
}
