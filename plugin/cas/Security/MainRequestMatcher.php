<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/14/17
 */

namespace Claroline\CasBundle\Security;

use Claroline\CasBundle\Library\Configuration\CasServerConfigurationFactory;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Class CasRequestMatcher.
 *
 * @DI\Service("claroline.cas.firewall.main_matcher")
 */
class MainRequestMatcher implements RequestMatcherInterface
{
    private $casConfiguration;
    /**
     * @DI\InjectParams({
     *     "casConfigFactory"       = @DI\Inject("claroline.factory.cas_configuration")
     * })
     *
     * @param CasServerConfigurationFactory $casConfigFactory
     */
    public function __construct(
        CasServerConfigurationFactory $casConfigFactory
    ) {
        $this->casConfiguration = $casConfigFactory->getCasConfiguration();
    }

    /**
     * Decides whether the rule(s) implemented by the strategy matches the supplied request.
     *
     * @param Request $request The request to check for a match
     *
     * @return bool true if the request matches, false otherwise
     */
    public function matches(Request $request)
    {
        $this->checkCasSessionParam($request);
        // Already logged using CAS or CAS path
        $casMatcher = new RequestMatcher('^/cas');
        $loginCheckMatcher = new RequestMatcher('^/login_check');
        $isCASTicket = $request->query->get('ticket') !== null ?
            preg_match('/^ST/', $request->query->get('ticket')) :
            false;
        if (
            !empty($request->getSession()->get('LOGGED_VIA_CAS')) ||
            ($this->casConfiguration->isActive() &&
                ($casMatcher->matches($request) || ($loginCheckMatcher->matches($request) && $isCASTicket))
            )
        ) {
            return false;
        }

        return true;
    }

    private function checkCasSessionParam(Request $request)
    {
        // In login page reset session param for CAS login
        if (!$this->casConfiguration->isOverrideLogin()) {
            $matcher = new RequestMatcher('^/login$');
            if ($matcher->matches($request)) {
                $request->getSession()->remove('LOGGED_VIA_CAS');
            }
        }
    }
}
