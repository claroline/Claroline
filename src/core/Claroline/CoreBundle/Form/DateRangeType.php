<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Validator\Constraints\AdminWorkspaceTagUniqueName;
use Claroline\CoreBundle\Form\DataTransformer\DateRangeToTextTransformer;
use JMS\DiExtraBundle\Annotation as DI;


/**
 * @DI\Service("claroline.form.daterange")
 * @DI\FormType(alias = "daterange")
 */
class DateRangeType extends AbstractType
{
    protected $translator;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->appendClientTransformer(new DateRangeToTextTransformer($this->translator));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'daterange';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform'
            )
        );
    }
}