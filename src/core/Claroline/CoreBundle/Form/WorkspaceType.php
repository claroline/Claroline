<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Yaml\Yaml;

class WorkspaceType extends AbstractType
{
    private $templateDir;

    public function __construct($templateDir)
    {
        $this->templateDir = $templateDir;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('required' => true));
        $builder->add('code', 'text', array('required' => true));
        $builder->add(
            'type',
            'choice',
            array(
                'choices' => array(
                    'simple' => 'Simple',
                    'aggregator' => 'Aggregator',
                ),
                'multiple' => false,
                'required' => true
            )
        );

        $templates = array();
        foreach (new \DirectoryIterator($this->templateDir) as $fileInfo) {
            if ($fileInfo->isFile()) {
                $templates[$fileInfo->getRealPath()] = $fileInfo->getBasename();
            }
        }

        $builder->add(
            'template',
            'choice',
            array(
                'choices' => $templates,
                'multiple' => false,
                'required' => true,
                'mapped' => false
            )
        );
    }

    public function getName()
    {
        return 'workspace_form';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'translation_domain' => 'platform'
        );
    }
}