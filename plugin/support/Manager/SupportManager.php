<?php

namespace FormaLibre\SupportBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\MessageBundle\Manager\MessageManager;
use FormaLibre\SupportBundle\Entity\Comment;
use FormaLibre\SupportBundle\Entity\Configuration;
use FormaLibre\SupportBundle\Entity\Intervention;
use FormaLibre\SupportBundle\Entity\Status;
use FormaLibre\SupportBundle\Entity\Ticket;
use FormaLibre\SupportBundle\Entity\TicketUser;
use FormaLibre\SupportBundle\Entity\Type;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("formalibre.manager.support_manager")
 */
class SupportManager
{
    private $mailManager;
    private $messageManager;
    private $om;
    private $pagerFactory;
    private $router;
    private $translator;
    private $userManager;

    private $configurationRepo;
    private $statusRepo;
    private $ticketRepo;
    private $ticketUserRepo;
    private $typeRepo;

    /**
     * @DI\InjectParams({
     *     "mailManager"     = @DI\Inject("claroline.manager.mail_manager"),
     *     "messageManager"  = @DI\Inject("claroline.manager.message_manager"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory"),
     *     "router"          = @DI\Inject("router"),
     *     "translator"      = @DI\Inject("translator"),
     *     "userManager"     = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
        MailManager $mailManager,
        MessageManager $messageManager,
        ObjectManager $om,
        PagerFactory $pagerFactory,
        RouterInterface $router,
        TranslatorInterface $translator,
        UserManager $userManager
    ) {
        $this->mailManager = $mailManager;
        $this->messageManager = $messageManager;
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->router = $router;
        $this->translator = $translator;
        $this->userManager = $userManager;

        $this->configurationRepo = $om->getRepository('FormaLibreSupportBundle:Configuration');
        $this->statusRepo = $om->getRepository('FormaLibreSupportBundle:Status');
        $this->ticketRepo = $om->getRepository('FormaLibreSupportBundle:Ticket');
        $this->ticketUserRepo = $om->getRepository('FormaLibreSupportBundle:TicketUser');
        $this->typeRepo = $om->getRepository('FormaLibreSupportBundle:Type');
    }

    public function persistTicket(Ticket $ticket)
    {
        $this->om->persist($ticket);
        $this->om->flush();
    }

    public function deleteTicket(Ticket $ticket)
    {
        $this->om->remove($ticket);
        $this->om->flush();
    }

    public function removeTicket(Ticket $ticket, $type)
    {
        $this->om->startFlushSuite();
        switch ($type) {
            case 'admin':
                $ticket->setAdminActive(false);
                $this->om->persist($ticket);
                $this->deleteTicketUsersByTicket($ticket);
                break;
            case 'user':
                $ticket->setUserActive(false);
                $ticket->setOpen(false);
                $this->om->persist($ticket);
                break;
        }
        if (!$ticket->isAdminActive() && !$ticket->isUserActive()) {
            $this->deleteTicket($ticket);
        }
        $this->om->endFlushSuite();
    }

    public function generateTicketNum(User $user)
    {
        $num = 1;
        $lastNum = $this->ticketRepo->findLastTicketNumByUser($user);

        if (!is_null($lastNum['ticket_num'])) {
            $num = $lastNum['ticket_num'] + 1;
        }

        return $num;
    }

    public function initializeTicket(Ticket $ticket, User $user)
    {
        $this->om->startFlushSuite();
        $ticket->setNum($this->generateTicketNum($user));
        $ticket->setCreationDate(new \DateTime());
        $status = $this->getStatusByCode('NEW');

        if (!empty($status)) {
            $this->createIntervention($ticket, $status, $user);
        }
        $this->persistTicket($ticket);
        $this->om->endFlushSuite();
        $this->sendTicketMail($user, $ticket, 'new_ticket');
    }

    public function closeTicket(Ticket $ticket, User $user)
    {
        $status = $this->getStatusByCode('FA');

        if (!empty($status)) {
            $this->om->startFlushSuite();
            $messageData = [];
            $messageData['oldStatus'] = $ticket->getStatus();
            $messageData['status'] = $status;
            $this->createIntervention($ticket, $status, $user);
            $this->createInterventionComment($user, $ticket, $messageData, Comment::PUBLIC_COMMENT);
            $this->persistTicket($ticket);
            $this->om->endFlushSuite();
        }
    }

    public function persistComment(Comment $comment)
    {
        $this->om->persist($comment);
        $this->om->flush();
    }

    public function deleteComment(Comment $comment)
    {
        $this->om->remove($comment);
        $this->om->flush();
    }

    public function persistType(Type $type)
    {
        $this->om->persist($type);
        $this->om->flush();
    }

    public function deleteType(Type $type)
    {
        $this->om->remove($type);
        $this->om->flush();
    }

    public function persistStatus(Status $status)
    {
        $this->om->persist($status);
        $this->om->flush();
    }

    public function deleteStatus(Status $status)
    {
        $this->om->remove($status);
        $this->om->flush();
    }

    public function persistIntervention(Intervention $intervention)
    {
        $this->om->persist($intervention);
        $this->om->flush();
    }

    public function createIntervention(Ticket $ticket, Status $status, User $user = null)
    {
        $this->om->startFlushSuite();
        $intervention = new Intervention();
        $intervention->setTicket($ticket);
        $intervention->setUser($user);
        $intervention->setStatus($status);
        $intervention->setStartDate(new \DateTime());
        $intervention->setEndDate(new \DateTime());
        $intervention->setDuration(0);
        $this->persistIntervention($intervention);
        $ticket->setStatus($status);
        $ticket->setUserActive(true);
        $this->persistTicket($ticket);
        $this->om->endFlushSuite();

        return $intervention;
    }

    public function reorderStatus(Status $status, $nextStatusId)
    {
        $allStatus = $this->getAllStatus();
        $nextId = intval($nextStatusId);
        $order = 1;
        $updated = false;

        foreach ($allStatus as $oneStatus) {
            if ($oneStatus === $status) {
                continue;
            } elseif ($oneStatus->getId() === $nextId) {
                $status->setOrder($order);
                $updated = true;
                $this->om->persist($status);
                ++$order;
                $oneStatus->setOrder($order);
                $this->om->persist($oneStatus);
                ++$order;
            } else {
                $oneStatus->setOrder($order);
                $this->om->persist($oneStatus);
                ++$order;
            }
        }

        if (!$updated) {
            $status->setOrder($order);
            $this->om->persist($status);
        }
        $this->om->flush();
    }

    public function getConfiguration()
    {
        $configs = $this->configurationRepo->findAll();

        if (count($configs) > 0) {
            $config = $configs[0];
        } else {
            $config = new Configuration();
            $details = [
                'contacts' => [],
                'notify' => [
                    'ticket_internal' => true,
                    'ticket_external' => true,
                    'admin_message_internal' => true,
                    'admin_message_external' => true,
                    'user_message_internal' => true,
                    'user_message_external' => true,
                    'note_internal' => true,
                    'note_external' => true,
                ],
            ];
            $config->setDetails($details);
            $this->persistConfiguration($config);
        }

        return $config;
    }

    public function persistConfiguration(Configuration $config)
    {
        $this->om->persist($config);
        $this->om->flush();
    }

    public function sendTicketMail(User $user, Ticket $ticket, $type = '', Comment $comment = null)
    {
        $config = $this->getConfiguration();
        $errors = [];
        $contactMail = null;
        $mailReceivers = [];
        $messageReceivers = [];
        $extra = [];
        $sendMessage = false;
        $sendMail = false;

        switch ($type) {
            case 'new_ticket':
                $sendMessage = $config->getNotify('ticket_internal');
                $sendMail = $config->getNotify('ticket_external');
                $contactIds = $config->getContacts();
                $contactMail = $ticket->getContactMail();
                $url = $this->router->generate('formalibre_admin_ticket_open', ['ticket' => $ticket->getId()], true);

                if (($sendMessage || $sendMail) && count($contactIds) > 0) {
                    $mailReceivers = $this->userManager->getUsersByIds($contactIds);
                    $messageReceivers = $mailReceivers;
                    $subject = '['.
                        $this->translator->trans('new_ticket', [], 'support').
                        ']['.
                        $user->getFirstName().
                        ' '.
                        $user->getLastName().
                        '] '.
                        $ticket->getTitle();
                    $content = $ticket->getDescription().
                        '<br><br>'.
                        $this->translator->trans('email', [], 'platform').
                        ' : '.
                        $ticket->getContactMail().
                        '<br>'.
                        $this->translator->trans('phone', [], 'platform').
                        ' : '.
                        $ticket->getContactPhone().
                        '<br>'.
                        $this->translator->trans('link', [], 'platform').
                        ' : <a href="'.$url.'">'.$url.'</a><br><br>';
                }
                break;
            case 'ticket_edition':
                $sendMessage = $config->getNotify('ticket_internal');
                $sendMail = $config->getNotify('ticket_external');
                $contactIds = $config->getContacts();
                $contactMail = $ticket->getContactMail();
                $url = $this->router->generate('formalibre_admin_ticket_open', ['ticket' => $ticket->getId()], true);

                if (($sendMessage || $sendMail) && count($contactIds) > 0) {
                    $mailReceivers = $this->userManager->getUsersByIds($contactIds);
                    $messageReceivers = $mailReceivers;
                    $subject = '['.
                        $this->translator->trans('ticket_edition', [], 'support').
                        ']['.
                        $user->getFirstName().
                        ' '.
                        $user->getLastName().
                        '] '.
                        $ticket->getTitle();
                    $content = $ticket->getDescription().
                        '<br><br>'.
                        $this->translator->trans('email', [], 'platform').
                        ' : '.
                        $ticket->getContactMail().
                        '<br>'.
                        $this->translator->trans('phone', [], 'platform').
                        ' : '.
                        $ticket->getContactPhone().
                        '<br>'.
                        $this->translator->trans('link', [], 'platform').
                        ' : <a href="'.$url.'">'.$url.'</a><br><br>';
                }
                break;
            case 'ticket_deletion':
                $sendMessage = $config->getNotify('ticket_internal');
                $sendMail = $config->getNotify('ticket_external');
                $contactIds = $config->getContacts();

                if (($sendMessage || $sendMail) && count($contactIds) > 0) {
                    $mailReceivers = $this->userManager->getUsersByIds($contactIds);
                    $messageReceivers = $mailReceivers;
                    $subject = '['.
                        $this->translator->trans('ticket_deletion', [], 'support').
                        ']['.
                        $user->getFirstName().
                        ' '.
                        $user->getLastName().
                        '] '.
                        $ticket->getTitle();
                    $content = $this->translator->trans('ticket_deletion', [], 'support').
                        '<br><br>';
                }
                break;
            case 'new_admin_comment':
                $sendMessage = $config->getNotify('user_message_internal');
                $sendMail = $config->getNotify('user_message_external');
                $url = $this->router->generate('formalibre_ticket_open', ['ticket' => $ticket->getId()], true);

                if (($sendMessage || $sendMail) && !is_null($comment)) {
                    $extra['to'] = [$ticket->getContactMail()];
                    $messageReceivers = [$ticket->getUser()];
                    $subject = '['.
                        $this->translator->trans('new_ticket_message', [], 'support').
                        '] '.
                        $ticket->getTitle();
                    $content = $comment->getContent().
                        '<br>'.
                        $this->translator->trans('link', [], 'platform').
                        ' : <a href="'.$url.'">'.$url.'</a><br><br>';
                }
                break;
            case 'new_comment':
                $sendMessage = $config->getNotify('admin_message_internal');
                $sendMail = $config->getNotify('admin_message_external');
                $contactMail = $ticket->getContactMail();
                $url = $this->router->generate('formalibre_admin_ticket_open', ['ticket' => $ticket->getId()], true);

                if (($sendMessage || $sendMail) && !is_null($comment)) {
                    $mailReceivers = $this->getStakeholdersByTicket($ticket);
                    $messageReceivers = $mailReceivers;
                    $subject = '['.
                        $this->translator->trans('new_ticket_message', [], 'support').
                        ']['.
                        $user->getFirstName().
                        ' '.
                        $user->getLastName().
                        '] '.
                        $ticket->getTitle();
                    $content = $comment->getContent().
                        '<br><br>'.
                        $this->translator->trans('email', [], 'platform').
                        ' : '.
                        $ticket->getContactMail().
                        '<br>'.
                        $this->translator->trans('phone', [], 'platform').
                        ' : '.
                        $ticket->getContactPhone().
                        '<br>'.
                        $this->translator->trans('link', [], 'platform').
                        ' : <a href="'.$url.'">'.$url.'</a><br><br>';
                }
                break;
            case 'new_internal_note':
                $sendMessage = $config->getNotify('note_internal');
                $sendMail = $config->getNotify('note_external');
                $contactMail = $ticket->getContactMail();
                $url = $this->router->generate('formalibre_admin_ticket_open', ['ticket' => $ticket->getId()], true);

                if (($sendMessage || $sendMail) && !is_null($comment)) {
                    $mailReceivers = $this->getStakeholdersByTicket($ticket, $user);
                    $messageReceivers = $mailReceivers;
                    $subject = '['.
                        $this->translator->trans('new_ticket_note', [], 'support').
                        ']['.
                        $user->getFirstName().
                        ' '.
                        $user->getLastName().
                        '] '.
                        $ticket->getTitle();
                    $content = $comment->getContent().
                        '<br>'.
                        $this->translator->trans('link', [], 'platform').
                        ' : <a href="'.$url.'">'.$url.'</a><br><br>';
                }
                break;
            default:
                break;
        }

        if ($sendMail && (count($mailReceivers) > 0 || (isset($extra['to']) && count($extra['to']) > 0))) {
            try {
                $this->mailManager->send($subject, $content, $mailReceivers, null, $extra, false, $contactMail);
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        if ($sendMessage && count($messageReceivers) > 0) {
            $message = $this->messageManager->create($content, $subject, $messageReceivers, $user);
            $this->messageManager->send($message, true, false);
        }

        return $errors;
    }

    public function generateTicketUser(Ticket $ticket, User $user)
    {
        $ticketUser = $this->ticketUserRepo->findOneBy(['ticket' => $ticket, 'user' => $user]);

        if (empty($ticketUser)) {
            $ticketUser = new TicketUser();
            $ticketUser->setTicket($ticket);
            $ticketUser->setUser($user);
            $this->om->persist($ticketUser);
            $this->om->flush();
        }

        return $ticketUser;
    }

    public function activateTicketUser(Ticket $ticket, User $user)
    {
        $this->om->startFlushSuite();
        $ticketUser = $this->generateTicketUser($ticket, $user);

        if (!$ticketUser->isActive()) {
            $ticketUser->setActive(true);
            $ticketUser->setActivationDate(new \DateTime());
            $this->om->persist($ticketUser);
        }
        $this->om->endFlushSuite();
    }

    public function deactivateTicketUser(TicketUser $ticketUser)
    {
        $ticketUser->setActive(false);
        $ticketUser->setActivationDate(null);
        $this->om->persist($ticketUser);
        $this->om->flush();
    }

    public function deleteTicketUser(Ticket $ticket, User $user)
    {
        $ticketUser = $this->ticketUserRepo->findOneBy(['ticket' => $ticket, 'user' => $user]);

        if (!empty($ticketUser)) {
            $this->om->remove($ticketUser);
            $this->om->flush();
        }
    }

    public function deleteTicketUsersByTicket(Ticket $ticket)
    {
        $ticketUsers = $this->ticketUserRepo->findBy(['ticket' => $ticket]);

        foreach ($ticketUsers as $ticketUser) {
            $this->om->remove($ticketUser);
        }
        $this->om->flush();
    }

    public function createInterventionComment(User $user, Ticket $ticket, array $messageData, $type, $message = null)
    {
        $content = '';

        if (isset($messageData['type'])) {
            $content .= '<b>['.$this->translator->trans('type_change', [], 'support').']</b><br>';
            $content .= '<b>'.$this->translator->trans('previous_type', [], 'support').'</b> : ';
            $content .= $messageData['oldType'] ? $this->translator->trans($messageData['oldType']->getName(), [], 'support') : '-';
            $content .= '<br><b>'.$this->translator->trans('new_type', [], 'support').'</b> : ';
            $content .= $this->translator->trans($messageData['type']->getName(), [], 'support').'<br><br>';
        }
        if (isset($messageData['status'])) {
            $content .= '<b>['.$this->translator->trans('status_change', [], 'support').']</b><br>';
            $content .= '<b>'.$this->translator->trans('previous_status', [], 'support').'</b> : ';
            $content .= $messageData['oldStatus'] ? $this->translator->trans($messageData['oldStatus']->getName(), [], 'support') : '-';
            $content .= '<br><b>'.$this->translator->trans('new_status', [], 'support').'</b> : ';
            $content .= $this->translator->trans($messageData['status']->getName(), [], 'support').'<br><br>';
        }
        if (!is_null($message)) {
            $content .= $message;
        }
        $comment = new Comment();
        $comment->setTicket($ticket);
        $comment->setUser($user);
        $comment->setIsAdmin(true);
        $comment->setType($type);
        $comment->setCreationDate(new \DateTime());
        $comment->setContent($content);
        $this->persistComment($comment);

        switch ($type) {
            case Comment::PUBLIC_COMMENT:
                $this->sendTicketMail($user, $ticket, 'new_admin_comment', $comment);
                break;
            case Comment::PRIVATE_COMMENT:
                $this->sendTicketMail($user, $ticket, 'new_internal_note', $comment);
                break;
        }

        return $comment;
    }

    public function getStakeholdersByTicket(Ticket $ticket, User $ignoredUser = null)
    {
        $stakeholders = [];
        $ticketUsers = $this->ticketUserRepo->findByTicket($ticket);

        foreach ($ticketUsers as $ticketUser) {
            $user = $ticketUser->getUser();

            if (is_null($ignoredUser) || $user->getId() !== $ignoredUser->getId()) {
                $stakeholders[] = $user;
            }
        }

        return $stakeholders;
    }

    public function initializeForwardedTicket(Ticket $ticket, User $user, Ticket $sourceTicket = null, $officialUuid = null)
    {
        $this->om->startFlushSuite();
        $ticket->setUser($user);
        $ticket->setCreationDate(new \DateTime());
        $ticket->setForwarded(true);
        $ticket->setOfficialUuid($officialUuid);
        $newStatus = $this->getStatusByCode('NEW');

        if (!empty($newStatus)) {
            $this->createIntervention($ticket, $newStatus, $user);
        }
        if (!empty($sourceTicket)) {
            $ticket->setLinkedTicket($sourceTicket);
            $sourceTicket->setLinkedTicket($ticket);
            $forwardedStatus = $this->getStatusByCode('FW');

            if (!empty($forwardedStatus)) {
                $messageData = [];
                $messageData['oldStatus'] = $ticket->getStatus();
                $messageData['status'] = $forwardedStatus;
                $this->createIntervention($sourceTicket, $forwardedStatus, $user);
                $this->createInterventionComment($user, $sourceTicket, $messageData, Comment::PUBLIC_COMMENT);
            }
            $this->persistTicket($sourceTicket);
        }
        $this->persistTicket($ticket);
        $this->om->endFlushSuite();

        return $ticket;
    }

    /**************************************
     * Access to TicketRepository methods *
     **************************************/

    public function getTicketsByUser(User $user)
    {
        return $this->ticketRepo->findBy(['user' => $user, 'userActive' => true, 'forwarded' => false]);
    }

    public function getOngoingTickets(
        $search = '',
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $withPager = true,
        $page = 1,
        $max = 50
    ) {
        $tickets = empty($search) ?
            $this->ticketRepo->findOngoingTickets($orderedBy, $order) :
            $this->ticketRepo->findSearchedOngoingTickets($search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($tickets, $page, $max) : $tickets;
    }

    public function getMyTickets(
        User $user,
        $search = '',
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $withPager = true,
        $page = 1,
        $max = 50
    ) {
        $tickets = empty($search) ?
            $this->ticketRepo->findMyTickets($user, $orderedBy, $order) :
            $this->ticketRepo->findSearchedMyTickets($user, $search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($tickets, $page, $max) : $tickets;
    }

    public function getClosedTickets(
        $search = '',
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $withPager = true,
        $page = 1,
        $max = 50
    ) {
        $tickets = empty($search) ?
            $this->ticketRepo->findClosedTickets($orderedBy, $order) :
            $this->ticketRepo->findSearchedClosedTickets($search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($tickets, $page, $max) : $tickets;
    }

    public function getOngoingForwardedTickets(
        $search = '',
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $withPager = true,
        $page = 1,
        $max = 50
    ) {
        $tickets = empty($search) ?
            $this->ticketRepo->findOngoingForwardedTickets($orderedBy, $order) :
            $this->ticketRepo->findSearchedOngoingForwardedTickets($search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($tickets, $page, $max) : $tickets;
    }

    public function getOngoingTicketsByUser(
        User $user,
        $search = '',
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $withPager = true,
        $page = 1,
        $max = 50
    ) {
        $tickets = empty($search) ?
            $this->ticketRepo->findOngoingTicketsByUser($user, $orderedBy, $order) :
            $this->ticketRepo->findSearchedOngoingTicketsByUser($user, $search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($tickets, $page, $max) : $tickets;
    }

    public function getClosedTicketsByUser(
        User $user,
        $search = '',
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $withPager = true,
        $page = 1,
        $max = 50
    ) {
        $tickets = empty($search) ?
            $this->ticketRepo->findClosedTicketsByUser($user, $orderedBy, $order) :
            $this->ticketRepo->findSearchedClosedTicketsByUser($user, $search, $orderedBy, $order);

        return $withPager ? $this->pagerFactory->createPagerFromArray($tickets, $page, $max) : $tickets;
    }

    public function getForwardedTicketByUuid($uuid)
    {
        $tickets = $this->ticketRepo->findBy(['forwarded' => true, 'officialUuid' => $uuid]);

        return count($tickets) === 1 ? $tickets[0] : null;
    }

    /************************************
     * Access to TypeRepository methods *
     ************************************/

    public function getAllTypes(
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50
    ) {
        $types = empty($search) ?
            $this->typeRepo->findAllTypes($orderedBy, $order) :
            $this->typeRepo->findAllSearchedTypes($search, $orderedBy, $order);

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($types, $page, $max) :
            $types;
    }

    public function getTypeByName($name)
    {
        return $this->typeRepo->findOneByName($name);
    }

    /**************************************
     * Access to StatusRepository methods *
     **************************************/

    public function getAllStatus(
        $search = '',
        $orderedBy = 'order',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50
    ) {
        $status = empty($search) ?
            $this->statusRepo->findAllStatus($orderedBy, $order) :
            $this->statusRepo->findAllSearchedStatus($search, $orderedBy, $order);

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($status, $page, $max) :
            $status;
    }

    public function getStatusByCode($code)
    {
        return $this->statusRepo->findOneBy(['code' => $code]);
    }

    public function getOrderOfLastStatus()
    {
        return $this->statusRepo->findOrderOfLastStatus();
    }

    public function getStatusByCodeInsensitive($code)
    {
        return $this->statusRepo->findStatusByCodeInsensitive($code);
    }

    /******************************************
     * Access to TicketUserRepository methods *
     ******************************************/

    public function getActiveTicketUserByUser(User $user)
    {
        return $this->ticketUserRepo->findBy(['user' => $user, 'active' => true], ['activationDate' => 'ASC']);
    }
}
