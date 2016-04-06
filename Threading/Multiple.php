<?php
/**
 * Note : Code is released under the GNU LGPL
 *
 * Please do not change the header of this file
 *
 * This library is free software; you can redistribute it and/or modify it under the terms of the GNU
 * Lesser General Public License as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * See the GNU Lesser General Public License for more details.
 */

/**
 * File:        Multiple.php
 * Project:     PHP Multi threading
 *
 * @author      Al-Fallouji Bashar
 */
namespace Threading;

use Threading\Task\Base as AbstractTask;
use Threading\Thread as ThreadTask;

/**
 * Multi-thread / task manager
 */
class Multiple
{
    /**
     * Assoc array of pid with active threads
     * @var array
     */
    protected $_activeThreads = array();
    protected $_threads = array();

    /**
     * Maximum number of child threads that can be created by the parent
     * @var int
     */
    protected $maxThreads = 5;

    /**
     * Class constructor
     *
     * @param int $maxThreads Maximum number of child threads that can be created by the parent
     */
    public function __construct($maxThreads = 5)
    {
        $this->maxThreads = $maxThreads;
        $this->parentPID = getmypid();
    }

    /**
     * Queue a task to be ran asynchronously
     *
     * @param AbstractTask $task Task to start
     *
     * @return void
     */
    public function add(AbstractTask &$task)
    {
        $this->_threads[] = new ThreadTask($task);
    }
    
    /**
     * Run the tasks
     *
     * @param callable $callable Function to run when all tasks have been forked
     *
     * @return void
     */
    public function run($callable = null)
    {
        foreach($this->_threads as &$thread)
        {
            $pid = $thread->start();
            $this->_activeThreads[$pid] =& $thread;
            
            // Reached maximum number of threads allowed
            if ($this->maxThreads <= count($this->_activeThreads)) 
            {
                $this->wait(true);
            }
        }
        
        if (is_callable('callable'))
        {
            $callable();
        }
    }
    
    /**
     * Start the task manager
     *
     * @param AbstractTask $task Task to start
     *
     * @return void
     */
    public function start(AbstractTask &$task, $continueWhenChildExited = false)
    {
        $thread = new ThreadTask($task);
        $pid = $thread->start();
        // Parent thread
        if ($pid) 
        {
            $this->_activeThreads[$pid] =& $thread;

            // Reached maximum number of threads allowed
            if($this->maxThreads <= count($this->_activeThreads)) 
            {
                $this->wait(true);
            }
        } 
        pcntl_wait($status, WNOHANG);
    }
    
    /**
     * Wait for all remaining children to complete
     *
     * @return void
     */
    public function wait($breakonComplete = false)
    {
        if ($this->parentPID != getmypid())
        {
            throw new \Exception("Wait() should only be ran on the parent process!");
        }
        
        // Parent Process : Waiting for all children to complete
        while(!empty($this->_activeThreads)) 
        {
            $endedPid = pcntl_wait($status); //, WNOHANG
            if ($endedPid === 0)
            {
                //usleep(500);
                continue;
            }
            
            if(-1 == $endedPid) 
            {
                foreach($this->_activeThreads as &$thread)
                {
                    $thread->done();
                }
                $this->_activeThreads = array();
            }
            
            $this->_activeThreads[$endedPid]->done();
            unset($this->_activeThreads[$endedPid]);
            
            if ($breakonComplete)
            {
                break 1;
            }
        }
    }
}
