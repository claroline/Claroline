<?php

namespace HeVinci\CompetencyBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HeVinci\CompetencyBundle\Entity\Level;
use Symfony\Component\Form\DataTransformerInterface;

class LevelTransformer implements DataTransformerInterface
{
    /**
     * Transforms an ArrayCollection of Level entities to a
     * multi-line string showing the level names.
     *
     * @param Collection $levels
     * @return string
     */
    public function transform($levels)
    {
        $serialized = '';

        if (!$levels instanceof Collection || $levels->count() === 0) {

            return $serialized;
        }

        foreach ($levels as $level) {
            $serialized .= $level->getName() . "\n";
        }

        return $serialized;
    }

    /**
     * Transforms a multi-line string of level names to an
     * ArrayCollection of Level entities.
     *
     * @param string $levels
     * @return ArrayCollection
     */
    public function reverseTransform($levels)
    {
        $collection = new ArrayCollection();
        $names = explode("\n", $levels);
        $value = 0;

        foreach ($names as $name) {
            if ($trimmedName = trim($name)) {
                $level = new Level();
                $level->setName($trimmedName);
                $level->setValue($value);
                $collection->add($level);
                $value++;
            }
        }

        return $collection;
    }
}