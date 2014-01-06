<?php

namespace Claroline\CoreBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Installation\Settings\SettingChecker;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

//
// Require 3rd-party libraries here:
//
//require_once 'PHPUnit/Autoload.php';
//require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Feature context.
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    private $kernel;
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
     * Sets HttpKernel instance.
     * This method will be automatically called by Symfony2Extension ContextInitializer.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * @Given /^I have a user "([^"]*)"$/
     */
    public function iHaveAUser($username)
    {
        $userManager = $this->getContainer()->get('claroline.manager.user_manager');
        $user = new User();
        $user->setUsername($username);
        $user->setPassword($username);
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setMail($username . '@claroline.net');
        $userManager->createUser($user);
    }

    /**
     * @Given /^the database does not exists$/
     */
    public function theDatabaseIsEmpty()
    {
        $cn = $this->getContainer()->get('doctrine.dbal.default_connection');
        $exists = true;
        try {
            $cn->query('SELECT 1');
        } catch (\Doctrine\DBAL\Exception\AccessDeniedException $ex) {
            $exists = false;
        }

        if ($exists) {
            throw new \Exception('The database already exists and must be dropped');
        }
    }

    /**
     * @Given /^operation\.xml is initialized$/
     */
    public function operationXmlIsInitialized()
    {
        $ds = DIRECTORY_SEPARATOR;

        $operationFile = $this->kernel->getRootDir() . $ds . 'config' . $ds . 'operations.xml';

        if (!file_exists($operationFile)) {
            file_put_contents(
                $operationFile,
                '<operations><install type="core">Claroline\CoreBundle\ClarolineCoreBundle</install></operations>'
            );
        }
    }

    /**
     * @Given /^base url is web$/
     */
    public function baseUrlIsWeb()
    {
        $this->setMinkParameter('base_url', 'http://localhost/vostro/Claroline/web/');
    }

    /**
     * @Given /^installation directories are writable$/
     */
    public function installationDirectoriesAreWritable()
    {
        $checker = new SettingChecker();
        //var_dump($checker->getSettingCategories());
        //hasFailedRequirequirement() always return false. It's a problem.

        if ($checker->hasFailedRequirement()) {
            //todo show the directory list
            throw new \Exception('Failed requirements');
        }
    }

    /**
     * @Then /^database should exists$/
     */
    public function databaseShouldExists()
    {
        $cn = $this->getContainer()->get('doctrine.dbal.default_connection');
        $cn->query('SELECT 1');
    }

    /**
     * @Then /^user "([^"]*)" should exists$/
     */
    public function userShouldExists($username)
    {
        throw new \Behat\Behat\Exception\PendingException('This assertion does not work. The table never exists');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->getRepository('ClarolineCoreBundle:User')->findOneByUsername($username);
    }

    /**
     * @Given /^the cache directory is writable$/
     */
    public function theCacheDirectoryIsWritable()
    {
        //It doesn't work
        //throw new \Behat\Behat\Exception\PendingException('Does not work');

        $dir = $this->kernel->getRootDir() . '/cache/prod/jms_diextra/metadata';
        $res = is_writeable($dir);;

        if (!$res) {
            throw new \Exception('The cache directory is not writable');
        }
    }

    /**
     * @Then /^the platform should have "([^"]*)" "([^"]*)"$/
     */
    public function thePlatformShouldHave($count, $entity)
    {
        $res = $this->getContainer()->get('claroline.persistence.object_manager')
            ->count('ClarolineCoreBundle:' . $entity);

        if ($res != $count) {
            throw new \Exception('The plateform has ' . $res . ' ' . $entity);
        }
    }

    /**
     * @Given /^the database is initialized/
     */
    public function theDatabaseIsInitialized()
    {
        $start = new \DateTime();
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger(
            $this->getContainer()->get('doctrine.orm.entity_manager')
        );
        $purger->purge();

        //load the required fixture
        $fixture = new \Claroline\CoreBundle\DataFixtures\Required\LoadRequiredFixturesData();
        $referenceRepo = new ReferenceRepository($om);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->load($om);
        $end = new \DateTime();
        $diff = $start->diff($end);
        $duration = $diff->i > 0 ? $diff->i . 'm ' : '';
        $duration .= $diff->s . 's';
    }

    /**
     * @Given /^the user "([^"]*)" is created$/
     */
    public function theUserIsCreated($username)
    {
        $user = new \Claroline\CoreBundle\Entity\User();
        $user->setUsername($username);
        $user->setPlainPassword($username);
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setMail($username . '@claroline.net');
        $this->getContainer()->get('claroline.manager.user_manager')->createUserWithRole($user, 'ROLE_ADMIN');
    }

    private function loadFixture(AbstractFixture $fixture)
    {

    }

}
