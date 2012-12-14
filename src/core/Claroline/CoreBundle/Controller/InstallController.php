<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Install;
use Claroline\Corebundle\Form\InstallType;
use Claroline\Corebundle\Form\AdminType;
use Claroline\Corebundle\Form\BaseProfileType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Exception\DumpException;
use Claroline\CoreBundle\Tests\DataFixtures\LoadPlatformRolesData;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
class InstallController extends Controller
{
    const PATH ='../app/config/local/install.yml';
    const FINAL_PATH = '../app/config/local/parameters.yml';

    public function indexAction()
    {
        $phpversion = phpversion();
        $mysqlversion = mysqli_get_client_info();
        if (chmod('../app/config/local', 0600)) {
            if (chmod('../files', 0600))
                if (chmod('../web', 0600)) {
                    $ourFileHandle = fopen(self::PATH, 'w');
                    fclose($ourFileHandle);
                    $permission = 'ok';
                }

                else
                    $permission .=' [web]';
            else
                $permission .= '[files]';
        }
        else
            $permission = 'Verifier les permissions en écritures et lecture du dossier [app/config/local]';

        $config = array(
            'php' => $phpversion,
            'mysql' => $mysqlversion,
            'chmod' => $permission
        );

        $formBuilder = $this->createFormBuilder();
        $form = $formBuilder->getForm();
        return $this->render('ClarolineCoreBundle:Install:index.html.twig', array('version' => $config,));
    }

    public function showDbFormAction()
    {
        $install = new Install();
        $form = $this->createForm(new InstallType, $install);

        return $this->render('ClarolineCoreBundle:Install:checkupDb.html.twig', array('form' => $form->createView(),));
    }

    public function checkDbFormAction()
    {
        $install = new Install();
        $form = $this->createForm(new InstallType, $install);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {

            $form->bindRequest($request);
            if ($form->isValid()) {
                $postData = $request->request->get('install_form');
       
                try {
                    $dbLink = new \PDO('mysql:host=' . $postData['dbHost'] . ';', $postData['dbUser'], $postData['dbPassword']);
                } catch (PDOException $e) {
                    echo 'Connection failed: ' . $e->getMessage();

                    $this->get('session')->setFlash('erreure', 'Erreure lors de l ecriture des données');
                    return $this->showDbFormAction();
                }
                if ($this->putInYml($postData, $form->getName(), 'install_form') == 1) {
                    unset($form);
                    unset($formBuilder);
                    return $this->showAdminFormAction();
                }
                else
                    $this->get('session')->setFlash('erreure', 'Erreure lors de l ecriture des données');
                echo" yaml failed";
            }
        }
    }

    public function showAdminFormAction()
    {

        $user = new User();
        $form = $this->createForm(new AdminType, $user);
        return $this->render('ClarolineCoreBundle:Install:checkAdmin.html.twig', array('form' => $form->createView(),));
    }

    public function checkAdminFormAction()
    {
        $user = new User();
        $request = $this->get('request');
        $form = $this->createForm(new AdminType, $user);
        
       $postData = $request->request->get('admin_form');
      $error = $this->validateForm($postData);
       if(strcmp($postData['plainPassword']['first'],$postData['plainPassword']['second'])== 0)
          if ($request->getMethod() == 'POST') {     
            if(count($error) <= 0)
          {
                if ($this->putInYml($postData)) {
                   $this->createParametersYml();
                    $db = $this->readYml(self::PATH); 
                     $this->get('cache_warmer')->warmUp($this->container->getParameter('kernel.cache_dir'));
                     return $this->render('ClarolineCoreBundle:Install:execute.html.twig', array('value' => $db));
                       
                }
            } else {
                foreach ($error as $i => $message) {
                      $this->get('session')->setFlash($i,$message);
                }
              
                return $this->render('ClarolineCoreBundle:Install:checkAdmin.html.twig', array('form' => $form->createView(),));
            }
        }
    }

    public function summaryShowAction()
    {
         $value = $this->readYml(self::PATH); 
        return $this->render('ClarolineCoreBundle:Install:execute.html.twig', array('value' => $value));
    }

    public function executeAction()
    {

    
        $db= $this->readYml(self::PATH);
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = array(
        'dbname' =>'',
        'user' => $db['dbUser'],
        'password' => $db['dbPassword'],
        'host' => $db['dbHost'],
        'driver' =>'pdo_mysql',
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
        //$user->setEmail('test@claroline.net');
       
        $em = $this->get('doctrine.orm.entity_manager');

        $roleRepo = $em->getRepository('Claroline\CoreBundle\Entity\Role');
        $adminRole = $roleRepo->findOneByName(PlatformRoles::ADMIN);
        $user->addRole($adminRole);
        $user = $this->get('claroline.user.creator')->create($user); 
        $em->persist($user);
        $em->flush();
       if(!unlink(self::PATH))
       {
           $this->get('session')->setFlash('erreur','Le fichier n\'a pas ete supprimé, veuiller le supprimmer à la main');
       }
          return $this->render('ClarolineCoreBundle:Install:sucess.html.twig');
    }

    public function putInYml($array)
    {
        /*
         * @$array the array to put in the yml file
         */

        $keys = array();

        $parser = new Parser();
        try {
            $value = $parser->parse(file_get_contents(self::PATH));
        } catch (ParseException $e) {
            echo "Impossible d'ouvrir le fichier install.yml " . $e->getMessage();
        }
        if (!empty($value)) {

            if ((count($value) != count($array)) || (count($value) < (count($array)))) 
                {
                    $result = array_merge($value,$array);
               
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
            }
         else {
            $dumper = new Dumper();
            $yaml = $dumper->dump($array, 1);
            file_put_contents(self::PATH, $yaml);
        }

        return 1;
    }
     function readYml($string)
        {
           try {
                $parser = new Parser();
                $value = $parser->parse(file_get_contents($string));
            } catch (ParseException $e) {
                echo "Impossible d'ouvrir le fichier install.yml " . $e->getMessage();
            }
            return($value);
        }
   public function createParametersYml()
    {
        $fromFile = $this->readYml(self::PATH);
        $parameters = array(
            'parameters' => array(
                'database_driver' => 'pdo_mysql',
                'database_host' => $fromFile['dbHost'],
                'database_port' => null,
                'database_name' => $fromFile['dbName'],
                'database_user' => $fromFile['dbUser'],
                'database_password' => $fromFile['dbPassword'],
                'mailer_transport' => 'smtp',
                'mailer_host' => 'localhost',
                'mailer_user' => null,
                'mailer_password' => null,
                'mailer_encryption' => null,
                'mailer_auth_mode' =>null,
                'locale' => 'fr',
                'secret' => 'ThisTokenIsNotSoSecretChangeIt')
        );
        
        try {
            /*  $parser = new Symfony\Component\Yaml\Parser();
              $parameters= $parser->parse($parameters);
              var_dump($parameters); */
            $dumper = new Dumper();
            $yaml = $dumper->dump($parameters, 2);
            file_put_contents(self::FINAL_PATH, $yaml);
        } catch (DumpException $e) {
            echo "probleme lors de la creation du fichier de configuration parameters.yml
                 vÃ©rifier vos droits en Ã©criture" . $e->getMessage();
        }
        return 1;
    }
    
    public function validateForm(array $input)
    {
        $error = array();
        $keys =  array_keys($input);
        foreach ($input as $index => $value) {
            if (!empty($value)) {
                if (!is_array($value)) {
                    if (strlen($value) >= 3) {
                        
                    } else {
                      
                        $error[] = ' Le champs '.$index.' doit etre plus grand que 3 caractères';
                    }
                } 
                else
                   $error = $this->validateForm ($value);
                }
                else {
                    $error[] = $keys[$index] . ' Ce champs '.$index.' ne peut pas être vide';
            }
        }
        return($error);
    }
    private function createRole()
    {
        
        $path = __DIR__.'/../DataFixtures/';
        $doctrine = $this->get('doctrine');
        $em = $doctrine->getManager();
        $loader = new DataFixturesLoader($this->container);
        $loader->loadFromDirectory($path);
       
        $fixtures = $loader->getFixtures();
        if (!$fixtures) {
            throw new InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- " . implode("\n- ", $paths))
            );
        }
        $purger = new ORMPurger($em);
      //  $purger->setPurgeMode($input->getOption('purge-with-truncate') ? ORMPurger::PURGE_MODE_TRUNCATE : ORMPurger::PURGE_MODE_DELETE);
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

    public function successAction()
    {
        return $this->render('ClarolineCoreBundle:Install:index.html.twig', array('version' => $config,));
    }

}


?>
