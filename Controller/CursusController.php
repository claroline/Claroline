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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use Claroline\CursusBundle\Form\CourseType;
use Claroline\CursusBundle\Form\CursusType;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CursusController extends Controller
{
    private $cursusManager;
    private $formFactory;
    private $request;
    private $securityContext;
    private $toolManager;
    private $translator;
    
    /**
     * @DI\InjectParams({
     *     "cursusManager"   = @DI\Inject("claroline.manager.cursus_manager"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "securityContext" = @DI\Inject("security.context"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        CursusManager $cursusManager,
        FormFactory $formFactory,
        RequestStack $requestStack,
        SecurityContextInterface $securityContext,
        ToolManager $toolManager,
        Translator $translator
    )
    {
        $this->cursusManager = $cursusManager;
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->securityContext = $securityContext;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
    }


    /******************
     * Cursus methods *
     ******************/


    /**
     * @EXT\Route(
     *     "/tool/index",
     *     name="claro_cursus_tool_index"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
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
            'type' => 'cursus',
            'allRootCursus' => $allRootCursus
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/create/form",
     *     name="claro_cursus_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
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
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
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
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusEditModalForm.html.twig")
     *
     * @param Cursus $cursus
     */
    public function cursusEditFormAction(Cursus $cursus)
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(
            new CursusType(),
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
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusEditModalForm.html.twig")
     *
     * @param Cursus $cursus
     */
    public function cursusEditAction(Cursus $cursus)
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(
            new CursusType(),
            $cursus
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
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
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
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
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:cursusViewHierarchyModal.html.twig")
     *
     * @param Cursus $cursus
     */
    public function cursusViewHierarchyAction(Cursus $cursus)
    {
        $this->checkToolAccess();

        return array('cursus' => $cursus);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/delete",
     *     name="claro_cursus_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
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
     *     "cursus/{cursus}/management",
     *     name="claro_cursus_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
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

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
            'type' => 'cursus',
            'cursus' => $cursus
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/{parent}/child/create/form",
     *     name="claro_cursus_child_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
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
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
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
            $orderMax = $this->cursusManager->getLastRootCursusOrder();

            if (is_null($orderMax)) {
                $cursus->setCursusOrder(1);
            } else {
                $cursus->setCursusOrder(intval($orderMax) + 1);
            }
            $this->cursusManager->persistCursus($cursus);

            return new JsonResponse('success', 200);
        } else {

            return array(
                'form' => $form->createView(),
                'parent' => $parent
            );
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/associate/user/{user}",
     *     name="claro_cursus_associate_user",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function cursusUserAssociateAction(Cursus $cursus, User $user)
    {
        $this->checkToolAccess();
        $this->cursusManager->registerUserToCursus($cursus, $user);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/dissociate/user/{user}",
     *     name="claro_cursus_dissociate_user",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function cursusUserDissociateAction(Cursus $cursus, User $user)
    {
        $this->checkToolAccess();
        $this->cursusManager->unregisterUserFromCursus($cursus, $user);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/associate/group/{group}",
     *     name="claro_cursus_associate_group",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function cursusGroupAssociateAction(Cursus $cursus, Group $group)
    {
        $this->checkToolAccess();
        $this->cursusManager->registerGroupToCursus($cursus, $group);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/dissociate/group/{group}",
     *     name="claro_cursus_dissociate_group",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function cursusGroupDissociateAction(Cursus $cursus, Group $group)
    {
        $this->checkToolAccess();
        $this->cursusManager->unregisterGroupFromCursus($cursus, $group);

        return new JsonResponse('success', 200);
    }


    /******************
     * Course methods *
     ******************/


    /**
     * @EXT\Route(
     *     "/tool/course/index/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_cursus_tool_course_index",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="title","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function cursusToolCourseIndexAction(
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'title',
        $order = 'ASC'
    )
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }
        $courses = $search === '' ?
            $this->cursusManager->getAllCourses($orderedBy, $order, $page, $max) :
            $this->cursusManager->getSearchedCourses($search, $orderedBy, $order, $page, $max);

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
            'type' => 'course',
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
     *     "cursus/course/create/form",
     *     name="claro_cursus_course_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:courseCreateModalForm.html.twig")
     */
    public function courseCreateFormAction()
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(new CourseType());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "cursus/course/create",
     *     name="claro_cursus_course_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:courseCreateModalForm.html.twig")
     */
    public function courseCreateAction()
    {
        $this->checkToolAccess();
        $course = new Course();
        $form = $this->formFactory->create(new CourseType(), $course);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->cursusManager->persistCourse($course);

            $message = $this->translator->trans(
                'course_creation_confirm_msg' ,
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
     *     "cursus/course/{course}/edit/form",
     *     name="claro_cursus_course_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:courseEditModalForm.html.twig")
     *
     * @param Course $course
     */
    public function courseEditFormAction(Course $course)
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(
            new CourseType(),
            $course
        );

        return array(
            'form' => $form->createView(),
            'course' => $course
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/course/{course}/edit",
     *     name="claro_cursus_course_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:courseEditModalForm.html.twig")
     *
     * @param Course $course
     */
    public function courseEditAction(Course $course)
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(
            new CourseType(),
            $course
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->cursusManager->persistCourse($course);

            $message = $this->translator->trans(
                'course_edition_confirm_msg' ,
                array(),
                'cursus'
            );
            $session = $this->request->getSession();
            $session->getFlashBag()->add('success', $message);

            return new JsonResponse('success', 200);
        } else {

            return array(
                'form' => $form->createView(),
                'course' => $course
            );
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/course/{course}/delete",
     *     name="claro_cursus_course_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param Course $course
     */
    public function courseDeleteAction(Course $course)
    {
        $this->checkToolAccess();
        $this->cursusManager->deleteCourse($course);

        $message = $this->translator->trans(
            'course_deletion_confirm_msg' ,
            array(),
            'cursus'
        );
        $session = $this->request->getSession();
        $session->getFlashBag()->add('success', $message);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/cursus/course/{course}/description/display",
     *     name="claro_cursus_course_display_description",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Cursus:courseDescriptionDisplayModal.html.twig")
     *
     * @param Course $course
     */
    public function courseDescriptionDisplayAction(Course $course)
    {
        $this->checkToolAccess();

        return array('description' => $course->getDescription());
    }

    /**
     * @EXT\Route(
     *     "cursus/course/{course}/management",
     *     name="claro_cursus_course_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Cursus $cursus
     *
     */
    public function courseManagementAction(Course $course)
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
            'type' => 'course',
            'course' => $course
        );
    }
    
    
    /********************************
     * Plugin configuration methods *
     ********************************/
    
    
    /**
     * @EXT\Route(
     *     "/displayed/words/configuration",
     *     name="claro_cursus_displayed_words_configuration"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function displayedWordsConfigurationAction()
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
     *     "/admin/displayed/word/{key}/change/{value}",
     *     name="claro_cursus_change_displayed_word",
     *     defaults={"value"=""},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function displayedWordChangeAction($key, $value = '')
    {
        $this->securityContext->isGranted('ROLE_ADMIN');
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

    private function checkToolAccess()
    {
        $cursusTool = $this->toolManager->getAdminToolByName('claroline_cursus_tool');
        
        if (is_null($cursusTool) ||
            !$this->securityContext->isGranted('OPEN', $cursusTool)) {

            throw new AccessDeniedException();
        }
    }
}