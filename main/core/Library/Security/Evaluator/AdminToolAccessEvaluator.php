<?php

namespace Claroline\CoreBundle\Library\Security\Evaluator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminToolAccessEvaluator
{
    private $authorization;
    private $em;

    /**
     * @param AuthorizationCheckerInterface $authorization
     * @param EntityManagerInterface        $em
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EntityManagerInterface $em
    ) {
        $this->authorization = $authorization;
        $this->em = $em;
    }

    /**
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
