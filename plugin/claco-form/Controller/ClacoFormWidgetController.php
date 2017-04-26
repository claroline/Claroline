<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Controller;

use Claroline\ClacoFormBundle\Entity\ClacoFormWidgetConfig;
use Claroline\ClacoFormBundle\Form\ClacoFormWidgetConfigurationType;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Manager\Organization\LocationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class ClacoFormWidgetController extends Controller
{
    private $clacoFormManager;
    private $formFactory;
    private $locationManager;
    private $request;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "clacoFormManager" = @DI\Inject("claroline.manager.claco_form_manager"),
     *     "formFactory"      = @DI\Inject("form.factory"),
     *     "locationManager"  = @DI\Inject("claroline.manager.organization.location_manager"),
     *     "request"          = @DI\Inject("request"),
     *     "translator"       = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ClacoFormManager $clacoFormManager,
        FormFactory $formFactory,
        LocationManager $locationManager,
        Request $request,
        TranslatorInterface $translator
    ) {
        $this->clacoFormManager = $clacoFormManager;
        $this->formFactory = $formFactory;
        $this->locationManager = $locationManager;
        $this->request = $request;
        $this->translator = $translator;
    }

    /**
     * @EXT\Route(
     *     "/claco/form/widget/{widgetInstance}/open",
     *     name="claro_claco_form_widget_open",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     */
    public function clacoFormWidgetOpenAction(WidgetInstance $widgetInstance)
    {
        $config = $this->clacoFormManager->getClacoFormWidgetConfiguration($widgetInstance);
        $nbEntries = $config->getNbEntries();
        $showFieldLabel = $config->getShowFieldLabel();
        $showCreatorPicture = $config->getShowCreatorPicture();
        $categoriesIds = $config->getCategories();
        $resourceNode = $config->getResourceNode();
        $clacoForm = is_null($resourceNode) ? null : $this->clacoFormManager->getClacoFormByResourceNode($resourceNode);
        $fields = $config->getFields();
        $entries = is_null($clacoForm) ? [] : $this->clacoFormManager->getNRandomEntries($clacoForm, $nbEntries, $categoriesIds);
        $data = [];

        foreach ($entries as $entry) {
            if (count($fields) === 0) {
                $value = [
                    'label' => $this->translator->trans('entry_title', [], 'clacoform'),
                    'value' => $entry->getTitle(),
                    'type' => 0,
                ];
                $data[] = ['values' => [$value], 'entry' => $entry];
            } else {
                $values = [];

                foreach ($fields as $field) {
                    $type = $field->getFieldFacet()->getType();
                    $fieldValue = $this->clacoFormManager->getFieldValueByEntryAndField($entry, $field);

                    if (is_null($fieldValue)) {
                        $value = null;
                    } else {
                        $value = $fieldValue->getFieldFacetValue()->getValue();

                        switch ($type) {
                            case FieldFacet::DATE_TYPE:
                                $value = !is_null($value) ? $value->format('d/m/Y') : $value;
                                break;
                            case FieldFacet::CHECKBOXES_TYPE:
                            case FieldFacet::CASCADE_SELECT_TYPE:
                                $value = is_array($value) ? implode(', ', $value) : $value;
                                break;
                            case FieldFacet::COUNTRY_TYPE:
                                $value = $this->locationManager->getCountryByCode($value);
                                break;
                        }
                    }
                    $values[] = ['label' => $field->getName(), 'value' => $value, 'type' => $type];
                }
                $data[] = ['values' => $values, 'entry' => $entry];
            }
        }

        return [
            'clacoForm' => $clacoForm,
            'data' => $data,
            'showFieldLabel' => $showFieldLabel,
            'showCreatorPicture' => $showCreatorPicture,
        ];
    }

    /**
     * @EXT\Route(
     *     "/claco/form/widget/{widgetInstance}/configure/form",
     *     name="claro_claco_form_widget_configure_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     */
    public function clacoFormWidgetConfigureFormAction(WidgetInstance $widgetInstance)
    {
        $config = $this->clacoFormManager->getClacoFormWidgetConfiguration($widgetInstance);
        $form = $this->formFactory->create(new ClacoFormWidgetConfigurationType($config, $this->clacoFormManager), $config);

        return ['form' => $form->createView(), 'config' => $config];
    }

    /**
     * @EXT\Route(
     *     "/claco/form/widget/config/{config}/configure",
     *     name="claro_claco_form_widget_configure",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineClacoFormBundle:ClacoFormWidget:clacoFormWidgetConfigureForm.html.twig")
     */
    public function clacoFormWidgetConfigureAction(ClacoFormWidgetConfig $config)
    {
        $form = $this->formFactory->create(new ClacoFormWidgetConfigurationType($config, $this->clacoFormManager), $config);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $nbEntries = $form->get('nbEntries')->getData();
            $showFieldLabel = $form->get('showFieldLabel')->getData();
            $showCreatorPicture = $form->get('showCreatorPicture')->getData();
            $categoriesList = $form->get('categories')->getData();
            $config->setNbEntries($nbEntries);
            $config->setShowFieldLabel($showFieldLabel);
            $config->setShowCreatorPicture($showCreatorPicture);
            $categoriesIdsList = [];

            foreach ($categoriesList as $category) {
                $categoriesIdsList[] = $category->getId();
            }
            $config->setCategories($categoriesIdsList);
            $this->clacoFormManager->persistClacoFormWidgetConfiguration($config);

            return new JsonResponse('success', 204);
        } else {
            return ['form' => $form->createView(), 'config' => $config];
        }
    }

    /**
     * @EXT\Route(
     *     "/claco/form/resource/node/{resourceNode}/fields/retrieve",
     *     name="claro_claco_form_non_confidential_fields_retrieve",
     *     options={"expose"=true}
     * )
     */
    public function clacoFormNonConfidentialFieldsRetrieveAction(ResourceNode $resourceNode)
    {
        $data = [];
        $fields = $this->clacoFormManager->getNonConfidentialFieldsByResourceNode($resourceNode);

        foreach ($fields as $field) {
            $data[] = ['id' => $field->getId(), 'name' => $field->getName()];
        }

        return new JsonResponse($data);
    }

    /**
     * @EXT\Route(
     *     "/claco/form/resource/node/{resourceNode}/categories/retrieve",
     *     name="claro_claco_form_categories_retrieve",
     *     options={"expose"=true}
     * )
     */
    public function clacoFormCategoriesRetrieveAction(ResourceNode $resourceNode)
    {
        $clacoForm = $this->clacoFormManager->getClacoFormByResourceNode($resourceNode);
        $displayCategories = $clacoForm->getDisplayCategories();
        $data = [];
        $categories = $displayCategories ? $clacoForm->getCategories() : [];

        foreach ($categories as $category) {
            $data[] = ['id' => $category->getId(), 'name' => $category->getName()];
        }

        return new JsonResponse($data);
    }
}
