<?php

namespace Claroline\CoreBundle\Form\Handler;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Utility class for handling forms defined as services.
 *
 * @DI\Service("claroline.form_handler")
 */
class FormHandler
{
    private $factory;
    private $currentForm;

    /**
     * @DI\InjectParams({
     *     "factory" = @DI\Inject("form.factory")
     * })
     *
     * @param FormFactoryInterface $factory
     */
    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
        $this->currentForm = null;
    }

    /**
     * Returns whether a form is valid and stores it internally for future use.
     *
     * @param string  $formReference The form type name
     * @param Request $request       The request to be bound
     * @param mixed   $data          An entity or array to be bound
     * @param array   $options       The options to be passed to the form builder
     *
     * @return bool
     */
    public function isValid($formReference, Request $request, $data = null, array $options = [])
    {
        $form = $this->getForm($formReference, $data, $options);
        $form->handleRequest($request);

        return $form->isValid();
    }

    /**
     * Returns the data associated to the current form.
     *
     * @return mixed
     *
     * @throws \LogicException if no form has been handled yet
     */
    public function getData()
    {
        return $this->getCurrentForm()->getData();
    }

    /**
     * Creates and returns a form view either from the current form
     * or from a new form type reference passed as argument.
     *
     * @param string $formReference The form type name
     * @param mixed  $data          An entity or array to be bound
     * @param array  $options       The options to be passed to the form builder
     *
     * @return mixed
     *
     * @throws \LogicException if no reference is passed and
     *                         no form has been handled yet
     */
    public function getView($formReference = null, $data = null, array $options = [])
    {
        if ($formReference) {
            $this->getForm($formReference, $data, $options);
        }

        return $this->getCurrentForm()->createView();
    }

    private function getForm($reference, $data = null, array $options = [])
    {
        return $this->currentForm = $this->factory->create($reference, $data, $options);
    }

    private function getCurrentForm()
    {
        if (!$this->currentForm) {
            throw new \LogicException('No form has been handled yet');
        }

        return $this->currentForm;
    }
}
