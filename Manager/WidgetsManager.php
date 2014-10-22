<?php

namespace Icap\PortfolioBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use Icap\PortfolioBundle\Factory\WidgetFactory;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;

/**
 * @DI\Service("icap_portfolio.manager.widgets")
 */
class WidgetsManager
{
    /** @var EntityManager  */
    protected $entityManager;

    /** @var EngineInterface  */
    protected $templatingEngine;

    /** @var FormFactory  */
    protected $formFactory;

    /** @var array */
    protected $widgetsConfig = null;

    /** @var WidgetTypeManager  */
    protected $widgetTypeManager;

    /** @var WidgetFactory  */
    protected $widgetFactory;

    /**
     * @DI\InjectParams({
     *     "entityManager"     = @DI\Inject("doctrine.orm.entity_manager"),
     *     "templatingEngine"  = @DI\Inject("templating"),
     *     "formFactory"       = @DI\Inject("form.factory"),
     *     "widgetTypeManager" = @DI\Inject("icap_portfolio.manager.widget_type"),
     *     "widgetFactory"     = @DI\Inject("icap_portfolio.factory.widget")
     * })
     */
    public function __construct(EntityManager $entityManager, EngineInterface $templatingEngine, FormFactory $formFactory, WidgetTypeManager $widgetTypeManager, WidgetFactory $widgetFactory)
    {
        $this->entityManager     = $entityManager;
        $this->templatingEngine  = $templatingEngine;
        $this->formFactory       = $formFactory;
        $this->widgetTypeManager = $widgetTypeManager;
        $this->widgetFactory     = $widgetFactory;
    }

    /**
     * @return array
     */
    public function getWidgetsConfig()
    {
        return $this->widgetTypeManager->getWidgetsConfig();
    }

    /**
     * @param AbstractWidget $widget
     * @param string         $type
     *
     * @return string
     */
    public function getView(AbstractWidget $widget, $type)
    {
        return $this->templatingEngine->render('IcapPortfolioBundle:templates:' . $type . '.html.twig', array('widget' => $widget));
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
     * @param AbstractWidget $widget
     * @param string         $type
     * @param array          $parameters
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function handle(AbstractWidget $widget, $type, array $parameters)
    {
        $originalChildren = new ArrayCollection();

        foreach ($widget->getChildren() as $child) {
            $originalChildren->add($child);
        }

        $originalColumn = $widget->getColumn();
        $originalRow    = $widget->getRow();

        $data = array();

        $form = $this->getForm($type, $widget);
        $form->submit($parameters);

        if ($form->isValid()) {
            $newChildren = $widget->getChildren();
            $newColumn   = $widget->getColumn();
            $newRow      = $widget->getRow();

            foreach ($originalChildren as $child) {
                if (!$newChildren->contains($child)) {
                     $this->entityManager->remove($child);
                }
            }

            if ($originalColumn != $newColumn) {
                $existedWidgetParameters = array(
                    'portfolio' => $widget->getPortfolio(),
                    'column'    => $widget->getColumn(),
                    'row'       => $widget->getRow()
                );

                $maxRow = $this->entityManager->getRepository('IcapPortfolioBundle:Widget\AbstractWidget')->findMaxRow($widget->getPortfolio(), $widget->getColumn());
                $widget->setRow($maxRow['maxRow'] + 1);
            }
            else if ($originalRow != $newRow) {
                $replacedWidgetParameters = array(
                    'portfolio' => $widget->getPortfolio(),
                    'column'    => $widget->getColumn(),
                    'row'       => $widget->getRow()
                );
                /** @var AbstractWidget $replacedWidget */
                $replacedWidget = $this->entityManager->getRepository('IcapPortfolioBundle:Widget\AbstractWidget')->findOneBy($replacedWidgetParameters);
                $replacedWidget->setRow(--$newRow);

                $this->entityManager->persist($replacedWidget);
            }

            $this->entityManager->persist($widget);
            $this->entityManager->flush();

            $data = $this->getWidgetData($widget);

            return $data;
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param Portfolio $portfolio
     * @param string    $type
     *
     * @throws \InvalidArgumentException
     * @return AbstractWidget
     */
    public function getNewWidget(Portfolio $portfolio, $type)
    {
        $widget = $this->widgetFactory->createWidget($portfolio, $type);

        return $widget;
    }

    /**
     * @param AbstractWidget $widget
     */
    public function deleteWidget(AbstractWidget $widget)
    {
        $this->entityManager->remove($widget);
        $this->entityManager->flush();
    }

    /**
     * @param AbstractWidget $widget
     * @param bool           $withView
     *
     * @return array
     */
    public function getWidgetData(AbstractWidget $widget, $withView = true)
    {
        $widgetViews = array(
            'views'  => array(
                'view' => $withView ? $this->getView($widget, $widget->getWidgetType()) : array()
            )
        );

        return  $widget->getCommonData() + $widgetViews + $widget->getData();
    }
}
 