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
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CursusController extends Controller
{
    private $cursusManager;
    private $securityContext;
    private $toolManager;
    private $translator;
    
    /**
     * @DI\InjectParams({
     *     "cursusManager"   = @DI\Inject("claroline.manager.cursus_manager"),
     *     "securityContext" = @DI\Inject("security.context"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        CursusManager $cursusManager,
        SecurityContextInterface $securityContext,
        ToolManager $toolManager,
        Translator $translator
    )
    {
        $this->cursusManager = $cursusManager;
        $this->securityContext = $securityContext;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
    }
    
    /**
     * @EXT\Route(
     *     "/tool/index",
     *     name="claro_cursus_tool_index"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function cursusToolIndexAction()
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
    
    
    /********************************
     * Plugin configuration methods *
     ********************************/
    
    
    /**
     * @EXT\Route(
     *     "/displayed/words/configuration",
     *     name="claro_cursus_displayed_words_configuration"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
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
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
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