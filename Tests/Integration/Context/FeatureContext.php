<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Tests\Integration\Context;

use Behat\Behat\Context\Step;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\MinkExtension\Context\MinkContext;
use Claroline\CoreBundle\Library\Installation\Settings\SettingChecker;
use Goutte\Client;

/**
 * Feature context.
 */
class FeatureContext extends MinkContext
{
    use KernelDictionary;

    private $parameters;

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario(ScenarioEvent $event)
    {
        if (in_array('javascript', $event->getScenario()->getTags())) {
            $client = new Client();
            $client->request('GET', $this->getUrl('test/reinstall'));
        } else {
            $this->visit('test/reinstall');
        }
    }

    /**
     * @Given /^the admin account "([^"]*)" is created$/
     */
    public function theAdminAccountIsCreated($username)
    {
        $this->loadFixture(
            'Claroline\CoreBundle\DataFixtures\Test\LoadUserData',
            array(array('username' => $username, 'role' => 'ROLE_ADMIN'))
        );
    }

    /**
     * @Given /^the following accounts are created:$/
     */
    public function theFollowingAccountsAreCreated(TableNode $table)
    {
        $users = array();

        foreach ($table->getRows() as $row) {
            $users[] = array('username' => $row[0], 'role' => $row[1]);
        }

        $this->loadFixture('Claroline\CoreBundle\DataFixtures\Test\LoadUserData', $users);
    }

    /**
     * @Given /^the following groups are created:$/
     */
    public function theFollowingGroupsAreCreated(TableNode $table)
    {
        $groups = array();

        foreach ($table->getRows() as $row) {
            $groups[] = array('name' => $row[0], 'role' => $row[1]);
        }

        $this->loadFixture('Claroline\CoreBundle\DataFixtures\Test\LoadGroupData', $groups);
    }

    /**
     * Connects the user with login and password
     *
     * @Given /^I log in with "([^"]*)"\/"([^"]*)"$/
     */
    public function iLogInWith($login, $password)
    {
        return array(
            new Step\When('I am on "/login"'),
            new Step\When('I fill in "Username or email" with "'. $login . '"'),
            new Step\When('I fill in "Password" with "'. $password . '"'),
            new Step\When('I press "Login"')
        );
    }

    /**
     * @When /^I follow the hidden "([^"]*)"$/
     */
    public function iFollowTheHidden($label)
    {
        $script = "(function() { $('a:contains(\"{$label}\")')[0].click(); })();";
        $this->getSession()->evaluateScript($script);
    }

    /**
     * @Given /^I check the line containing "([^"]*)"$/
     */
    public function iCheckTableLineContaining($text)
    {
        $script = "(function() {
            var row = $('tr:contains(\"{$text}\")');
            var selector = '#' + row.attr('id') + ' input:checkbox';
            var checkbox = $(selector);
            $(checkbox).prop('checked', 'checked');
        })();";
        $this->getSession()->evaluateScript($script);
    }

    /**
     * @Given /^self registration is allowed$/
     */
    public function selfRegistrationIsAllowed()
    {
        $configHandler = $this->getContainer()->get('claroline.config.platform_config_handler');
        $configHandler->setParameters(array('allow_self_registration' => true));
    }

    /**
     * @Given /^self registration is disabled$/
     */
    public function selfRegistrationIsDisabled()
    {
        $configHandler = $this->getContainer()->get('claroline.config.platform_config_handler');
        $configHandler->setParameters(array('allow_self_registration' => false));
    }

    protected function loadFixture($fixtureFqcn, array $args = array())
    {
        $client = new Client();
        $client->request(
            'POST',
            $this->getUrl('test/fixture/load'),
            array('fqcn' => $fixtureFqcn, 'args' => $args)
        );
        $response = $client->getResponse();

        if ($response->getStatus() !== 200 || preg_match('/Fatal error/i', $response->getContent())) {
            throw new \Exception(
                "Unable to load {$fixtureFqcn} fixture.\n"
                . "Response status is: {$response->getStatus()}\n"
                . "Response content is: {$response->getContent()}"
            );
        }
    }

    private function getUrl($path)
    {
        return $this->getMinkParameter('base_url') . $path;
    }
}
