<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CoursesWidgetConfig;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use Claroline\CursusBundle\Form\CoursesWidgetConfigurationType;
use Claroline\CursusBundle\Form\CourseType;
use Claroline\CursusBundle\Form\CursusType;
use Claroline\CursusBundle\Form\FileSelectType;
use Claroline\CursusBundle\Form\PluginConfigurationType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CursusController extends Controller
{
    private $authorization;
    private $cursusManager;
    private $formFactory;
    private $platformConfigHandler;
    private $request;
    private $router;
    private $toolManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "authorization"         = @DI\Inject("security.authorization_checker"),
     *     "cursusManager"         = @DI\Inject("claroline.manager.cursus_manager"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "router"                = @DI\Inject("router"),
     *     "toolManager"           = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"            = @DI\Inject("translator")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        CursusManager $cursusManager,
        FormFactory $formFactory,
        PlatformConfigurationHandler $platformConfigHandler,
        RequestStack $requestStack,
        RouterInterface $router,
        ToolManager $toolManager,
        TranslatorInterface $translator
    )
    {
        $this->authorization = $authorization;
        $this->cursusManager = $cursusManager;
        $this->formFactory = $formFactory;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
    }


    /******************
     * Cursus methods *
     ******************/

    /**
     * @EXT\Route(
     *     "/cursus/management/tool/menu",
     *     name="claro_cursus_management_tool_menu"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function cursusManagementToolMenuAction()
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords
        );
    }

    /**
     * @EXT\Route(
     *     "/tool/index",
     *     name="claro_cursus_tool_index"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function cursusToolIndexAction()
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }
        $allRootCursus = $this->cursusManager->getAllRootCursus();

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
            'allRootCursus' => $allRootCursus
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/create/form",
     *     name="claro_cursus_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusCreateModalForm.html.twig")
     */
    public function cursusCreateFormAction()
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(new CursusType());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "cursus/create",
     *     name="claro_cursus_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusCreateModalForm.html.twig")
     */
    public function cursusCreateAction()
    {
        $this->checkToolAccess();
        $cursus = new Cursus();
        $form = $this->formFactory->create(new CursusType(), $cursus);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $orderMax = $this->cursusManager->getLastRootCursusOrder();

            if (is_null($orderMax)) {
                $cursus->setCursusOrder(1);
            } else {
                $cursus->setCursusOrder(intval($orderMax) + 1);
            }
            $color = $form->get('color')->getData();
            $details = array('color' => $color);
            $cursus->setDetails($details);
            $this->cursusManager->persistCursus($cursus);

            $message = $this->translator->trans(
                'cursus_creation_confirm_msg' ,
                array(),
                'cursus'
            );
            $session = $this->request->getSession();
            $session->getFlashBag()->add('success', $message);

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/edit/form",
     *     name="claro_cursus_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusEditModalForm.html.twig")
     *
     * @param Cursus $cursus
     */
    public function cursusEditFormAction(Cursus $cursus)
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(
            new CursusType($cursus),
            $cursus
        );

        return array(
            'form' => $form->createView(),
            'cursus' => $cursus
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/edit",
     *     name="claro_cursus_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusEditModalForm.html.twig")
     *
     * @param Cursus $cursus
     */
    public function cursusEditAction(Cursus $cursus)
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(
            new CursusType($cursus),
            $cursus
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $color = $form->get('color')->getData();
            $details = $cursus->getDetails();

            if (is_null($details)) {
                $details = array('color' => $color);
            } else {
                $details['color'] = $color;
            }
            $cursus->setDetails($details);
            $this->cursusManager->persistCursus($cursus);

            $message = $this->translator->trans(
                'cursus_edition_confirm_msg' ,
                array(),
                'cursus'
            );
            $session = $this->request->getSession();
            $session->getFlashBag()->add('success', $message);

            return new JsonResponse('success', 200);
        } else {

            return array(
                'form' => $form->createView(),
                'cursus' => $cursus
            );
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/view",
     *     name="claro_cursus_view",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusViewModal.html.twig")
     *
     * @param Cursus $cursus
     */
    public function cursusViewAction(Cursus $cursus)
    {
        $this->checkToolAccess();

        return array('cursus' => $cursus);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/view/hierarchy",
     *     name="claro_cursus_view_hierarchy",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusViewHierarchyModal.html.twig")
     *
     * @param Cursus $cursus
     */
    public function cursusViewHierarchyAction(Cursus $cursus)
    {
        $this->checkToolAccess();
        $hierarchy = array();
        $allCursus = $this->cursusManager->getHierarchyByCursus($cursus);

        foreach ($allCursus as $oneCursus) {
            $parent = $oneCursus->getParent();

            if (!is_null($parent)) {
                $parentId = $parent->getId();

                if (!isset($hierarchy[$parentId])) {
                    $hierarchy[$parentId] = array();
                }
                $hierarchy[$parentId][] = $oneCursus;
            }
        }

        return array('cursus' => $cursus, 'hierarchy' => $hierarchy);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/delete",
     *     name="claro_cursus_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param Cursus $cursus
     */
    public function cursusDeleteAction(Cursus $cursus)
    {
        $this->checkToolAccess();
        $this->cursusManager->deleteCursus($cursus);

        $message = $this->translator->trans(
            'cursus_deletion_confirm_msg' ,
            array(),
            'cursus'
        );
        $session = $this->request->getSession();
        $session->getFlashBag()->add('success', $message);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/cursus/{cursus}/description/display",
     *     name="claro_cursus_display_description",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusDescriptionDisplayModal.html.twig")
     *
     * @param Cursus $cursus
     */
    public function cursusDescriptionDisplayAction(Cursus $cursus)
    {
        $this->checkToolAccess();

        return array('description' => $cursus->getDescription());
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/management",
     *     name="claro_cursus_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Cursus $cursus
     *
     */
    public function cursusManagementAction(Cursus $cursus)
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }
        $hierarchy = array();
        $allCursus = $this->cursusManager->getHierarchyByCursus($cursus);

        foreach ($allCursus as $oneCursus) {
            $parent = $oneCursus->getParent();

            if (!is_null($parent)) {
                $parentId = $parent->getId();

                if (!isset($hierarchy[$parentId])) {
                    $hierarchy[$parentId] = array();
                }
                $hierarchy[$parentId][] = $oneCursus;
            }
        }

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
            'cursus' => $cursus,
            'hierarchy' => $hierarchy
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/{parent}/child/create/form",
     *     name="claro_cursus_child_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusChildCreateModalForm.html.twig")
     */
    public function cursusChildCreateFormAction(Cursus $parent)
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(new CursusType());

        return array(
            'form' => $form->createView(),
            'parent' => $parent
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/{parent}/child/create",
     *     name="claro_cursus_child_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusChildCreateModalForm.html.twig")
     */
    public function cursusChildCreateAction(Cursus $parent)
    {
        $this->checkToolAccess();
        $cursus = new Cursus();
        $form = $this->formFactory->create(new CursusType(), $cursus);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $cursus->setParent($parent);
            $orderMax = $this->cursusManager->getLastCursusOrderByParent($parent);

            if (is_null($orderMax)) {
                $cursus->setCursusOrder(1);
            } else {
                $cursus->setCursusOrder(intval($orderMax) + 1);
            }
            $color = $form->get('color')->getData();
            $details = array('color' => $color);
            $cursus->setDetails($details);
            $this->cursusManager->persistCursus($cursus);

            return new JsonResponse(
                array(
                    'parent_id' => $parent->getId(),
                    'id' => $cursus->getId(),
                    'title' => $cursus->getTitle()
                ),
                200
            );
        } else {

            return array(
                'form' => $form->createView(),
                'parent' => $parent
            );
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/add/courses/list/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_cursus_add_courses_users_list",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="title","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays the list of courses.
     *
     * @param Cursus $cursus
     * @param string  $search
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     * @param string  $order
     */
    public function cursusAddCoursesListAction(
        Cursus $cursus,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'title',
        $order = 'ASC'
    )
    {
        $this->checkToolAccess();

        $courses = $this->cursusManager->getUnmappedCoursesByCursus(
            $cursus,
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );

        return array(
            'cursus' => $cursus,
            'courses' => $courses,
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/associate/course/{course}",
     *     name="claro_cursus_associate_course",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function cursusCourseAssociateAction(Cursus $cursus, Course $course)
    {
        $this->checkToolAccess();
        $cursus->setCourse($course);
        $this->cursusManager->persistCursus($cursus);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/dissociate/course",
     *     name="claro_cursus_dissociate_course",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function cursusCourseDissociateAction(Cursus $cursus)
    {
        $this->checkToolAccess();
        $cursus->setCourse(null);
        $this->cursusManager->persistCursus($cursus);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/add/course/{course}",
     *     name="claro_cursus_add_course",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param Cursus $cursus
     * @param Course $course
     */
    public function cursusCourseAddAction(Cursus $cursus, Course $course)
    {
        $this->checkToolAccess();
        $createdCursus = $this->cursusManager->addCoursesToCursus($cursus, array($course));
        $results = array();

        foreach ($createdCursus as $created) {
            $results[] = array('id' => $created->getId(), 'title' => $created->getTitle());
        }

        return new JsonResponse($results, 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/add/courses",
     *     name="claro_cursus_add_courses",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "courses",
     *      class="ClarolineCursusBundle:Course",
     *      options={"multipleIds" = true, "name" = "courseIds"}
     * )
     *
     * @param Cursus $cursus
     * @param Course[] $courses
     */
    public function cursusCoursesAddAction(Cursus $cursus, array $courses)
    {
        $this->checkToolAccess();
        $this->cursusManager->addCoursesToCursus($cursus, $courses);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/remove/course/{course}",
     *     name="claro_cursus_remove_course",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param Cursus $cursus
     * @param Course $course
     */
    public function cursusCourseRemoveAction(Cursus $cursus, Course $course)
    {
        $this->checkToolAccess();
        $this->cursusManager->removeCoursesFromCursus($cursus, array($course));

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/remove/courses",
     *     name="claro_cursus_remove_courses",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "courses",
     *      class="ClarolineCursusBundle:Course",
     *      options={"multipleIds" = true, "name" = "courseIds"}
     * )
     *
     * @param Cursus $cursus
     * @param Course[] $courses
     */
    public function cursusCoursesRemoveAction(Cursus $cursus, array $courses)
    {
        $this->checkToolAccess();
        $this->cursusManager->removeCoursesFromCursus($cursus, $courses);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/course/create/form",
     *     name="claro_cursus_course_into_cursus_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusCourseCreateModalForm.html.twig")
     */
    public function cursusCourseCreateFormAction(User $authenticatedUser, Cursus $cursus)
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(
            new CourseType($authenticatedUser, $this->cursusManager, $this->translator),
            new Course()
        );

        return array(
            'form' => $form->createView(),
            'cursus' => $cursus
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/course/create",
     *     name="claro_cursus_course_into_cursus_create",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusCourseCreateModalForm.html.twig")
     */
    public function cursusCourseCreateAction(User $authenticatedUser, Cursus $cursus)
    {
        $this->checkToolAccess();
        $course = new Course();
        $form = $this->formFactory->create(
            new CourseType($authenticatedUser, $this->cursusManager, $this->translator),
            $course
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $icon = $form->get('icon')->getData();

            if (!is_null($icon)) {
                $hashName = $this->cursusManager->saveIcon($icon);
                $course->setIcon($hashName);
            }
            $this->cursusManager->persistCourse($course);
            $createdCursus = $this->cursusManager->addCoursesToCursus($cursus, array($course));
            $results = array();

            foreach ($createdCursus as $created) {
                $results[] = array(
                    'id' => $created->getId(),
                    'title' => $created->getTitle(),
                    'course_id' => $course->getId(),
                    'code' => $course->getCode(),
                    'root' => $cursus->getRoot()
                );
            }

            return new JsonResponse($results, 200);
        } else {

            return array(
                'form' => $form->createView(),
                'cursus' => $cursus
            );
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/order/update/with/cursus/{otherCursus}/mode/{mode}",
     *     name="claro_cursus_update_order",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function updateCursusOrderAction(
        Cursus $cursus,
        Cursus $otherCursus,
        $mode
    )
    {
        $this->checkToolAccess();

        if ($cursus->getParent() === $otherCursus->getParent()) {
            $newOrder = $otherCursus->getCursusOrder();

            if ($mode === 'next') {
                $this->cursusManager->updateCursusOrder($cursus, $newOrder);
            } else {
                $cursus->setCursusOrder($newOrder + 1);
                $this->cursusManager->persistCursus($cursus);
            }

            return new JsonResponse('success', 204);
        } else {

            return new JsonResponse('Forbidden', 403);
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/update/parent/{parent}/order/with/cursus/{nextCursusId}",
     *     name="claro_cursus_update_parent_and_order",
     *     defaults={"nextCursusId"=-1},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function updateCursusParentAndOrderAction(
        Cursus $cursus,
        Cursus $parent,
        $nextCursusId = -1
    )
    {
        $this->checkToolAccess();

        if ($nextCursusId === -1) {
            $order = -1;
        } else {
            $nextCursus = $this->cursusManager->getOneCursusById($nextCursusId);
            $order = is_null($nextCursus) ? -1 : $nextCursus->getCursusOrder();
        }
        $this->cursusManager->updateCursusParentAndOrder($cursus, $parent, $order);

        return new JsonResponse('success', 204);
    }


    /********************************
     * Plugin configuration methods *
     ********************************/


    /**
     * @EXT\Route(
     *     "/plugin/configure/form",
     *     name="claro_cursus_plugin_configure_form"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function pluginConfigureFormAction()
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }

        $form = $this->formFactory->create(
            new PluginConfigurationType($this->platformConfigHandler),
            $this->cursusManager->getConfirmationEmail()
        );

        return array(
            'form' => $form->createView(),
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords
        );
    }

    /**
     * @EXT\Route(
     *     "/plugin/configure",
     *     name="claro_cursus_plugin_configure"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:pluginConfigureForm.html.twig")
     */
    public function pluginConfigureAction()
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }

        $formData = $this->request->get('cursus_plugin_configuration_form');
        $this->cursusManager->persistConfirmationEmail($formData['content']);
        $this->platformConfigHandler->setParameters(
            array(
                'cursusbundle_default_session_start_date' => $formData['startDate'],
                'cursusbundle_default_session_end_date' => $formData['endDate'],
            )
        );
        $form = $this->formFactory->create(
            new PluginConfigurationType($this->platformConfigHandler),
            $this->cursusManager->getConfirmationEmail()
        );

        return array(
            'form' => $form->createView(),
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/displayed/word/{key}/change/{value}",
     *     name="claro_cursus_change_displayed_word",
     *     defaults={"value"=""},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function displayedWordChangeAction($key, $value = '')
    {
        $this->authorization->isGranted('ROLE_ADMIN');
        $displayedWord = $this->cursusManager->getOneDisplayedWordByWord($key);

        if (is_null($displayedWord)) {
            $displayedWord = new CursusDisplayedWord();
            $displayedWord->setWord($key);
        }
        $displayedWord->setDisplayedWord($value);
        $this->cursusManager->persistCursusDisplayedWord($displayedWord);

        $sessionFlashBag = $this->get('session')->getFlashBag();
        $msg = $this->translator->trans('the_displayed_word_for', array(), 'cursus') .
            ' [' .
            $key .
            '] ' .
            $this->translator->trans('will_be', array(), 'cursus') .
            ' ['
            . $value .
            ']';
        $sessionFlashBag->add('success', $msg);

        return new Response('success', 200);
    }


    /******************
     * Widget methods *
     ******************/

    /**
     * @EXT\Route(
     *     "/courses/registration/widget/{widgetInstance}",
     *     name="claro_cursus_courses_registration_widget",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Widget:coursesRegistrationWidget.html.twig")
     */
    public function coursesRegistrationWidgetAction(WidgetInstance $widgetInstance)
    {
        return array('widgetInstance' => $widgetInstance);
    }

    /**
     * @EXT\Route(
     *     "/courses/list/registration/widget/{widgetInstance}/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_cursus_courses_list_for_registration_widget",
     *     defaults={"page"=1, "search"="", "max"=20, "orderedBy"="title","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Widget:coursesListForRegistrationWidget.html.twig")
     */
    public function coursesListForRegistrationWidgetAction(
        User $authenticatedUser,
        WidgetInstance $widgetInstance,
        $search = '',
        $page = 1,
        $max = 20,
        $orderedBy = 'title',
        $order = 'ASC'
    )
    {
        $config = $this->cursusManager->getCoursesWidgetConfiguration($widgetInstance);
        $configCursus = $config->getCursus();

        if (is_null($configCursus)) {
            $courses = $this->cursusManager->getAllCourses(
                $search,
                $orderedBy,
                $order,
                true,
                $page,
                $max
            );
        } else {
            $courses = $this->cursusManager->getDescendantCoursesByCursus(
                $configCursus,
                $search,
                $orderedBy,
                $order,
                true,
                $page,
                $max
            );
        }
        $coursesArray = array();

        foreach ($courses as $course) {
            $coursesArray[] = $course;
        }
        $sessions = array();
        $courseSessions = $this->cursusManager->getSessionsByCourses(
            $coursesArray,
            'creationDate',
            'ASC'
        );

        foreach ($courseSessions as $courseSession) {
            $courseId = $courseSession->getCourse()->getId();
            $status = $courseSession->getSessionStatus();

            if ($status === 0 || $status === 1) {

                if (!isset($sessions[$courseId])) {
                    $sessions[$courseId] = array();
                }
                $sessions[$courseId][] = $courseSession;
            }
        }
        $registeredSessions = array();
        $pendingSessions = array();
        $userSessions = $this->cursusManager->getSessionUsersBySessionsAndUsers(
            $courseSessions,
            array($authenticatedUser),
            0
        );
        $pendingRegistrations =
            $this->cursusManager->getSessionQueuesByUser($authenticatedUser);

        foreach ($userSessions as $userSession) {
            $registeredSessions[$userSession->getSession()->getId()] = true;
        }

        foreach ($pendingRegistrations as $pendingRegistration) {
            $pendingSessions[$pendingRegistration->getSession()->getId()] = $pendingRegistration;
        }
        $courseQueues = array();
        $courseQueueRequests = $this->cursusManager->getCourseQueuesByUser($authenticatedUser);

        foreach ($courseQueueRequests as $courseQueueRequest) {
            $courseQueues[$courseQueueRequest->getCourse()->getId()] = true;
        }

        return array(
            'widgetInstance' => $widgetInstance,
            'courses' => $courses,
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'sessions' => $sessions,
            'registeredSessions' => $registeredSessions,
            'pendingSessions' => $pendingSessions,
            'courseQueues' => $courseQueues
        );
    }

    /**
     * @EXT\Route(
     *     "/course/session/{session}/self/register",
     *     name="claro_cursus_course_session_self_register",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Widget:coursesListForRegistrationWidget.html.twig")
     */
    public function courseSessionSelfRegisterAction(
        CourseSession $session,
        User $authenticatedUser
    )
    {
        if ($session->getPublicRegistration()) {

            if ($session->getRegistrationValidation()) {
                $this->cursusManager->addUserToSessionQueue($authenticatedUser, $session);
            } else {
                $this->cursusManager->registerUsersToSession(
                    $session,
                    array($authenticatedUser),
                    0
                );
            }
        }

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/course/{course}/queue/register",
     *     name="claro_cursus_course_queue_register",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function courseQueueRegisterAction(
        Course $course,
        User $authenticatedUser
    )
    {
        $this->cursusManager->addUserToCourseQueue($authenticatedUser, $course);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/course/{course}/queue/cancel",
     *     name="claro_cursus_course_queue_cancel",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function courseQueueCancelAction(
        Course $course,
        User $authenticatedUser
    )
    {
        $this->cursusManager->removeUserFromCourseQueue($authenticatedUser, $course);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/courses/registration/widget/{widgetInstance}/configure/form",
     *     name="claro_cursus_courses_registration_widget_configure_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Widget:coursesRegistrationWidgetConfigureForm.html.twig")
     */
    public function coursesRegistrationWidgetConfigureFormAction(WidgetInstance $widgetInstance)
    {
        $config = $this->cursusManager->getCoursesWidgetConfiguration($widgetInstance);

        $form = $this->formFactory->create(
            new CoursesWidgetConfigurationType(),
            $config
        );

        return array(
            'form' => $form->createView(),
            'config' => $config
        );
    }

    /**
     * @EXT\Route(
     *     "/courses/registration/widget/configure/config/{config}",
     *     name="claro_cursus_courses_registration_widget_configure",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Widget:coursesRegistrationWidgetConfigureForm.html.twig")
     */
    public function coursesRegistrationWidgetConfigureAction(CoursesWidgetConfig $config)
    {
        $form = $this->formFactory->create(
            new CoursesWidgetConfigurationType(),
            $config
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->cursusManager->persistCoursesWidgetConfiguration($config);

            return new JsonResponse('success', 204);
        } else {

            return array(
                'form' => $form->createView(),
                'config' => $config
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/my/courses/widget/{widgetInstance}",
     *     name="claro_cursus_my_courses_widget",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Widget:myCoursesWidget.html.twig")
     */
    public function myCoursesWidgetAction(WidgetInstance $widgetInstance)
    {
        return array('widgetInstance' => $widgetInstance);
    }

    /**
     * @EXT\Route(
     *     "/my/courses/widget/{widgetInstance}/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_cursus_my_courses_list_for_widget",
     *     defaults={"page"=1, "search"="", "max"=20, "orderedBy"="title","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Widget:myCoursesListForWidget.html.twig")
     */
    public function myCoursesListForWidgetAction(
        User $authenticatedUser,
        WidgetInstance $widgetInstance,
        $search = '',
        $page = 1,
        $max = 20,
        $orderedBy = 'title',
        $order = 'ASC'
    )
    {
        $courses = $this->cursusManager->getCoursesByUser(
            $authenticatedUser,
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );
        $sessionUsers = $this->cursusManager->getSessionUsersByUser($authenticatedUser);
        $workspacesList = array();

        foreach ($sessionUsers as $sessionUser) {
            $session = $sessionUser->getSession();
            $course = $session->getCourse();
            $workspace = $session->getWorkspace();

            if (!is_null($workspace)) {
                $workspacesList[$course->getId()] = $workspace;
            }
        }

        return array(
            'widgetInstance' => $widgetInstance,
            'courses' => $courses,
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'workspacesList' => $workspacesList
        );
    }

    /**
     * @EXT\Route(
     *     "/cursus/export",
     *     name="claro_cursus_export",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function cursusExportAction()
    {
        $this->checkToolAccess();
        $cursus = $this->cursusManager->getAllCursus();
        $zipName = 'cursus.zip';
        $mimeType = 'application/zip';
        $file = $this->cursusManager->zipDatas($cursus, 'cursus');;

        $response = new StreamedResponse();
        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . urlencode($zipName));
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Connection', 'close');
        $response->send();

        return new Response();
    }

    /**
     * @EXT\Route(
     *     "/cursus/import/form",
     *     name="claro_cursus_import_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusImportModalForm.html.twig")
     */
    public function cursusImportFormAction()
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(new FileSelectType());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/cursus/import",
     *     name="claro_cursus_import",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusImportModalForm.html.twig")
     */
    public function cursusImportAction()
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(new FileSelectType());
        $form->handleRequest($this->request);
        $file = $form->get('archive')->getData();
        $zip = new \ZipArchive();

        if (empty($file) || 
            !$zip->open($file)||
            !$zip->getStream('cursus.json') ||
            !$zip->getStream('courses.json')) {

            $form->get('archive')->addError(
                new FormError($this->translator->trans('invalid_file', array(), 'cursus'))
            );
        }

        if ($form->isValid()) {
            $coursesStream = $zip->getStream('courses.json');
            $coursesContents = '';

            while (!feof($coursesStream)) {
                $coursesContents .= fread($coursesStream, 2);
            }
            fclose($coursesStream);
            $courses = json_decode($coursesContents, true);
            $importedCourses = $this->cursusManager->importCourses($courses);

            $iconsDir = $this->container->getParameter('claroline.param.thumbnails_directory') . '/';

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);

                if (strpos($name, 'icons/') !== 0) {
                    continue;
                }
                $iconFileName = $iconsDir . substr($name, 6);
                $stream = $zip->getStream($name);
                $destStream = fopen($iconFileName, 'w');

                while ($data = fread($stream, 1024)) {
                    fwrite($destStream, $data);
                }
                fclose($stream);
                fclose($destStream);
            }
            $cursusStream = $zip->getStream('cursus.json');
            $cursuscontents = '';

            while (!feof($cursusStream)) {
                $cursuscontents .= fread($cursusStream, 2);
            }
            fclose($cursusStream);
            $cursus = json_decode($cursuscontents, true);
            $this->cursusManager->importCursus($cursus, $importedCourses);

            $zip->close();

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView());
        }

    }

    private function checkToolAccess()
    {
        $cursusTool = $this->toolManager->getAdminToolByName('claroline_cursus_tool');

        if (is_null($cursusTool) ||
            !$this->authorization->isGranted('OPEN', $cursusTool)) {

            throw new AccessDeniedException();
        }
    }
}
