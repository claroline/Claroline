<?php

namespace HeVinci\CompetencyBundle\Form\Handler;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Utility class for handling forms defined as services.
 *
 * @DI\Service("hevinci.form.handler")
 */
class FormHandler
{
    private $container;
    private $currentForm;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->currentForm = null;
    }

    /**
     * Returns whether a form is valid and stores it internally for future use.
     *
     * @param string    $formReference  The service name of a form
     * @param Request   $request        The request to bind to the form
     * @param mixed     $data           An optional entity or array to bind the form to
     * @return bool
     * @throws \InvalidArgumentException if the service doesn't refer to a form
     */
    public function isValid($formReference, Request $request, $data = null)
    {
        $form = $this->getForm($formReference);

        if ($data) {
            $form->setData($data);
        }

        $form->handleRequest($request);

        return $form->isValid();
    }

    /**
     * Returns the data associated to the current form.
     *
     * @return mixed
     * @throws \LogicException if no form has been handled yet
     */
    public function getData()
    {
        return $this->getCurrentForm()->getData();
    }

    /**
     * Creates and returns a form view either from the current form
     * or from a new form service reference passed as argument.
     *
     * @param string $formReference The service name of a form
     * @return mixed
     * @throws \InvalidArgumentException    if a reference is passed but it
     *                                      doesn't refer to a form
     * @throws \LogicException              if no reference is passed and
     *                                      no form has been handled yet
     */
    public function getView($formReference = null)
    {
        if ($formReference) {
            $this->getForm($formReference);
        }

        return $this->getCurrentForm()->createView();
    }

    private function getForm($reference)
    {
        $form = $this->container->get($reference);

        if (!$form instanceof Form) {
            throw new \InvalidArgumentException(
                "The '{$reference}' service is not a form'"
            );
        }

        return $this->currentForm = $form;
    }

    private function getCurrentForm()
    {
        if (!$this->currentForm) {
            throw new \LogicException('No form has been handled yet');
        }

        return $this->currentForm;
    }
}
