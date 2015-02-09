<?php

namespace HeVinci\CompetencyBundle\Form\Handler;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
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

    public function handle($formReference, Request $request, $data = null)
    {
        $form = $this->getForm($formReference);

        if ($data) {
            $form->setData($data);
        }

        $form->handleRequest($request);

        return $form->isValid();
    }

    public function getData()
    {
        return $this->getCurrentForm()->getData();
    }

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
            throw new \Exception(
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
