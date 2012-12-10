<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Install;
use Claroline\Corebundle\Form\InstallType;
use Claroline\Corebundle\Form\AdminType;
use Claroline\Corebundle\Form\BaseProfileType;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Exception\DumpException;
use Claroline\CoreBundle\Tests\DataFixtures\LoadPlatformRolesData;
use Doctrine\Common\DataFixtures\ReferenceRepository;

class InstallController extends Controller
{
    const PATH ='../app/config/local/install.yml';

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
                // var_dump($request->request->get('install_form'));
                // $postData->setDbPassword(is_null($postData['dbPassword'])) ? "" : $postData['dbPassword'];

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

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $postData = $request->request->get('admin_form');
                if ($this->putInYml($postData, $form->getName(), 'admin_form')) {
                    return $this->summaryShowAction();
                    //return $this->render('ClarolineCoreBundle:Install:execute.html.twig');
                }
            } else {
                $this->get('session')->setFlash('erreure', 'Erreure lors de l ecriture des données');
                return $this->render('ClarolineCoreBundle:Install:checkAdmin.html.twig', array('form' => $form->createView(),));
            }
        }
    }

    public function summaryAction()
    {
        $yaml = new Parser();
        try {
            $value = $yaml->parse(file_get_contents(self::PATH));
        } catch (ParseException $e) {
            echo "Impossible d'ouvrir le fichier parameters.yml " . $e->getMessage();
        }
        return $this->render('ClarolineCoreBundle:Install:execute.html.twig', array('value' => $value));
    }

    public function executeAction()
    {

        try {
            $yaml = new Parser();
            $value = $yaml->parse(file_get_contents(self::PATH));
        } catch (ParseException $e) {
            echo "Impossible d'ouvrir le fichier parameters.yml " . $e->getMessage();
        }

        try {
            $dbLink = new \PDO('mysql:host=' . $value['dbHost'] . ';', $value['dbUser'], $value['dbPassword']);
            $req = $dbLink->prepare('CREATE DATABASE IF NOT EXISTS ' . $value['dbName']);
            $req->execute();
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }

        $user = new User();
        $user->setLastName($value['0']);
        $user->setFirstName($value['1']);
        $user->setUsername($value['2']);
        $user->setPassword($value['first']['0']);
        $user->setEmail($value['4']);

        $fixture = new LoadPlatformRolesData();

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $referenceRepo = new ReferenceRepository($em);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->load($em);

        $roleRepo = $em->getRepository('Claroline\CoreBundle\Entity\Role');
        $adminRole = $roleRepo->findOneByName(PlatformRoles::ADMIN);
        $user->addRole($adminRole);
    }

    public function putInYml($array, $form, $from)
    {
        /*
         * @$array the array to put in the yml file
         * @$form who from where the form is calling
         * $from from where the methode is calling
         */

        if (strcmp($from, $form) == 0) {

            $parser = new Parser();
            try {
                $value = $parser->parse(file_get_contents(self::PATH));
            } catch (ParseException $e) {
                echo "Impossible d'ouvrir le fichier install.yml " . $e->getMessage();
            }
            if (!empty($value)) {
                //$diff=array_diff($value, $array);
                // var_dump($diff);
                if (count($value) < 6) {
                    foreach ($array as $data) {
                        array_push($value, $data);
                    }
                    try {
                        $dumper = new Dumper();
                        $yaml = $dumper->dump($value, 1);
                        file_put_contents(self::PATH, $yaml);
                    } catch (DumpException $e) {
                        echo "probleme lors de la creation du fichier de configuration parameters.yml
                 vérifier vos droits en écriture" . $e->getMessage();
                    }
                    return 1;
                }
            } else {
                $dumper = new Dumper();
                $yaml = $dumper->dump($array, 1);
                file_put_contents(self::PATH, $yaml);
            }
            
            return 1;
        }
        else
            echo"do nothing";
    }

    public function successAction()
    {
        
    }

}

?>
