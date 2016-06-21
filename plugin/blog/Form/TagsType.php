<?php

namespace Icap\BlogBundle\Form;

use Icap\BlogBundle\Form\DataTransformer\TagsToTextTransformer;
use Icap\BlogBundle\Manager\TagManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class TagsType extends AbstractType
{
    private $tagManager;

    public function __construct(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new TagsToTextTransformer($this->tagManager));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $class = 'tags';

        if (isset($view->vars['attr']['class'])) {
            $class = $view->vars['attr']['class'].' '.$class;
        }

        $view->vars['attr']['class'] = $class;
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'tags';
    }
}
