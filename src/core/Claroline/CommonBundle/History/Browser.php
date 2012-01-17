<?php

namespace Claroline\CommonBundle\History;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Claroline\CommonBundle\History\Context;
use Claroline\CommonBundle\Exception\ClarolineException;

class Browser
{
    const HISTORY_SESSION_VARIABLE = 'context_history';

    private $request;
    private $session;  
    private $historyQueue;
    private $queueMaxSize;
    
    public function __construct(Request $request, Session $session, $historyMaxSize)
    {
        $this->request = $request;
        $this->session = $session;
        $this->queueMaxSize = $historyMaxSize;
        $this->historyQueue = $this->initQueue();
    }
    
    public function keepCurrentContext($contextName)
    {
        if ('GET' !== $this->request->getMethod())
        {
            throw new ClarolineException(
                'The keepCurrentContext() method is only available for GET contexts.'
            );
        }
        
        if (! is_string($contextName) || empty($contextName))
        {
            throw new ClarolineException(
                'The keepCurrentContext() $contextName argument must be an non empty string.'
            );
        }
        
        $this->enqueue(new Context($contextName, $this->request->getUri()));
    }  
    
    public function getLastContext()
    {
        $historyQueue = $this->session->get(self::HISTORY_SESSION_VARIABLE);
        $lastContextIndex = count($this->historyQueue) - 1;
        
        if (array_key_exists($lastContextIndex, $historyQueue))
        {
            return $historyQueue[$lastContextIndex];
        }
        
        return null;
    }
    
    public function getContextHistory()
    {
        $historyQueue = $this->session->get(self::HISTORY_SESSION_VARIABLE);
        
        return array_reverse($historyQueue);
    }
    
    private function initQueue()
    {        
        if (! $this->session->has(self::HISTORY_SESSION_VARIABLE))
        {
            $queue = array();
            $this->session->set(self::HISTORY_SESSION_VARIABLE, $queue);
            
            return $queue;
        }
        
        $queue = $this->session->get(self::HISTORY_SESSION_VARIABLE);
        $queueSize = count($queue);
        
        if ($queueSize > $this->queueMaxSize)
        {
            for ($i = 0; $i < ($queueSize - $this->queueMaxSize); ++$i)
            {
                array_shift($queue);
            }
            
            $this->session->set(self::HISTORY_SESSION_VARIABLE, $queue);
        }
        
        return $queue;
    }
    
    private function enqueue(Context $context)
    {
        foreach ($this->historyQueue as $index => $storedContext)
        {
            if ($context == $storedContext)
            {
                unset($this->historyQueue[$index]);
                $this->historyQueue = array_values($this->historyQueue);
                break;
            }
        }

        if (count($this->historyQueue) == $this->queueMaxSize)
        {
            array_shift($this->historyQueue);
        }

        $this->historyQueue[] = $context;
        $this->session->set(self::HISTORY_SESSION_VARIABLE, $this->historyQueue);
    }
}