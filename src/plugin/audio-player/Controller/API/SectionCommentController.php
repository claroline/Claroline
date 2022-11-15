<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AudioPlayerBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AudioPlayerBundle\Entity\Resource\SectionComment;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/audioresourcesectioncomment")
 */
class SectionCommentController extends AbstractCrudController
{
    /** @var ClaroUtilities */
    private $claroUtils;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(ClaroUtilities $claroUtils, TranslatorInterface $translator)
    {
        $this->claroUtils = $claroUtils;
        $this->translator = $translator;
    }

    public function getName(): string
    {
        return 'audioresourcesectioncomment';
    }

    public function getClass(): string
    {
        return SectionComment::class;
    }

    public function getIgnore(): array
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    /**
     * @Route(
     *     "/{resourceNode}/list/{type}",
     *     name="apiv2_audioresourcesectioncomment_list_comments"
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param string $type
     *
     * @return JsonResponse
     */
    public function sectionsCommentsListAction(ResourceNode $resourceNode, $type, Request $request)
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['resourceNode'] = $resourceNode->getUuid();
        $params['hiddenFilters']['type'] = $type;

        return new JsonResponse(
            $this->finder->search(SectionComment::class, $params)
        );
    }

    /**
     * @Route(
     *     "/{resourceNode}/comments/csv",
     *     name="apiv2_audioresourcesectioncomment_list_comments_csv"
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return StreamedResponse
     */
    public function sectionsCommentsCsvExportAction(ResourceNode $resourceNode)
    {
        /** @var SectionComment[] $comments */
        $comments = $this->finder->fetch(SectionComment::class, ['resourceNode' => $resourceNode->getUuid()]);
        $dateStr = date('YmdHis');

        return new StreamedResponse(function () use ($comments) {
            $handle = fopen('php://output', 'w+');
            fputcsv($handle, [
                $this->translator->trans('user', [], 'platform'),
                $this->translator->trans('section_start', [], 'audio'),
                $this->translator->trans('section_end', [], 'audio'),
                $this->translator->trans('creation_date', [], 'platform'),
                $this->translator->trans('comment', [], 'platform'),
            ], ';', '"');

            foreach ($comments as $comment) {
                fputcsv($handle, [
                    $comment->getUser() ?
                        $comment->getUser()->getFirstName().' '.$comment->getUser()->getLastName() :
                        $this->translator->trans('anonymous', [], 'platform'),
                    $comment->getSection()->getStart(),
                    $comment->getSection()->getEnd(),
                    $comment->getCreationDate()->format('Y-m-d h:i:s'),
                    $this->claroUtils->html2Csv($comment->getContent(), true),
                ], ';', '"');
            }
            fclose($handle);

            return $handle;
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="'.$resourceNode->getName().'_'.$dateStr.'.csv"',
        ]);
    }
}
