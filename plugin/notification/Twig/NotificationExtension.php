<?php

namespace Icap\NotificationBundle\Twig;

class NotificationExtension extends \Twig_Extension
{
    protected $translator;

    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('smartDate', array($this, 'getSmartDate')),
            new \Twig_SimpleFilter('notificationMessage', array($this, 'getNotificationMessage')),
            new \Twig_SimpleFilter('rssMessage', array($this, 'getRssMessage')),
        );
    }

    public function getNotificationMessage($notification, $user = null, $resource = null, $systemName = '')
    {
        if (!empty($resource)) {
            $actionMessage = $this->translator->trans(
                $notification->getActionKey(),
                array('%resourceName%' => $resource['name']),
                'notification'
            );
        } else {
            $actionMessage = $this->translator->trans($notification->getActionKey(), array(), 'notification');
        }

        if (!empty($user)) {
            $message = '<strong>'.$user['firstName'].' '.$user['lastName'].'</strong> ';
        } else {
            $message = '<strong>'.$systemName.'</strong> ';
        }

        $message .= $actionMessage;

        return $message;
    }

    public function getSmartDate($rawDate)
    {
        $timestamp = $rawDate->getTimestamp();
        $months = explode(', ', $this->translator->trans('months', array(), 'notification'));
        $days = explode(', ', $this->translator->trans('days', array(), 'notification'));

        if ($timestamp > strtotime('-2 minutes')) {
            $smartDate = $this->translator->trans('few_seconds_ago', array(), 'notification');
        } elseif ($timestamp > strtotime('-59 minutes')) {
            $minutes = floor((strtotime('now') - $timestamp) / 60);
            $smartDate = $this->translator->transChoice(
                'minutes_ago',
                $minutes,
                array('%count%' => $minutes),
                'notification'
            );
        } elseif ($timestamp > strtotime('today')) {
            $hours = floor((strtotime('now') - $timestamp) / (60 * 60));
            $smartDate = $this->translator->transChoice(
                'hours_ago',
                $hours,
                array('%count%' => $hours),
                'notification'
            );
        } elseif ($timestamp > strtotime('yesterday')) {
            $smartDate = $this->translator->trans('yesterday', array(), 'notification').
                $rawDate->format($this->translator->trans('hour_format', array(), 'notification'));
        } elseif ($timestamp > strtotime('this week')) {
            $smartDate = $days[$rawDate->format('N') - 1].
                $rawDate->format($this->translator->trans('hour_format', array(), 'notification'));
        } elseif ($timestamp > strtotime('first day of January', time())) {
            $month = $months[$rawDate->format('n') - 1];
            $day = $rawDate->format('j');
            $smartDate = $this->translator->trans(
                    'day_format',
                    array(
                        '%month%' => $month,
                        '%day%' => $day,
                    ),
                    'notification'
                ).$rawDate->format($this->translator->trans('hour_format', array(), 'notification'));
        } else {
            $month = $months[$rawDate->format('n') - 1];
            $day = $rawDate->format('j');
            $smartDate = $this->translator->trans(
                    'day_format',
                    array(
                        '%month%' => $month,
                        '%day%' => $day,
                    ),
                    'notification'
                ).' '.$rawDate->format('Y').' '.
                $rawDate->format($this->translator->trans('hour_format', array(), 'notification'));
        }

        return $smartDate;
    }

    public function getRssMessage($message)
    {
        $message = preg_replace("/<span[^>]*?>.*?<\/span>/si", '', $message);

        return html_entity_decode($message, ENT_QUOTES);
    }

    public function getName()
    {
        return 'notification_extension';
    }
}
