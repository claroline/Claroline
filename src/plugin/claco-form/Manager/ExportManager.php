<?php

namespace Claroline\ClacoFormBundle\Manager;

use Claroline\AppBundle\Manager\PdfManager;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Comment;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\LocationManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ExportManager
{
    /** @var RouterInterface */
    private $router;
    /** @var ClacoFormManager */
    private $clacoFormManager;
    /** @var PlatformConfigurationHandler */
    private $configHandler;
    /** @var string */
    private $filesDir;
    /** @var LocationManager */
    private $locationManager;
    /** @var Environment */
    private $templating;
    /** @var TranslatorInterface */
    private $translator;
    /** @var PdfManager */
    private $pdfManager;

    public function __construct(
        RouterInterface $router,
        ClacoFormManager $clacoFormManager,
        PlatformConfigurationHandler $configHandler,
        string $filesDir,
        LocationManager $locationManager,
        Environment $templating,
        TranslatorInterface $translator,
        PdfManager $pdfManager
    ) {
        $this->router = $router;
        $this->clacoFormManager = $clacoFormManager;
        $this->configHandler = $configHandler;
        $this->filesDir = $filesDir;
        $this->locationManager = $locationManager;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->pdfManager = $pdfManager;
    }

    public function generatePdfForEntry(Entry $entry, User $user)
    {
        $clacoForm = $entry->getClacoForm();
        $fields = $clacoForm->getFields();

        $fieldValues = [];
        foreach ($entry->getFieldValues() as $fieldValue) {
            $field = $fieldValue->getField();
            $fieldValues[$field->getId()] = $fieldValue->getFieldFacetValue()->getValue();
        }

        $canEdit = $this->clacoFormManager->hasRight($clacoForm, 'EDIT');
        $displayMeta = $clacoForm->getDisplayMetadata();
        $isEntryManager = $user instanceof User && $this->clacoFormManager->isEntryManager($entry, $user);
        $withMeta = $canEdit || 'all' === $displayMeta || ('manager' === $displayMeta && $isEntryManager);

        $template = $clacoForm->getTemplate();
        $useTemplate = $clacoForm->getUseTemplate();
        if (!empty($template) && $useTemplate) {
            $template = str_replace('%clacoform_entry_title%', $entry->getTitle(), $template);
        }

        foreach ($fields as $field) {
            if (!$field->isHidden() && ($withMeta || !$field->isMetadata()) && isset($fieldValues[$field->getId()])) {
                $fieldValues[$field->getId()] = $this->formatFieldValue($entry, $field, $fieldValues[$field->getId()]);
            } else {
                $fieldValues[$field->getId()] = '';
            }

            if (!empty($template) && $useTemplate) {
                $template = str_replace("%field_{$field->getUuid()}%", $fieldValues[$field->getId()], $template);
                // for retro-compatibility with very old templates
                $template = str_replace("%field_{$field->getId()}%", $fieldValues[$field->getId()], $template);
            }
        }

        $canViewComments = $this->clacoFormManager->canViewComments($clacoForm);
        $comments = [];
        if ($canViewComments) {
            $entryComments = $entry->getComments();

            foreach ($entryComments as $comment) {
                if (Comment::VALIDATED === $comment->getStatus()) {
                    $comments[] = $comment;
                }
            }
        }

        return $this->pdfManager->fromHtml(
            $this->templating->render('@ClarolineClacoForm/claco_form/entry.html.twig', [
                'entry' => $entry,
                'template' => $template,
                'useTemplate' => $useTemplate,
                'withMeta' => $withMeta,
                'fields' => $fields,
                'fieldValues' => $fieldValues,
                'canViewComments' => $canViewComments,
                'comments' => $comments,
            ])
        );
    }

    public function exportEntries(ClacoForm $clacoForm)
    {
        $entriesData = [];
        $fields = $clacoForm->getFields();
        $entries = $this->clacoFormManager->getAllEntries($clacoForm);

        foreach ($entries as $entry) {
            $user = $entry->getUser();
            $publicationDate = $entry->getPublicationDate();
            $editionDate = $entry->getEditionDate();
            $fieldValues = $entry->getFieldValues();
            $data = [];
            $data['id'] = $entry->getId();
            $data['uuid'] = $entry->getUuid();
            $data['title'] = $entry->getTitle();

            $data['author'] = $this->translator->trans('anonymous', [], 'platform');
            $data['author_username'] = null;
            $data['author_email'] = null;
            if (!empty($user)) {
                $data['author'] = $user->getFirstName().' '.$user->getLastName();
                $data['author_username'] = $user->getUsername();
                $data['author_email'] = $user->getEmail();
            }

            $data['publicationDate'] = empty($publicationDate) ? '' : $publicationDate->format('d/m/Y');
            $data['editionDate'] = empty($editionDate) ? '' : $editionDate->format('d/m/Y');

            foreach ($fieldValues as $fieldValue) {
                $field = $fieldValue->getField();
                $fieldFacetValue = $fieldValue->getFieldFacetValue();

                $data[$field->getId()] = $this->formatFieldValue($entry, $field, $fieldFacetValue->getValue());
            }

            $entriesData[] = $data;
        }

        return $this->templating->render(
            '@ClarolineClacoForm/claco_form/entries_export.html.twig',
            [
                'fields' => $fields,
                'entries' => $entriesData,
            ]
        );
    }

    public function zipEntries($content, ClacoForm $clacoForm)
    {
        $archive = new \ZipArchive();
        $pathArch = $this->configHandler->getParameter('tmp_dir').DIRECTORY_SEPARATOR.Uuid::uuid4()->toString().'.zip';
        $archive->open($pathArch, \ZipArchive::CREATE);
        $archive->addFromString($clacoForm->getResourceNode()->getName().'.xls', $content);

        $entries = $this->clacoFormManager->getAllEntries($clacoForm);

        foreach ($entries as $entry) {
            $fieldValues = $entry->getFieldValues();

            foreach ($fieldValues as $fieldValue) {
                $field = $fieldValue->getField();
                $fieldFacetValue = $fieldValue->getFieldFacetValue();

                if (FieldFacet::FILE_TYPE === $field->getType()) {
                    /* TODO: change this when FILE_TYPE can accept an array of files again */
                    $file = $fieldFacetValue->getValue();

                    if (!empty($file) && is_array($file)) {
                        $fileUrl = preg_replace('#^\.\.\/files\/#', '', $file['url']); // TODO : files part should not be stored in the DB
                        $filePath = $this->filesDir.DIRECTORY_SEPARATOR.$fileUrl;

                        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                        $fileName = TextNormalizer::toKey($entry->getTitle()).'-'.TextNormalizer::toKey($field->getLabel()).'.'.$ext;

                        $archive->addFile(
                            $filePath,
                            'files'.DIRECTORY_SEPARATOR.TextNormalizer::toKey($entry->getTitle()).DIRECTORY_SEPARATOR.$fileName
                        );
                    }
                }
            }
        }
        $archive->close();

        return $pathArch;
    }

    private function formatFieldValue(Entry $entry, Field $field, $value)
    {
        if (is_null($value)) {
            return '';
        }

        $fieldFacet = $field->getFieldFacet();
        switch ($fieldFacet->getType()) {
            case FieldFacet::DATE_TYPE:
                if (!empty($value)) {
                    $dateValue = DateNormalizer::denormalize($value);
                    if ($dateValue) {
                        $value = $dateValue->format('d/m/Y');
                    }
                }

                break;

            case FieldFacet::CHOICE_TYPE:
                $options = $fieldFacet->getOptions();
                if (is_array($value) && !empty($options) && $options['multiple']) {
                    $value = implode(', ', $value);
                }
                break;

            case FieldFacet::HTML_TYPE:
                // when present, those tags completely broke the pdf generation
                $illegalTags = [
                    '<o:p>',
                    '</o:p>',
                ];

                foreach ($illegalTags as $illegalTag) {
                    $value = str_replace($illegalTag, '', $value);
                }

                break;

            case FieldFacet::CASCADE_TYPE:
                $value = implode(', ', $value);
                break;

            case FieldFacet::COUNTRY_TYPE:
                $value = $this->locationManager->getCountryByCode($value);
                break;

            case FieldFacet::FILE_TYPE:
                if (is_array($value) && !empty($value['url'])) {
                    $downloadUrl = $this->router->generate('claro_claco_form_field_value_file_download', [
                        'entry' => $entry->getUuid(),
                        'field' => $field->getUuid(),
                    ], UrlGeneratorInterface::ABSOLUTE_URL);

                    $fileUrl = preg_replace('#^\.\.\/files\/#', '', $value['url']); // TODO : files part should not be stored in the DB
                    $filePath = $this->filesDir.DIRECTORY_SEPARATOR.$fileUrl;

                    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                    $fileName = TextNormalizer::toKey($field->getLabel()).'.'.$ext;

                    $value = '<a href="'.$downloadUrl.'">'.$fileName.'</a>';
                }

                break;
            case FieldFacet::BOOLEAN_TYPE:
                $value = $value ? $field->getLabel() : '';
                break;
        }

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return $value;
    }
}
