<?php

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\AppBundle\Logger\ConsoleLogger;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanResourceTypeDatabaseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:clean:resource-type')
            ->setDescription('Remove unused resource types');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);

        $types = [
          'claroline_survey',
          'activity',
          'innova_audio_recorder',
          'innova_video_recorder',
          'innova_media_resource',
          'innova_collecticiel',
        ];

        $databaseManager = $this->getContainer()->get('claroline.manager.database_manager');
        $databaseManager->setLogger($consoleLogger);

        $databaseManager->dropTables([
          'media_resource_region_config',
          'media_resource_region',
          'media_resource_options',
          'media_resource',
          'media_resource_media',
          'media_resource_help_text',
          'media_resource_help_link',
        ], true);

        $this->removeResources($types, $consoleLogger);

        $databaseManager->dropTables([
             //surveys
             'claro_survey_multiple_choice_question_answer',
             'claro_survey_open_ended_question_answer',
             'claro_survey_question_answer',
             'claro_survey_simple_text_question_answer',
             'claro_survey_answer',
             'claro_survey_choice',
             'claro_survey_multiple_choice_question',
             'claro_survey_question',
             'claro_survey_question_model',
             'claro_survey_resource',
             'claro_survey_question_relation',
             //activities
             'claro_activity_parameters',
             'claro_activity_rule',
             'claro_activity_rule_action',
             'claro_activity_evaluation',
             'claro_activity_past_evaluation',
             //audio recorder
             'innova_audio_recorder_configuration',
             //video_recorder
             'innova_video_recorder_configuration',
         ], true);

        $databaseManager->dropTables([
              //collecticiel
              'innova_collecticielbundle_choice_criteria',
              'innova_collecticielbundle_choice_notation',
              'innova_collecticielbundle_comment',
              'innova_collecticielbundle_comment_read',
              'innova_collecticielbundle_correction',
              'innova_collecticielbundle_criterion',
              'innova_collecticielbundle_document',
              'innova_collecticielbundle_drop',
              'innova_collecticielbundle_dropzone',
              'innova_collecticielbundle_grade',
              'innova_collecticielbundle_grading_criteria',
              'innova_collecticielbundle_grading_notation',
              'innova_collecticielbundle_grading_scale',
              'innova_collecticielbundle_notation',
              'innova_collecticielbundle_return_receipt',
              'innova_collecticielbundle_return_receipt_type',
          ], true);
    }

    public function removeResources(array $types, $consoleLogger)
    {
        $databaseManager = $this->getContainer()->get('claroline.manager.database_manager');
        $om = $this->getContainer()->get('Claroline\AppBundle\Persistence\ObjectManager');
        $databaseManager->setLogger($consoleLogger);

        foreach ($types as $type) {
            $batch = uniqid();
            $consoleLogger->info('Backup old nodes for type '.$type);
            $databaseManager->backupRows(ResourceNode::class, ['resourceType' => $type], 'claro_node_'.$type, $batch);
            $databaseManager->backupRows(ResourceRights::class, ['resourceType' => $type], 'claro_rights_'.$type, $batch);
            $typeEntity = $om->getRepository(ResourceType::class)->findOneByName($type);
            $nodes = $om->getRepository(ResourceNode::class)->findBy(['resourceType' => $typeEntity]);
            $manager = $this->getContainer()->get('claroline.manager.resource_manager');
            $total = count($nodes);
            $consoleLogger->info('Ready to remove '.$total.' '.$type);
            $i = 0;

            foreach ($nodes as $node) {
                ++$i;
                $consoleLogger->info('Removing '.$type.' '.$i.'/'.$total);
                $manager->delete($node, true);
            }

            $entity = $om->getRepository(ResourceType::class)->findOneByName($type);

            if ($entity) {
                $consoleLogger->info('Backup old resourcreType '.$type);
                $databaseManager->backupRows(ResourceType::class, ['name' => $type], 'claro_resource_type_'.$type, $batch);
                $consoleLogger->info('Removing type '.$type);
                $om->remove($entity);
                $om->flush();
            }

            $om->flush();
        }
    }
}
