<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/7/14
 * Time: 4:33 PM.
 */

namespace Icap\WebsiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('attr' => array('autofocus' => true)));
    }

    public function getName()
    {
        return 'icap_website_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_website',
            'data_class' => 'Icap\WebsiteBundle\Entity\Website',
            'csrf_protection' => true,
            'intention' => 'create_website',
        ));
    }
}
