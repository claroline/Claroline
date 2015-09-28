<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioWidget;
use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use Icap\PortfolioBundle\Event\WidgetFormViewEvent;
use Icap\PortfolioBundle\Factory\WidgetFactory;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

    /** @var EventDispatcherInterface  */
    protected $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "templatingEngine" = @DI\Inject("templating"),
     *     "formFactory" = @DI\Inject("form.factory"),
     *     "widgetTypeManager" = @DI\Inject("icap_portfolio.manager.widget_type"),
     *     "widgetFactory" = @DI\Inject("icap_portfolio.factory.widget"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(EntityManager $entityManager, EngineInterface $templatingEngine, FormFactory $formFactory,
        WidgetTypeManager $widgetTypeManager, WidgetFactory $widgetFactory, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->templatingEngine = $templatingEngine;
        $this->formFactory = $formFactory;
        $this->widgetTypeManager = $widgetTypeManager;
        $this->widgetFactory = $widgetFactory;
        $this->eventDispatcher = $eventDispatcher;
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
     *
     * @return string
     */
    public function getFormView($type)
    {
        $widgetFormEvent = new WidgetFormViewEvent();
        $widgetFormEvent->setWidgetType($type);

        $this->eventDispatcher->dispatch('icap_portfolio_widget_form_view_' . $type, $widgetFormEvent);

        return $widgetFormEvent->getFormView();
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
     * @param PortfolioWidget $portfolioWidget
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    public function getPortfolioWidgetForm(PortfolioWidget $portfolioWidget)
    {
        return $this->formFactory->create('icap_portfolio_portfolio_widget_form', $portfolioWidget);
    }

    /**
     * @param AbstractWidget $widget
     * @param string         $type
     * @param array          $parameters
     * @param string         $env
     *
     * @return array
     */
    public function handle(AbstractWidget $widget, $type, array $parameters, $env = 'prod')
    {
        $originalChildren = new ArrayCollection();

        foreach ($widget->getChildren() as $child) {
            $originalChildren->add($child);
        }

        $data = array();

        $form = $this->getForm($type, $widget);
        $form->submit($parameters);

        if ($form->isValid()) {
            $newChildren = $widget->getChildren();

            foreach ($originalChildren as $child) {
                if (!$newChildren->contains($child)) {
                     $this->entityManager->remove($child);
                }
            }

            $this->entityManager->persist($widget);
            $this->entityManager->flush();

            $data = $this->getWidgetData($widget);

            return $data;
        }

        if ('dev' === $env) {
            echo "<pre>";
            foreach ($form->getErrors(true, false) as $formError) {
                var_dump($formError->getMessage());
                var_dump($formError->getMessageParameters());
            }
            echo "</pre>" . PHP_EOL;
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param PortfolioWidget $porfolioWidget
     * @param array           $parameters
     * @param string          $env
     *
     * @return array
     */
    public function handlePortfolioWidget(PortfolioWidget $porfolioWidget, array $parameters, $env = 'prod')
    {
        $data = array();

        $form = $this->getPortfolioWidgetForm($porfolioWidget);
        $form->submit($parameters);

        if ($form->isValid()) {
            $this->entityManager->persist($porfolioWidget);
            $this->entityManager->flush();

            $data = $this->getPortfolioWidgetData($porfolioWidget);

            return $data;
        }

        if ('dev' === $env) {
            echo "<pre>";
            foreach ($form->getErrors(true, false) as $formError) {
                var_dump($formError->getMessage());
                var_dump($formError->getMessageParameters());
            }
            echo "</pre>" . PHP_EOL;
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param string $type
     * @param User   $user
     *
     * @return AbstractWidget
     */
    public function getNewDataWidget($type, User $user)
    {
        $widget = $this->widgetFactory->createDataWidget($type);
        $widget
            ->setId(uniqid())
            ->setUser($user);

        return $widget;
    }

    /**
     * @param Portfolio $portfolio
     * @param string    $type
     *
     * @return PortfolioWidget
     */
    public function getNewPortfolioWidget(Portfolio $portfolio, $type)
    {
        return $this->widgetFactory->createPortfolioWidget($portfolio, $type);
    }

    /**
     * @param PortfolioWidget $portfolioWidget
     */
    public function deletePortfolioWidget(PortfolioWidget $portfolioWidget)
    {
        $this->entityManager->remove($portfolioWidget);
        $this->entityManager->flush();
    }

    /**
     * @param AbstractWidget $widget
     */
    public function deleteDataWidget(AbstractWidget $widget)
    {
        $this->entityManager->remove($widget);
        $this->entityManager->flush();
    }

    /**
     * @param PortfolioWidget $portfolioWidget
     * @param bool           $withView
     *
     * @return array
     */
    public function getPortfolioWidgetData(PortfolioWidget $portfolioWidget, $withView = true)
    {
        $widget = $portfolioWidget->getWidget();

        $widgetViews = array(
            'views' => $withView ? array('view' => $this->getView($widget, $widget->getWidgetType())) : array()
        );

        $widgetData = [
            'widget' => $widget->getCommonData() + $widgetViews + ($withView ? $widget->getData() : $widget->getEmpty())
        ];

        return  $portfolioWidget->getData() + $widgetData;
    }

    /**
     * @param AbstractWidget $widget
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[]
     */
    public function getWidgetData(AbstractWidget $widget)
    {
        $widgetViews = array(
            'views' => array('view' => $this->getView($widget, $widget->getWidgetType()))
        );

        return $widget->getCommonData() + $widgetViews + $widget->getData();
    }

    /**
     * @param Portfolio $portfolio
     * @param bool      $inArray
     *
     * @return \Icap\PortfolioBundle\Entity\PortfolioWidget[]|array
     */
    public function getByPortfolioForGridster(Portfolio $portfolio, $inArray = false)
    {
        $portfolioWidgets = $this->entityManager->getRepository("IcapPortfolioBundle:PortfolioWidget")->findOrderedByRowAndCol($portfolio);

        if ($inArray) {
            $portfolioWidgetsInArray = [];
            foreach ($portfolioWidgets as $portfolioWidget) {
                $portfolioWidgetsInArray[] = $this->getPortfolioWidgetData($portfolioWidget);
            }

            $portfolioWidgets = $portfolioWidgetsInArray;
        }

        return $portfolioWidgets;
    }

    /**
     * @param User $user
     * @param string|null $type
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[]
     */
    public function getWidgets(User $user, $type = null)
    {
        /** @var \Icap\PortfolioBundle\Repository\Widget\AbstractWidgetRepository $abstractWidgetRepository */
        $abstractWidgetRepository = $this->entityManager->getRepository("IcapPortfolioBundle:Widget\AbstractWidget");

        if ($type !== null) {
            $widgets = $abstractWidgetRepository->findByWidgetTypeAndUser($type, $user);
        }
        else {
            $widgets = $abstractWidgetRepository->findByUser($user);
        }

        return $widgets;
    }

    /**
     * @param Portfolio $portfolio
     * @param User      $user
     * @param string    $type
     *
     * @return \Icap\PortfolioBundle\Entity\PortfolioWidget[]
     */
    public function getPortfolioWidgetsForWidgetPicker(Portfolio $portfolio, User $user, $type)
    {
        $portfolioWidgets = [];

        $widgets = $this->getWidgets($user, $type);

        foreach ($widgets as $widget) {
            $portfolioWidget = new PortfolioWidget();
            $portfolioWidget
                ->setPortfolio($portfolio)
                ->setWidget($widget)
                ->setWidgetType($type)
                ->setSize($this->getWidgetSizeByType($type))
            ;
            $portfolioWidgets[] = $portfolioWidget;
        }

        return $portfolioWidgets;
    }

    public function getWidgetSizeByType($type)
    {
        $classNamespace = '\Icap\PortfolioBundle\Entity\Widget\\' . ucfirst($type) . 'Widget';
        $position = [
            'sizeX' => $classNamespace::SIZE_X,
            'sizeY' => $classNamespace::SIZE_Y
        ];

        return $position;
    }
}
 