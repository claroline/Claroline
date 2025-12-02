<?php

namespace Claroline\ClacoFormBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\ChoiceType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\NumericType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldValueType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->define('field')
            ->allowedTypes(FieldFacet::class)
            ->required();

        $resolver->setDefaults([
            'data_class' => FieldFacetValue::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        /** @var FieldFacet $field */
        $field = $options['field'];
        $builder->add('fieldFacet', RelatedEntityType::class);

        $builder->addRequestTransformer(function (FinderRequest $finderRequest, FinderInterface $finder) use ($field) {
            if ($finderRequest->hasFilter($finder->getPropertyPath())) {
                $filterValue = $finderRequest->getFilter($finder->getPropertyPath());

                $finderRequest->addFilter($finder->getPropertyPath().'.value', $filterValue);
                $finderRequest->addFilter($finder->getPropertyPath().'.fieldFacet', $field->getUuid());

                $finderRequest->removeFilter($finder->getPropertyPath());
            }

            if ($finderRequest->hasSort($finder->getPropertyPath())) {
                $sortValue = $finderRequest->getSort($finder->getPropertyPath());

                $finderRequest->addSort($finder->getPropertyPath().'.value', $sortValue);
                $finderRequest->removeSort($finder->getPropertyPath());
            }

            return $finderRequest;
        });

        switch ($field->getType()) {
            case FieldFacet::BOOLEAN_TYPE:
                $builder->add('value', BooleanType::class);
                break;
            case FieldFacet::NUMBER_TYPE:
                $builder->add('value', NumericType::class);
                break;
            case FieldFacet::DATE_TYPE:
                $builder->add('value', DateType::class);
                break;
            case FieldFacet::CHOICE_TYPE:
                $builder->add('value', ChoiceType::class, ['choices' => array_map(function (FieldFacetChoice $choice) {
                    return $choice->getValue();
                }, $field->getRootFieldFacetChoices())]);
                break;
            default:
                $builder->add('value', TextType::class);
                break;
        }
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
