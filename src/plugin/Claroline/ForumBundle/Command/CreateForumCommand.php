<?php

namespace Claroline\ForumBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Message;

class CreateForumCommand extends ContainerAwareCommand
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('claroline:forum:create')
            ->setDescription('Creates a forum.');
        $this->setDefinition(array(
            new InputArgument('username', InputArgument::REQUIRED, 'The username'),
            new InputArgument('name', InputArgument::REQUIRED, 'The forum name'),
            new InputArgument('subjectsAmount', InputArgument::REQUIRED, 'The number of subjects'),
            new InputArgument('messagesAmount', InputArgument::REQUIRED, 'The number of messages'),
        ));
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'username' => 'username',
            'name' => 'name',
            'subjectsAmount' => 'subjectsAmount',
            'messagesAmount' => 'messagesAmount'
        );

        foreach ($params as $argument => $argumentName) {
            if (!$input->getArgument($argument)) {
                $input->setArgument(
                    $argument, $this->askArgument($output, $argumentName)
                );
            }
        }
    }

    protected function askArgument(OutputInterface $output, $argumentName)
    {
        $argument = $this->getHelper('dialog')->askAndValidate(
            $output, "Enter the {$argumentName}: ", function($argument) {
                if (empty($argument)) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $creator = $this->getContainer()->get('claroline.resource.manager');
        $subjectsAmount = $input->getArgument('subjectsAmount');
        $messagesAmount = $input->getArgument('messagesAmount');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('ClarolineCoreBundle:User')->findOneBy(array('username' => $input->getArgument('username')));
        $root = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->findOneBy(array('parent' => null, 'workspace' => $user->getPersonalWorkspace()->getId()));
        $collaborators = $user->getPersonalWorkspace()->getCollaboratorRole()->getUsers();
        $maxOffset = count($collaborators);
        $maxOffset--;
        $forum = new Forum();
        $forum->setName($input->getArgument('name'));
        $em->persist($forum);
        $em->flush();
        $forumInstance = $creator->create($forum, $root->getId(), 'Forum', true, null, $user);
        echo "forum {$forumInstance->getName()} created\n";

        for ($i = 0; $i < $subjectsAmount; $i++) {
            $title = $this->generateLipsum(5);
            $user = $collaborators[rand(0, $maxOffset)];
            $subject = new Subject();
            $subject->setName($title);
            $subject->setTitle($title);
            $subject->setCreator($user);
            $subject->setForum($forumInstance->getResource());
            $em->persist($subject);
            echo "subject $title created\n";
            $subjectInstance = $creator->create($subject, $forumInstance->getId(), 'Subject', true, null, $user);

            for ($j = 0; $j < $messagesAmount; $j++) {
                $sender = $collaborators[rand(0, $maxOffset)];
                $message = new Message();
                $message->setName('tmp');
                $message->setCreator($sender);
                $message->setContent($this->generateLipsum(150, true));
                $message->setSubject($subject);
                $creator->create($message, $subjectInstance->getId(), 'Message', true, null, $sender);
            }
        }

        $em->flush();
    }

    private function getArrayLipsum()
    {
        $lipsum = array('lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit', 'curabitur', 'vel', 'hendrerit', 'libero', 'eleifend',
            'blandit', 'nunc', 'ornare', 'odio', 'ut', 'orci', 'gravida', 'imperdiet', 'nullam', 'purus', 'lacinia', 'a',
            'pretium', 'quis', 'congue', 'praesent', 'sagittis', 'laoreet', 'auctor', 'mauris', 'non', 'velit', 'eros', 'dictum', 'proin', 'accumsan',
            'sapien', 'nec', 'massa', 'volutpat', 'venenatis', 'sed', 'eu', 'molestie', 'lacus', 'quisque', 'porttitor', 'ligula',
            'dui', 'mollis', 'tempus', 'at', 'magna', 'vestibulum', 'turpis', 'ac', 'diam', 'tincidunt', 'id', 'condimentum', 'enim',
            'sodales', 'in', 'hac', 'habitasse', 'platea', 'dictumst', 'aenean', 'neque', 'fusce', 'augue', 'leo', 'eget', 'semper', 'mattis', 'tortor',
            'scelerisque', 'nulla', 'interdum', 'tellus', 'malesuada', 'rhoncus', 'porta', 'sem', 'aliquet', 'et', 'nam', 'suspendisse',
            'potenti', 'vivamus', 'luctus', 'fringilla', 'erat', 'donec', 'justo', 'vehicula', 'ultricies', 'varius', 'ante',
            'primis', 'faucibus', 'ultrices', 'posuere', 'cubilia', 'curae', 'etiam', 'cursus', 'aliquam', 'quam', 'dapibus',
            'nisl', 'feugiat', 'egestas', 'class', 'aptent', 'taciti', 'sociosqu', 'ad', 'litora', 'torquent', 'per', 'conubia', 'nostra',
            'inceptos', 'himenaeos', 'phasellus', 'nibh', 'pulvinar', 'vitae', 'urna', 'iaculis', 'lobortis', 'nisi', 'viverra',
            'arcu', 'morbi', 'pellentesque', 'metus', 'commodo', 'ut', 'facilisis', 'felis', 'tristique', 'ullamcorper', 'placerat', 'aenean',
            'convallis', 'sollicitudin', 'integer', 'rutrum', 'duis', 'est', 'etiam', 'bibendum', 'donec', 'pharetra', 'vulputate', 'maecenas',
            'mi', 'fermentum', 'consequat', 'suscipit', 'aliquam', 'habitant', 'senectus', 'netus', 'fames', 'quisque', 'euismod',
            'curabitur', 'lectus', 'elementum', 'tempor', 'risus', 'cras');

        return $lipsum;
    }

    /**
     * if nbwords = 0, then it's somewhat random (from 5 to 500)
     *
     * @param integer $nbWords
     * @param boolean $isFullText
     *
     * @return string
     */
    private function generateLipsum($nbWords = 0, $isFullText = false)
    {
        $words = $this->getArrayLipsum();
        $content = '';
        $endPhrase = array('?', '!', '.', '...');
        $loopBeforeEnd = 0;

        if ($nbWords == 0) {
            $nbWords = rand(5, 500);
        }

        for ($i = 0; $i < $nbWords; $i++) {

            if ($loopBeforeEnd == 0) {
                $loopBeforeEnd = rand(3, 15);
            }

            $loopBeforeEnd--;

            if ($isFullText && $loopBeforeEnd == 0) {
                $content.="{$endPhrase[array_rand($endPhrase)]} " . ucfirst($words[array_rand($words)]);
            } else {

                if ($content != '') {
                    $content .= ' ';
                }

                $content.= "{$words[array_rand($words)]}";
            }

            $i++;
        }

        if ($isFullText) {
            $content = ucfirst($content) . '.';
        }

        return $content;
    }

}