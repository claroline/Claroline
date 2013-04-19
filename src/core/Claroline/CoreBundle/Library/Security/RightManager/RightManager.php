<?php

namespace Claroline\CoreBundle\Library\Security\RightManager;

use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Dbal\AclProvider;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\CoreBundle\Library\Security\RightManager\Delegate\StrategyChooser;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.security.right_manager")
 */
class RightManager implements RightManagerInterface
{
    /** @var AclProvider */
    private $aclProvider;

    /** @var StrategyChooser */
    private $strategyChooser;

    /** @var TargetDelegateInterface */
    private $currentTargetStrategy;

    /** @var SubjectDelegateInterface */
    private $currentSubjectStrategy;

    /**
     * @DI\InjectParams({
     *     "aclProvider" = @DI\Inject("security.acl.provider"),
     *     "strategyChooser" = @DI\Inject("claroline.security.right_manager.strategy_chooser")
     * })
     */
    public function __construct(AclProvider $aclProvider, StrategyChooser $strategyChooser)
    {
        $this->aclProvider = $aclProvider;
        $this->strategyChooser = $strategyChooser;
        $this->currentTargetStrategy = null;
        $this->currentSubjectStrategy = null;
    }

    public function addRight($target, $subject, $mask)
    {
        $this->chooseStrategy($target, $subject);

        $sid = $this->currentSubjectStrategy->buildSecurityIdentity($subject);
        $acl = $this->getAclFromTarget($target);

        $this->currentTargetStrategy->insertAce($acl, $sid, $mask);

        $this->aclProvider->updateAcl($acl);
    }

    public function removeRight($target, $subject, $mask)
    {
        $this->chooseStrategy($target, $subject);
        $this->doRemoveRight($target, $subject, $mask);
    }

    public function removeAllRights($target, $subject)
    {
        $this->chooseStrategy($target, $subject);
        $this->doRemoveRight($target, $subject, 0);
    }

    public function getRight($target, $subject)
    {
        $this->chooseStrategy($target, $subject);
        $acl = $this->getAclFromTarget($target);
        $aces = $this->currentTargetStrategy->getAces($acl);
        $sid = $this->currentSubjectStrategy->buildSecurityIdentity($subject);
        $mask = null;

        foreach ($aces as $ace) {
            if ($ace->getSecurityIdentity() == $sid) {
                $mask = $ace->getMask();
                break;
            }
        }

        return $mask;
    }

    public function setRight($target, $subject, $mask)
    {
        $this->chooseStrategy($target, $subject);
        $this->removeAllRights($target, $subject);
        $this->addRight($target, $subject, $mask);
    }

    public function getUsersWithRight($target, $rightMask)
    {
        $this->currentTargetStrategy =
            $this->strategyChooser->chooseTargetStrategy($target);
        $this->currentSubjectStrategy =
            $this->strategyChooser->getUserDelegate();

        $acl = $this->getAclFromTarget($target);
        $aces = $this->currentTargetStrategy->getAces($acl);

        $res = array();

        foreach ($aces as $ace) {
            $compatibleAce = $this->isCompatibleMask($ace->getMask(), $rightMask);

            if ($compatibleAce) {
                $sid = $ace->getSecurityIdentity();
                $res[] = $this->currentSubjectStrategy->buildSubject($sid);
            }
        }

        return $res;
    }

    public function hasRight($target, $subject, $rightMask)
    {
        $this->chooseStrategy($target, $subject);
        $acl = $this->getAclFromTarget($target);

        $sid = $this->currentSubjectStrategy->buildSecurityIdentity($subject);

        try {
            return $acl->isGranted(array($rightMask), array($sid));
        } catch (NoAceFoundException $ex) {
            unset($ex);

            return false;
        }
    }

    private function doRemoveRight($target, $subject, $mask = 0)
    {
        $sid = $this->currentSubjectStrategy->buildSecurityIdentity($subject);
        $acl = $this->getAclFromTarget($target);

        $this->doRecursiveRemoveRight($acl, $sid, $mask, 0);
        $this->aclProvider->updateAcl($acl);
    }

    private function doRecursiveRemoveRight(Acl $acl, $sid, $mask, $startIndex)
    {
        $aces = $this->currentTargetStrategy->getAces($acl);
        $aceCount = count($aces);

        if ($aceCount === 0) {
            return;
        }

        if ($startIndex < 0) {
            return;
        }

        for ($aceIndex = $startIndex; $aceIndex < $aceCount; ++$aceIndex) {
            $ace = $aces[$aceIndex];
            $compatibleAce =
                $ace->getSecurityIdentity() == $sid
                && $this->isCompatibleMask($ace->getMask(), $mask);

            if ($compatibleAce) {
                $currentMask = $ace->getMask();
                $mb = new MaskBuilder($currentMask);
                $mb->remove($mask);
                $updatedMask = $mb->get();

                if ($updatedMask == 0 || $mask == 0) {
                    $this->currentTargetStrategy->deleteAce($acl, $aceIndex);
                    $this->doRecursiveRemoveRight($acl, $sid, $mask, $aceIndex);

                    return;
                } else {

                    $this->currentTargetStrategy->updateAce($acl, $aceIndex, $updatedMask);
                }
            }
        }
    }

    private function isCompatibleMask($testedMask, $baseMask)
    {
        return $baseMask == ($baseMask & $testedMask);
    }

    private function getAclFromTarget($target)
    {
        $oid = $this->currentTargetStrategy->buildObjectIdentity($target);

        try {
            $acl = $this->aclProvider->findAcl($oid);
        } catch (AclNotFoundException $ex) {
            unset($ex);
            $acl = $this->aclProvider->createAcl($oid);
        }

        return $acl;
    }

    private function chooseStrategy($target = null, $subject = null)
    {
        $this->currentTargetStrategy = $this->strategyChooser->chooseTargetStrategy($target);
        $this->currentSubjectStrategy = $this->strategyChooser->chooseSubjectStrategy($subject);
    }
}