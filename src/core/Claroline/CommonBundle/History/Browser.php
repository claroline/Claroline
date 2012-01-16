<?php

namespace Claroline\CommonBundle\History;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Claroline\CommonBundle\Exception\ClarolineException;

class Browser
{
    private $request;
    private $session;  
    private $historyQueue;
    private $queueMaxSize;
    
    public function __construct(Request $request, Session $session)
    {
        $this->request = $request;
        $this->session = $session;
        $this->historyQueue = $this->initQueue($session);
        $this->queueMaxSize = 4; // TODO : use a config param
    }
    
    public function keepCurrentContext($key)
    {
        if (! is_string($key) || empty($key))
        {
            throw new ClarolineException(
                'The keepCurrentContext() $key argument must be an non empty string.'
            );
        }
        
        $this->enqueue($this->request->getUri());
    }
    
    public function getLastContext()
    {
        // if queue = 0 -> exception or return current uri ?
        return $this->historyQueue[count($this->historyQueue) - 1];
    }
    
    public function getContextHistory()
    {
        return $this->historyQueue;
    }
    
    private function initQueue(Session $session)
    {
        if (! $session->has('context_history'))
        {
            $queue = array();
            $session->set('context_history', $queue);
            
            return $queue;
        }
        
        $queue = $session->get('context_history');
        $queueSize = $count($queue);
        
        if ($queueSize > $this->queueMaxSize)
        {
            for ($i = 0; $i < $queueSize - $this->queueMaxSize; ++$i)
            {
                array_shift($queue);
            }
        }
        
        return $queue;
    }
    
    private function enqueue($element)
    {
        foreach ($this->historyQueue as $index => $storedElement)
        {
            if ($element == $storedElement)
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

        $this->historyQueue[] = $element;
    }
}