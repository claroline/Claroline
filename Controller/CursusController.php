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

use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
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
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
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
            'type' => 'cursus',
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

        $courses = $search === '' ?
            $this->cursusManager->getAllCourses($orderedBy, $order, $page, $max) :
            $this->cursusManager->getSearchedCourses($search, $orderedBy, $order, $page, $max);

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
        $this->cursusManager->addCoursesToCursus($cursus, array($course));

        return new JsonResponse('success', 200);
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
    
    
    /********************************
     * Plugin configuration methods *
     ********************************/
    
    
    /**
     * @EXT\Route(
     *     "/displayed/words/configuration",
     *     name="claro_cursus_displayed_words_configuration"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
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
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
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