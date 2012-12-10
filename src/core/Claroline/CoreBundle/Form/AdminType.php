<?php
namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class AdminType extends BaseProfileType{
   
     public function buildForm(FormBuilderInterface $builder, array $options)
    {
           parent::buildForm($builder, $options);
           $builder ->add('mail', 'email', array('required' => false));
     }
     
      public function getName()
    {
        return 'admin_form';
    }
    
      public function getDefaultOptions(array $options) {
        return array(
            'data_class' => 'Claroline\CoreBundle\Entity\User',
        );
    }
}

?>
