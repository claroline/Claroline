<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\Manager\PdfManager as BasePdfManager;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Symfony\Contracts\Translation\TranslatorInterface;

class PdfManager
{
    /** @var TranslatorInterface */
    private $translator;
    /** @var PlatformManager */
    private $platformManager;
    /** @var BasePdfManager */
    private $pdfManager;
    /** @var TemplateManager */
    private $templateManager;

    public function __construct(
        TranslatorInterface $translator,
        PlatformManager $platformManager,
        BasePdfManager $pdfManager,
        TemplateManager $templateManager
    ) {
        $this->translator = $translator;
        $this->platformManager = $platformManager;
        $this->pdfManager = $pdfManager;
        $this->templateManager = $templateManager;
    }

    public function getWorkspaceParticipationCertificate(Evaluation $evaluation, string $locale): ?string
    {
        // only generate certificate if the evaluation is finished
        if (!$evaluation->isTerminated()) {
            return null;
        }

        $placeholders = $this->getCommonPlaceholders($evaluation);

        return $this->pdfManager->fromHtml(
            $this->templateManager->getTemplate('workspace_participation_certificate', $placeholders, $locale)
        );
    }

    public function getWorkspaceSuccessCertificate(Evaluation $evaluation, string $locale): ?string
    {
        // only generate certificate if the evaluation is finished and has success/failure status
        if (!$evaluation->isTerminated() || !in_array($evaluation->getStatus(), [AbstractEvaluation::STATUS_PASSED, AbstractEvaluation::STATUS_FAILED])) {
            return null;
        }

        $placeholders = array_merge($this->getCommonPlaceholders($evaluation), [
            'evaluation_score' => $evaluation->getScore() ? $evaluation->getScore() : '0',
            'evaluation_score_max' => $evaluation->getScoreMax(),
        ]);

        return $this->pdfManager->fromHtml(
            $this->templateManager->getTemplate('workspace_success_certificate', $placeholders, $locale)
        );
    }

    private function getCommonPlaceholders(Evaluation $evaluation): array
    {
        $workspace = $evaluation->getWorkspace();
        $user = $evaluation->getUser();

        return [
            'workspace_name' => $workspace->getName(),
            'workspace_code' => $workspace->getCode(),
            'workspace_description' => $workspace->getDescription(),
            'workspace_poster' => $workspace->getPoster() ? '<img src="'.$this->platformManager->getUrl().'/'.$workspace->getPoster().'" style="max-width: 100%;"/>' : '',

            'user_first_name' => $user->getFirstName(),
            'user_last_name' => $user->getLastName(),
            'user_username' => $user->getUsername(),

            'evaluation_duration' => round($evaluation->getDuration() / 60, 2), // in minutes
            'evaluation_status' => $this->translator->trans('evaluation_'.$evaluation->getStatus().'_status', [], 'workspace'),
            'evaluation_date' => $evaluation->getDate()->format('d/m/Y H:i'),
        ];
    }
}
