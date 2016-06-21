<?php

namespace Claroline\CoreBundle\Library\Security\Evaluator;

use Doctrine\ORM\EntityManagerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service
 * @DI\Tag(
 *     name="security.expressions.function_evaluator",
 *     attributes={"function"="canOpenAdminTool"}
 * )
 */
class AdminToolAccessEvaluator
{
    private $authorization;
    private $em;

    /**
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "em"            = @DI\Inject("doctrine.orm.entity_manager")
     * })
     *
     * @param SecurityContextInterface $context
     * @param EntityManagerInterface   $em
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EntityManagerInterface $em
    ) {
        $this->authorization = $authorization;
        $this->em = $em;
    }

    /**
     * @DI\SecurityFunction("canOpenAdminTool(toolName)")
     *
     * @param string $toolName
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function canOpenAdminTool($toolName)
    {
        $tool = $this->em->getRepository('ClarolineCoreBundle:Tool\AdminTool')
            ->findOneBy(['name' => $toolName]);

        if (!$tool) {
            throw new \LogicException(
                "Annotation error: cannot found admin tool '{$toolName}'"
            );
        }

        return $this->authorization->isGranted('OPEN', $tool);
    }
}
