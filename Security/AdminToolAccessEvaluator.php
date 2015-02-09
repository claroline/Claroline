<?php

namespace HeVinci\CompetencyBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Service
 * @DI\Tag(
 *     name="security.expressions.function_evaluator",
 *     attributes={"function"="canOpenAdminTool"}
 * )
 */
class AdminToolAccessEvaluator
{
    private $securityContext;
    private $em;

    /**
     * @DI\InjectParams({
     *     "context"    = @DI\Inject("security.context"),
     *     "em"         = @DI\Inject("doctrine.orm.entity_manager")
     * })
     *
     * @param SecurityContextInterface $context
     * @param EntityManagerInterface $em
     */
    public function __construct(
        SecurityContextInterface $context,
        EntityManagerInterface $em
    )
    {
        $this->securityContext = $context;
        $this->em = $em;
    }

    /**
     * @DI\SecurityFunction("canOpenAdminTool(toolName)")
     *
     * @param string $toolName
     * @throws \Exception
     * @return bool
     */
    public function canOpenAdminTool($toolName)
    {
        $tool = $this->em->getRepository('ClarolineCoreBundle:Tool\AdminTool')
            ->findOneBy(['name' => $toolName]);

        if (!$tool) {
            throw new \Exception(
                "Annotation error: cannot found admin tool '{$toolName}'"
            );
        }

        return $this->securityContext->isGranted('OPEN', $tool);
    }
}