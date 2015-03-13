<?php

namespace HeVinci\CompetencyBundle\Util;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Forms;

/**
 * Note: this test case uses the same logic than
 * Symfony\Component\Form\Test\FormIntegrationTestCase but adds an
 * access to the kernel. This is somewhat required to test doctrine
 * related stuff (which seems impossible to mock), like "entity" types.
 */
abstract class FormTestCase extends TransactionalTestCase
{
    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->getFormFactory();
    }

    protected function getExtensions()
    {
        return array();
    }

    protected function assertViewIsValid(Form $form, array $formData)
    {
        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
