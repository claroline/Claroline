<?php

namespace HeVinci\CompetencyBundle\Form\DataTransformer;

use Claroline\AppBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Ability;
use Symfony\Component\Form\DataTransformerInterface;

class AbilityImportTransformer implements DataTransformerInterface
{
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Method without any effect implemented to satisfy DataTransformerInterface.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * Transforms the array of fields from AbilityImportForm into an
     * instance of Ability. If the ability cannot be fetched from the
     * database, this method returns null, assuming that validation
     * by ExistingAbilityValidator will fail later.
     *
     * @param array $fields
     *
     * @return null|Ability
     */
    public function reverseTransform($fields)
    {
        $ability = $this->om->getRepository('HeVinci\CompetencyBundle\Entity\Ability')
            ->findOneBy(['name' => $fields['ability']]);

        if ($ability && $fields['level']) {
            $ability->setLevel($fields['level']);
        }

        return $ability;
    }
}
