<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Yaml\Yaml;

class WorkspaceType extends AbstractType
{
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
        $ds = DIRECTORY_SEPARATOR;
        foreach (new \DirectoryIterator(__DIR__."{$ds}..{$ds}Resources{$ds}config{$ds}workspace") as $fileInfo) {
            if ($fileInfo->isFile()) {
                $parsedFile = Yaml::parse($fileInfo->getRealPath());
                $templates[$fileInfo->getRealPath()] = $parsedFile['name'];
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