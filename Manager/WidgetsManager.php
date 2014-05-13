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
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    public function getForm($type)
    {
        return $this->formFactory->create('icap_portfolio_widget_form_' . $type);
    }
    /**
     * @param Portfolio $portfolio
     * @param string    $type
     * @param array     $parameters
     *
     * @return array
     */
    public function handle(Portfolio $portfolio, $type, array $parameters)
    {
        $data = array();

        $form = $this->getForm($type);

        // Update database info

        $data['value'] = $parameters['value'];
        $data['views'] = array(
            'view' => $this->getView($portfolio, $type)
        );

        return $data;
    }
}
 