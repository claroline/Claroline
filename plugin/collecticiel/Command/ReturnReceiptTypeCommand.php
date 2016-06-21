<?php

namespace Innova\CollecticielBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Innova\CollecticielBundle\Entity\ReturnReceiptType;

class ReturnReceiptTypeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('claroline:fixtures:innova_collecticiel_load')
            ->setDescription('Load needed ReturnReceipt datas for Collecticiel')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = time();
            //$em = $this->getContainer()->get('doctrine')->getEntityManager('default');
            $em = $this->getContainer()->get('claroline.persistence.object_manager');

            /* RETURN RECEIPT TYPE ARRAY */
            $returnreceipttypesArray = array(
                array('0', 'NO RETURN RECEIPT'),
                array('1', 'DOUBLOON'),
                array('2', 'DOCUMENT RECEIVED'),
                array('3', 'DOCUMENT UNREADABLE'),
                array('4', 'INCOMPLETE DOCUMENT'),
                array('5', 'ERROR DOCUMENT'),
                )
            ;

            /* TRAITEMENT */
            foreach ($returnreceipttypesArray as $returnreceipttype) {

                // RECUPERATION DU LIBELLE
                $typeName = $returnreceipttype[1];

                if (!$returnreceipttype = $em->getRepository('InnovaCollecticielBundle:ReturnReceiptType')->find($returnreceipttype[0])) {
                    /* CREATION */
                    $returnReceiptTypeAdd = new ReturnReceiptType();
                    $returnReceiptTypeAdd->setTypeName($typeName);

                    $em->persist($returnReceiptTypeAdd);
                    $output->writeln('Add new Return Receipt Type ('.$returnReceiptTypeAdd->getTypeName().')');
                } else {
                    /* MISE A JOUR */
                    $returnreceipttype->setTypeName($typeName);
                    $em->persist($returnreceipttype);
                    $output->writeln('Update Return Receipt Type ('.$returnreceipttype->getTypeName().')');
                }
            }
        $em->flush();

        $now = time();
        $duration = $now - $start;

        $output->writeln('Fixtures exécutées en '.$duration.' sec.');
    }
}
