<?php

namespace Icap\WikiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WikiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
    }

    public function getName()
    {
        return 'icap_wiki_type';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_wiki',
            'data_class' => 'Icap\WikiBundle\Entity\Wiki',
            'csrf_protection' => true,
            'intention' => 'create_wiki'
        ));
    }
}