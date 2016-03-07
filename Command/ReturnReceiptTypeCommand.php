<?php
namespace Innova\CollecticielBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

use Innova\CollecticielBundle\Entity\ReturnReceiptType;

class ReturnReceiptTypeCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('claroline:fixtures:load')
            ->setDescription('Load needed ReturnReceipt datas')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
            $start = time();
            $em = $this->getContainer()->get('doctrine')->getEntityManager('default');
//             $em = $this->getDoctrine()->getManager();
// //            $repository = $em->getRepository('InnovaCollecticielBundle:Criterion');

//             /* RETURN RECEIPT TYPE */
            $returnreceipttypes = array(
                array("0", "NO RETURN RECEIPT"),
                array("1", "DOUBLOON"), 
                array("2", "DOCUMENT RECEIVED"),
                array("3", "DOCUMENT UNREADABLE"),
                array("4", "INCOMPLETE DOCUMENT"),
                array("5", "ERROR DOCUMENT"),
                array("6", "INCOMPLETE DOCUMENT"),
                )
            ;
            foreach ($returnreceipttypes as $returnreceipttype) {
            $output->writeln($returnreceipttype[0]);
                if (!$returnreceipttype = $em->getRepository('InnovaCollecticielBundle:ReturnReceiptType')->findById($returnreceipttype[0])) {

            $output->writeln("ici");
            $output->writeln($returnreceipttype[1]);
            $output->writeln("--");

                    $returnReceiptType = new ReturnReceiptType();

//                    $returnReceiptType->setName($returnreceipttype[0]);
                    $returnReceiptType->setTypeName($returnreceipttype[1]);
                    $em->persist($returnReceiptType);
                    $output->writeln("Add new Return Receipt Type (".$returnreceipttype[1].").");
                }
            }
            $em->flush();

            $now = time();
            $duration = $now - $start;

            $output->writeln("Fixtures exécutées en ".$duration." sec.");
    }
}
