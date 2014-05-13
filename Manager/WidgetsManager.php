<?php

namespace Icap\PortfolioBundle\Manager;

use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;

/**
 * @DI\Service("icap_portfolio.manager.widgets")
 */
class WidgetsManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    protected $templatingEngine;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "entityManager"     = @DI\Inject("doctrine.orm.entity_manager"),
     *     "templatingEngine"  = @DI\Inject("templating"),
     *     "formFactory"       = @DI\Inject("form.factory")
     * })
     */
    public function __construct(EntityManager $entityManager, EngineInterface $templatingEngine, FormFactory $formFactory)
    {
        $this->entityManager    = $entityManager;
        $this->templatingEngine = $templatingEngine;
        $this->formFactory      = $formFactory;
    }

    /**
     * @param Portfolio $portfolio
     * @param string    $type
     *
     * @return string
     */
    public function getView(Portfolio $portfolio, $type)
    {
        return $this->templatingEngine->render('IcapPortfolioBundle:templates:' . $type . '.html.twig', array('portfolio' => $portfolio));
    }

    public function getViewData(Portfolio $portfolio, $type)
    {
        $viewData = array();

        switch($type) {
            case 'title':
                break;
        }

        return $viewData;
    }

    /**
     * @param string $type
     * @param string $action
     *
     * @return string
     */
    public function getFormView($type, $action)
    {
        return $this->templatingEngine->render('IcapPortfolioBundle:templates/' . $action . ':' . $type . '.html.twig');
    }

    /**
     * @param string $type
     * @param object $data
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    public function getForm($type, $data)
    {
        return $this->formFactory->create('icap_portfolio_widget_form_' . $type, $data);
    }

    /**
     * @param Portfolio $portfolio
     * @param string    $type
     * @param array     $parameters
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function handle(Portfolio $portfolio, $type, array $parameters)
    {
        $data = array();

        $form = $this->getForm($type, $portfolio);
        $form->submit($parameters);

        if ($form->isValid()) {
            $object = $form->getData();

            $this->entityManager->persist($object);
            $this->entityManager->flush();

            $data['title'] = $parameters['title'];
            $data['views'] = array(
                'view' => $this->getView($portfolio, $type)
            );

            return $data;
        }

        throw new \InvalidArgumentException();
    }
}
 