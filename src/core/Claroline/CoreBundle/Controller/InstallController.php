<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Installation\Install;
use Claroline\CoreBundle\Form\InstallType;
use Claroline\CoreBundle\Form\AdminType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Exception\DumpException;
use Doctrine\DBAL\DriverManager;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @todo Remove all echos !
*  @todo Remove test fields from createParamatersYml method when Claronext will be ready
 */
class InstallController extends Controller
{
    const PATH = '../app/config/local/install.yml';
    const FINAL_PATH = '../app/config/local/parameters.yml';

    /**
     * @Route(
     *     "/",
     *     name="claro_setup"
     * )
     */
    public function indexAction()
    {
        $this->get('translator')->setlocale('en');

        return $this->render(
            'ClarolineCoreBundle:Install:index.html.twig'
        );
    }

    /**
     * @Route(
     *     "/permission",
     *     name="claro_permission"
     * )
     */
    public function permissionAction()
    {
        $request = $this->get('request');
        if ($request->getMethod() == 'GET') {
            $lg = $_GET['lg'];
        } else {
            $lg = 'en';
        }

        $this->get('translator')->setlocale($lg);
        $version = array(
            'php' => phpversion(),
            'mysql' => mysqli_get_client_version(),
            'lg' => $lg

        );

        $ds = DIRECTORY_SEPARATOR;
        $writableFolders = array(
            "app{$ds}config{$ds}local", 'files', 'web', "app{$ds}logs", "app{$ds}cache", 'templates'
        );
        $folders = array();

        foreach ($writableFolders as $folder) {
            $folders[$folder] = (is_writable("..{$ds}{$folder}") ? 'OK' : 'KO');
        }

        return $this->render(
            'ClarolineCoreBundle:Install:permission.html.twig',
            array(
                'version' => $version,
                'folders' => $folders
                )
        );
    }

    /**
     * @Route(
     *     "/step_2/{lg}",
     *     name="claro_showDbForm"
     * )
     * @Method({"POST","GET"})
     */
    public function showDbFormAction($lg)
    {
        $ourFileHandle = fopen(self::PATH, 'w'); //where all the data from the forms will be store
        fclose($ourFileHandle);
        $locale = array('locale' => $lg);
        $this->putInYml($locale);
        $this->get('translator')->setlocale($lg);
        $install = new Install();
        $form = $this->createForm(new InstallType, $install);

        return $this->render(
            'ClarolineCoreBundle:Install:checkupDb.html.twig',
            array(
                'version' => $lg,
                'form' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/step_check_2",
     *     name="claro_checkDbForm"
     * )
     * @Method("POST")
     */
    public function checkDbFormAction()
    {
        $value = $this->readYml(self::PATH);
        $this->get('translator')->setlocale($value['locale']);
        $install = new Install();
        $form = $this->createForm(new InstallType(), $install);
        $request = $this->get('request');

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $postData = $request->request->get('install_form');
                $db = new \PDO('mysql:host=' . $postData['dbHost'], $postData['dbUser'], $postData['dbPassword']);

                if ($db) {
                    $query = "
                        SELECT SCHEMA_NAME
                        FROM INFORMATION_SCHEMA.SCHEMATA
                        WHERE SCHEMA_NAME = '{$postData['dbName']}'
                    ";
                    $count = $db->query($query)->fetch();

                    $exist = $request->request->all();
                    if (!is_null($count['0']) && !isset($exist['exist'])) {
                        $this->get('session')->setFlash('warning', 'La base de donnée existe deja');

                        return $this->render(
                            'ClarolineCoreBundle:Install:checkupDb.html.twig',
                            array(
                                'form' => $form->createView(),
                                'exist' => 1,
                                'version' => $value['locale']
                            )
                        );
                    }
                    if ($this->putInYml($postData, $form->getName(), 'install_form') == 1) {
                        return $this->showAdminForm();
                    } else {
                        $this->get('session')->setFlash('error', 'Erreur lors de l ecriture des données');
                    }
                } else {
                    $this->get('session')->setFlash(
                        'error',
                        'Impossible de creer la base de donnee verifier vos identifiants'
                    );

                    return $this->showDbFormAction();
                }
            } else {
                $this->get('session')->setFlash('error', 'Veuillez completer correctement le formulaire');

                return $this->render(
                    'ClarolineCoreBundle:Install:checkupDb.html.twig',
                    array('form' => $form->createView())
                );
            }
        }
    }

    /**
    *@Route(
    *   "/AdminForm",
    *   name="claro_showAdminForm"
    *)
    *
    */
    public function showAdminForm()
    {
        $value = $this->readYml(self::PATH);
        $this->get('translator')->setlocale($value['locale']);
        $user = new User();
        $form = $this->createForm(new AdminType, $user);

        return $this->render(
            'ClarolineCoreBundle:Install:checkAdmin.html.twig',
            array(
                'version' => $value['locale'],
                'form' => $form->createView())
        );
    }
    /**
     * @Route(
     *     "/step_check3",
     *     name="claro_checkAdminForm"
     * )
     * @Method("POST")
     */
    public function checkAdminFormAction()
    {
        $value = $this->readYml(self::PATH);
        $this->get('translator')->setlocale($value['locale']);
        $user = new User();
        $request = $this->get('request');
        $form = $this->createForm(new AdminType, $user);
        $postData = $request->request->get('admin_form');
        $error = $this->validateForm($postData);

        if (strcmp($postData['plainPassword']['first'], $postData['plainPassword']['second']) == 0) {
            if ($request->getMethod() == 'POST') {
                if (count($error) <= 0) {
                    if ($this->putInYml($postData)) {
                        $this->createParametersYml();
                        $db = $this->readYml(self::PATH);
                        $this->get('cache_warmer')->warmUp($this->container->getParameter('kernel.cache_dir'));

                        return $this->render('ClarolineCoreBundle:Install:execute.html.twig', array('value' => $db));
                    }
                } else {
                    foreach ($error as $i => $message) {
                        $this->get('session')->setFlash($i, $message);
                    }

                    return $this->render(
                        'ClarolineCoreBundle:Install:checkAdmin.html.twig',
                        array('form' => $form->createView())
                    );
                }
            }
        } else {
            $this->get('session')->setFlash('error', 'Les mot de passes ne correspondent pas');

            return $this->render(
                'ClarolineCoreBundle:Install:checkAdmin.html.twig',
                array(
                    'form' => $form->createView()
                )
            );
        }
    }

    /**
     * @Route(
     *     "/summary",
     *     name="claro_summary"
     * )
     */
    public function summaryShowAction()
    {
        $value = $this->readYml(self::PATH);
        $this->get('translator')->setlocale($value['locale']);

        return $this->render(
            'ClarolineCoreBundle:Install:execute.html.twig',
            array('value' => $value)
        );
    }

    /**
     * @Route(
     *     "/execute",
     *     name="claro_execute"
     * )
     * @Method("POST")
     */
    public function executeAction()
    {
        $db = $this->readYml(self::PATH);
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = array(
            'dbname' => '',
            'user' => $db['dbUser'],
            'password' => $db['dbPassword'],
            'host' => $db['dbHost'],
            'driver' => $db['dbDriver'],
        );

        $tmpConnection = DriverManager::getConnection($connectionParams, $config);

        $error = false;

        try {
            $tmpConnection->getSchemaManager()->dropAndCreateDatabase($db['dbName']);
        } catch (\Exception $e) {
            echo $e->getMessage();
            $error = true;
        }

        $tmpConnection->close();
        $manager = $this->get('claroline.install.core_installer');
        $manager->install();

        $this->createAcl();
        $this->createRole();

        $user = new User();
        $user->setLastName($db['lastName']);
        $user->setFirstName($db['firstName']);
        $user->setUsername($db['username']);
        $user->setPlainPassword($db['plainPassword']['first']);

        $em = $this->get('doctrine.orm.entity_manager');

        $roleRepo = $em->getRepository('ClarolineCoreBundle:Role');
        $adminRole = $roleRepo->findOneByName(PlatformRoles::ADMIN);
        $user->addRole($adminRole);
        $user = $this->get('claroline.user.creator')->create($user);
        $em->persist($user);
        $em->flush();

        if (!unlink(self::PATH)) {
            $this->get('session')->setFlash(
                'erreur',
                'Le fichier n\'a pas ete supprimé, veuiller le supprimmer à la main'
            );
        }

        return $this->render('ClarolineCoreBundle:Install:sucess.html.twig');
    }

    /**
     * @Route(
     *     "/sucess",
     *     name="claro_sucess"
     * )
     * @Method("POST")
     */
    public function successAction()
    {

        return $this->render(
            'ClarolineCoreBundle:Install:sucess.html.twig'
        );
    }

    /**
     * @param $array the array to put in the yml file
     */
    private function putInYml(array $array)
    {
        $parser = new Parser();

        try {
            $value = $parser->parse(file_get_contents(self::PATH));
        } catch (ParseException $e) {
            echo "Impossible d'ouvrir le fichier install.yml " . $e->getMessage();
        }

        if (!empty($value)) {
            //  Check if the array is not send a second time by hiting refresh
            // if the array keys are different then is ok
            $key = array_keys($value);
            if (!array_key_exists($key[0], $array)) {
                $result = array_merge($value, $array);

                try {
                    $dumper = new Dumper();
                    $yaml = $dumper->dump($result, 1);
                    file_put_contents(self::PATH, $yaml);
                } catch (DumpException $e) {
                    echo "probleme lors de la creation du fichier de configuration parameters.yml
                        vérifier vos droits en écriture" . $e->getMessage();
                }
            }

            return 1;
        } else {
            $dumper = new Dumper();
            $yaml = $dumper->dump($array, 1);
            file_put_contents(self::PATH, $yaml);
        }

        return 1;
    }

    private function readYml($string)
    {
        try {
            $parser = new Parser();
            $value = $parser->parse(file_get_contents($string));
        } catch (ParseException $e) {
            echo "Impossible d'ouvrir le fichier install.yml " . $e->getMessage();
        }

        return($value);
    }

    private function createParametersYml()
    {
        $fromFile = $this->readYml(self::PATH);
        $parameters = array(
            'parameters' => array(
                'database_driver' => $fromFile['dbDriver'],
                'database_host' => $fromFile['dbHost'],
                'database_port' => null,
                'database_name' => $fromFile['dbName'],
                'database_user' => $fromFile['dbUser'],
                'database_password' => $fromFile['dbPassword'],
                'test_database_driver' => $fromFile['dbDriver'],
                'test_database_host' => $fromFile['dbHost'],
                'test_database_port' => null,
                'test_database_name' => $fromFile['dbName'],
                'test_database_user' => $fromFile['dbUser'],
                'test_database_password' => $fromFile['dbPassword'],
                'mailer_transport' => 'smtp',
                'mailer_host' => 'localhost',
                'mailer_user' => null,
                'mailer_password' => null,
                'mailer_encryption' => null,
                'mailer_auth_mode' => null,
                'locale' => $fromFile['locale'],
                'secret' => 'ThisTokenIsNotSoSecretChangeIt')
        );

        try {
            $dumper = new Dumper();
            $yaml = $dumper->dump($parameters, 2);
            file_put_contents(self::FINAL_PATH, $yaml);
        } catch (DumpException $e) {
            echo "probleme lors de la creation du fichier de configuration parameters.yml
                 vérifier vos droits en écriture" . $e->getMessage();
        }

        return 1;
    }

    private function validateForm(array $input)
    {
        $errors = array();
        $keys = array_keys($input);

        foreach ($input as $index => $value) {
            if (!empty($value)) {
                if (!is_array($value)) {
                    if (strlen($value) < 3) {
                        $errors[] = ' Le champs ' . $index . ' doit être plus grand que 3 caractères';
                    }
                } else {
                    $errors = $this->validateForm($value);
                }
            } else {
                $errors[] = $keys[$index] . ' Ce champs ' . $index . ' ne peut pas être vide';
            }
        }

        return $errors;
    }

    private function createRole()
    {
        $path = __DIR__ . '/../DataFixtures/';
        $doctrine = $this->get('doctrine');
        $em = $doctrine->getManager();
        $loader = new DataFixturesLoader($this->container);
        $loader->loadFromDirectory($path);

        $fixtures = $loader->getFixtures();

        if (!$fixtures) {
            throw new InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- " . implode("\n- ", $path))
            );
        }

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($fixtures, true);
    }

    private function createAcl()
    {
        $connection = $this->get('security.acl.dbal.connection');
        $schema = $this->get('security.acl.dbal.schema');

        try {
            $schema->addToSchema($connection->getSchemaManager()->createSchema());
        } catch (SchemaException $e) {
            echo "Aborting: " . $e->getMessage();

            return 1;
        }

        foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->exec($sql);
        }
    }

}