<?php

namespace HeVinci\CompetencyBundle\Controller;

use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Form\Handler\FormHandler;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('competencies')")
 * @EXT\Route(requirements={"id"="\d+", "abilityId"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class CompetencyController
{
    private $manager;
    private $formHandler;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("hevinci.competency.competency_manager"),
     *     "handler" = @DI\Inject("hevinci.form.handler")
     * })
     *
     * @param CompetencyManager $manager
     * @param FormHandler       $handler
     */
    public function __construct(
        CompetencyManager $manager,
        FormHandler $handler
    )
    {
        $this->manager = $manager;
        $this->formHandler = $handler;
    }

    /**
     * Displays the index of the competency tool, i.e. the list
     * of competency frameworks.
     *
     * @EXT\Route("/frameworks", name="hevinci_frameworks")
     * @EXT\Template
     *
     * @return array
     */
    public function frameworksAction()
    {
        return [
            'frameworks' => $this->manager->listFrameworks(),
            'hasScales' => $this->manager->hasScales()
        ];
    }

    /**
     * Displays the framework creation form. If no scale has been created yet,
     * creates a default scale on the fly first.
     *
     * @EXT\Route("/frameworks/new", name="hevinci_new_framework")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:frameworkForm.html.twig")
     *
     * @return array
     */
    public function newFrameworkAction()
    {
        $this->manager->ensureHasScale();

        return ['form' => $this->formHandler->getView('hevinci_form_framework')];
    }

    /**
     * Handles the framework creation form submission.
     *
     * @EXT\Route("/frameworks", name="hevinci_create_framework")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:frameworkForm.html.twig")
     *
     * @param Request $request
     * @return array|JsonResponse
     */
    public function createFrameworkAction(Request $request)
    {
        if ($this->formHandler->isValid('hevinci_form_framework', $request)) {
            return new JsonResponse(
                $this->manager->persistFramework($this->formHandler->getData())
            );
        }

        return ['form' => $this->formHandler->getView()];
    }

    /**
     * Displays the management page for a given framework.
     *
     * @EXT\Route("/frameworks/{id}", name="hevinci_framework")
     * @EXT\Template
     *
     * @param Competency $framework
     * @return array
     */
    public function frameworkAction(Competency $framework)
    {
        return ['framework' => $this->manager->loadFramework($framework)];
    }

    /**
     * Displays the framework edition form.
     *
     * @EXT\Route("/frameworks/{id}/edit", name="hevinci_edit_framework_form")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:frameworkEditForm.html.twig")
     *
     * @param Competency $framework
     * @return array
     */
    public function frameworkEditionFormAction(Competency $framework)
    {
        $this->manager->ensureIsRoot($framework);

        return [
            'form' => $this->formHandler->getView('hevinci_form_framework', $framework),
            'framework' => $framework
        ];
    }

    /**
     * Edits a framework.
     *
     * @EXT\Route("/frameworks/{id}", name="hevinci_edit_framework")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:frameworkEditForm.html.twig")
     *
     * @param Request       $request
     * @param Competency    $framework
     * @return array
     */
    public function editFrameworkAction(Request $request, Competency $framework)
    {
        $this->manager->ensureIsRoot($framework);

        if ($this->formHandler->isValid('hevinci_form_framework', $request, $framework)) {
            return new JsonResponse($this->manager->updateCompetency($framework));
        }

        return ['form' => $this->formHandler->getView(), 'framework' => $framework];
    }

    /**
     * Deletes a competency.
     *
     * @EXT\Route("/{id}/delete", name="hevinci_delete_competency")
     *
     * @param Competency $competency
     * @return JsonResponse
     */
    public function deleteCompetencyAction(Competency $competency)
    {
        return new JsonResponse($this->manager->deleteCompetency($competency));
    }

    /**
     * Displays the competency creation form.
     *
     * @EXT\Route("/{id}/sub", name="hevinci_new_competency")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:competencyForm.html.twig")
     *
     * @param Competency $parent
     * @return array
     */
    public function newSubCompetencyAction(Competency $parent)
    {
        return [
            'form' => $this->formHandler->getView('hevinci_form_competency', null, ['parent_competency' => $parent]),
            'parentId' => $parent->getId()
        ];
    }

    /**
     * Creates a sub-competency.
     *
     * @EXT\Route("/{id}/sub", name="hevinci_create_competency")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:competencyForm.html.twig")
     *
     * @param Request $request
     * @param Competency $parent
     * @return array|JsonResponse
     */
    public function createSubCompetencyAction(Request $request, Competency $parent)
    {
        if ($this->formHandler->isValid('hevinci_form_competency', $request, null, ['parent_competency' => $parent])) {
            return new JsonResponse(
                $this->manager->createSubCompetency($parent, $this->formHandler->getData())
            );
        }

        return ['form' => $this->formHandler->getView(), 'parentId' => $parent->getId()];
    }

    /**
     * Displays the competency view/edit form.
     *
     * @EXT\Route("/{id}/edit", name="hevinci_competency")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:competencyEditForm.html.twig")
     *
     * @param Competency $competency
     * @return array
     */
    public function competencyAction(Competency $competency)
    {
        return [
            'form' => $this->formHandler->getView('hevinci_form_competency', $competency),
            'id' => $competency->getId()
        ];
    }

    /**
     * Edits a competency.
     *
     * @EXT\Route("/{id}", name="hevinci_edit_competency")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:competencyEditForm.html.twig")
     *
     * @param Request       $request
     * @param Competency    $competency
     * @return array
     */
    public function editCompetencyAction(Request $request, Competency $competency)
    {
        if ($this->formHandler->isValid('hevinci_form_competency', $request, $competency)) {
            return new JsonResponse($this->manager->updateCompetency($competency));
        }

        return ['form' => $this->formHandler->getView(), 'id' => $competency->getId()];
    }

    /**
     * Displays the ability creation form.
     *
     * @EXT\Route("/{id}/ability/new", name="hevinci_new_ability")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:abilityForm.html.twig")
     *
     * @param Competency $parent
     * @return array
     */
    public function newAbilityAction(Competency $parent)
    {
        return [
            'form' => $this->formHandler->getView('hevinci_form_ability', null, ['competency' => $parent]),
            'competency' => $parent
        ];
    }

    /**
     * Creates a new ability.
     *
     * @EXT\Route("/{id}/ability", name="hevinci_create_ability")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:abilityForm.html.twig")
     *
     * @param Request       $request
     * @param Competency    $parent
     * @return array
     */
    public function createAbilityAction(Request $request, Competency $parent)
    {
        if ($this->formHandler->isValid('hevinci_form_ability', $request, null, ['competency' => $parent])) {
            return new JsonResponse($this->manager->createAbility(
                $parent,
                $this->formHandler->getData(),
                $this->formHandler->getData()->getLevel()
            ));
        }

        return ['form' => $this->formHandler->getView(), 'competency' => $parent];
    }

    /**
     * Removes an ability or deletes it if it's no longer linked to any competency.
     *
     * @EXT\Route("/{id}/ability/{abilityId}/delete", name="hevinci_delete_ability")
     * @EXT\ParamConverter("ability", options={"id"= "abilityId"})
     *
     * @param Competency $parent
     * @param Ability $ability
     * @return JsonResponse
     */
    public function deleteAbilityAction(Competency $parent, Ability $ability)
    {
        return new JsonResponse($this->manager->removeAbility($parent, $ability));
    }

    /**
     * Displays the ability view/edit form.
     *
     * @EXT\Route("/{id}/ability/{abilityId}", name="hevinci_ability")
     * @EXT\ParamConverter("ability", options={"id"= "abilityId"})
     * @EXT\Template("HeVinciCompetencyBundle:Competency:abilityEditForm.html.twig")
     *
     * @param Competency    $parent
     * @param Ability       $ability
     * @return array
     */
    public function abilityAction(Competency $parent, Ability $ability)
    {
        $this->manager->loadAbility($parent, $ability);

        return [
            'form' => $this->formHandler->getView('hevinci_form_ability', $ability, ['competency' => $parent]),
            'competency' => $parent,
            'ability' => $ability
        ];
    }

    /**
     * Edits an ability.
     *
     * @EXT\Route("/{id}/ability/{abilityId}", name="hevinci_edit_ability")
     * @EXT\ParamConverter("ability", options={"id"= "abilityId"})
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:abilityEditForm.html.twig")
     *
     * @param Request $request
     * @param Competency $parent
     * @param Ability $ability
     * @return array|JsonResponse
     */
    public function editAbilityAction(Request $request, Competency $parent, Ability $ability)
    {
        if ($this->formHandler->isValid('hevinci_form_ability', $request, $ability, ['competency' => $parent])) {
            return new JsonResponse($this->manager->updateAbility(
                $parent,
                $this->formHandler->getData(),
                $this->formHandler->getData()->getLevel()
            ));
        }

        return [
            'form' => $this->formHandler->getView(),
            'competency' => $parent,
            'ability' => $ability
        ];
    }

    /**
     * Displays the ability add form.
     *
     * @EXT\Route("/{id}/ability/add", name="hevinci_add_ability_form")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:abilityAddForm.html.twig")
     *
     * @param Competency $parent
     * @return array
     */
    public function addAbilityFormAction(Competency $parent)
    {
        return [
            'form' => $this->formHandler->getView('hevinci_form_ability_import', null, ['competency' => $parent]),
            'competency' => $parent
        ];
    }

    /**
     * Adds an existing ability to a competency.
     *
     * @EXT\Route("/{id}/ability/add", name="hevinci_add_ability")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:abilityAddForm.html.twig")
     *
     * @param Request $request
     * @param Competency $parent
     * @return array|JsonResponse
     */
    public function addAbility(Request $request, Competency $parent)
    {
        if ($this->formHandler->isValid('hevinci_form_ability_import', $request, null, ['competency' => $parent])) {
            return new JsonResponse($this->manager->linkAbility(
                $parent,
                $this->formHandler->getData(),
                $this->formHandler->getData()->getLevel()
            ));
        }

        return ['form' => $this->formHandler->getView(), 'competency' => $parent];
    }

    /**
     * Returns ability suggestions for linking existing abilities to competencies.
     *
     * @EXT\Route("/{id}/ability/suggest/{query}", name="hevinci_suggest_ability")
     *
     * @param Competency    $parent
     * @param string        $query
     * @return JsonResponse
     */
    public function suggestAbilityAction(Competency $parent, $query)
    {
        return new JsonResponse($this->manager->suggestAbilities($parent, $query));
    }
}
