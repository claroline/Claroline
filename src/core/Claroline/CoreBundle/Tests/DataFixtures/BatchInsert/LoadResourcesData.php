<?

namespace Claroline\CoreBundle\Tests\DataFixtures\BatchInsert;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

/**
 * Loads a large amount of workspace.
 * This fixture assume the user JohnDoe (admin) already exists.
 */
class LoadResourcesData extends LoggableFixture implements ContainerAwareInterface
{
    private $container;
    private $numberFiles;
    private $numberDirectory;
    private $numberRoots;
    private $depth;
    private $user;
    private $suffixName;
    private $totalResources;


    public function __construct($depth, $numberFiles, $numberDirectory, $numberRoots)
    {
        $this->numberDirectory = $numberDirectory;
        $this->numberFiles = $numberFiles;
        $this->numberRoots = $numberRoots;
        $this->depth = $depth;
        $this->totalResources = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        if ($this->numberDirectory <= 1) {
            $numTot = $this->numberFiles;
        } else {
            $numTot = ((1 - pow($this->numberDirectory, $this->depth + 1)) / (1 - $this->numberDirectory)) - 1;
        }

        $this->log("Number of directories that will be generated per workspace: ". $numTot);
        $this->log("Number of files that will be generated per workspace: ". $numTot * $this->numberFiles);
        $this->log("Number of filled workspaces: ". $this->numberRoots);
        $this->log("Total resources: ". $this->numberRoots * ($numTot * $this->numberFiles + $numTot));

        $this->user = $this->findJohnDoe($manager);

        $count = $manager->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->count();
        $this->suffixName = $count;
        $count = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->count();
        $maxWsId = $count;

        $start = time();

        for ($i = 0; $i < $this->numberRoots; $i++) {
            $ws = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
                ->find($maxWsId);
            $this->userRootDirectory = $manager->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                 ->findWorkspaceRoot($ws);
            $this->generateItems(
                $manager,
                $this->depth,
                0,
                $this->numberDirectory,
                $this->numberFiles,
                $this->userRootDirectory
            );
            $maxWsId--;
        }

        $end = time();
        $duration = $this->container->get('claroline.utilities.misc')->timeElapsed($end - $start);
        $this->log("Time elapsed for the demo creation: " . $duration);

        return $duration;

    }

    private function generateItems(EntityManager $em, $maxDepth, $curDepth, $directoryCount, $fileCount, $parent)
    {
        $curDepth++;

        for ($j = 0; $j < $directoryCount; $j++) {
            $this->log('Total resources created: '.$this->totalResources);
            $dir = $this->addDirectory($parent, $this->user);

            for ($k = 0; $k < $fileCount; $k++) {
                $this->addFile($parent, $this->user);
            }

            if ($curDepth < $maxDepth) {
                $this->generateItems($em, $maxDepth, $curDepth, $directoryCount, $fileCount, $dir);

                if ($curDepth == 1) {
                    $this->log(" [UOW size: " . $em->getUnitOfWork()->size() . "]");
                    // Clear the EntityManager (EM) to free memory and speed all EM operations.
                    // We may clear the EM only when coming back at level 1 else we have
                    // problems with entities needed in the hierarchy.
                    $em->flush();
                    $em->clear();
                    $this->log(" [UOW size: " . $em->getUnitOfWork()->size() . "]");
                    // Re-attach all needed entities else we have problems later.
                    $this->userRootDirectory = $em->merge($this->userRootDirectory);
                    $this->user = $em->merge($this->user);
                    $parent = $em->merge($parent);
                }
            }
        }

        $this->log(" [UOW size: " . $em->getUnitOfWork()->size() . "]");
    }

    private function addDirectory(Directory $parent, User $user)
    {
        $this->suffixName++;
        $name = 'dir_'.$this->suffixName;
        $dir = new Directory();
        $dir->setName($name);
        $this->log('create '.$name);
        $dir = $this->container->get('claroline.resource.manager')
            ->create($dir, $parent->getId(), 'directory', $user, null, false, true);
        $this->totalResources++;

        return $dir;
    }

    private function addFile(Directory $parent, User $user)
    {
        $this->suffixName++;
        $name = 'file_'.$this->suffixName.'.txt';
        $file = new File();
        $file->setName($name);
        $hashName = $this->container->get('claroline.resource.utilities')->generateGuid();
        $file->setHashName($hashName);
        $file->setMimeType('text/plain');
        $file->setSize(0);
        $this->log('create '.$name);
        $file = $this->container->get('claroline.resource.manager')
            ->create($file, $parent->getId(), 'file', $user, null, false, false);
        $this->totalResources++;

        return $file;
    }

    private function findJohnDoe(ObjectManager $manager)
    {
        $query = $manager->createQuery("SELECT u FROM Claroline\CoreBundle\Entity\User u where u.username = 'JohnDoe'");
        $query->setFetchMode("MyProject\User", "address", "EXTRA_LAZY");

        return $query->getSingleResult();
    }
}