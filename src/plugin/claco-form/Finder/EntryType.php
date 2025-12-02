<?php

namespace Claroline\ClacoFormBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\CommunityBundle\Finder\UserType;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        // the claco form of the entries
        // this is required to have access to the defined fields
        $resolver
            ->define('clacoForm')
            ->allowedTypes(ClacoForm::class)
            ->required();

        $resolver->setDefaults([
            'data_class' => Entry::class,
            'fulltext' => ['title'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        /** @var ClacoForm $clacoForm */
        $clacoForm = $options['clacoForm'];

        $builder
            ->add('title', TextType::class)
            ->add('locked', BooleanType::class)
            ->add('published', BooleanType::class)
            ->add('creationDate', DateType::class)
            ->add('editionDate', DateType::class)
            ->add('publicationDate', DateType::class)
            ->add('clacoForm', RelatedEntityType::class)
            ->add('categories', CategoryType::class)
            ->add('user', UserType::class)
        ;

        foreach ($clacoForm->getFields() as $field) {
            $builder->add('values.'.$field->getUuid(), FieldValueType::class, [
                'field' => $field->getFieldFacet(),
                'joinQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->leftJoin($alias.'.fieldValues', $finder->getAlias().'FieldValues');
                    $queryBuilder->leftJoin($finder->getAlias().'FieldValues.fieldFacetValue', $finder->getAlias());
                },
            ]);
        }
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
