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
     * Start the task manager
     *
     * @param AbstractTask $task Task to start
     *
     * @return void
     */
    public function start(AbstractTask $task, $continueWhenChildExited = false)
    {
        $pid = pcntl_fork();
        if ($pid == -1) 
        {
            throw new \Exception('[Pid:' . getmypid() . '] Could not fork process');
        } 
        // Parent thread
        elseif ($pid) 
        {
            $this->_activeThreads[$pid] = true;

            // Reached maximum number of threads allowed
            if($this->maxThreads <= count($this->_activeThreads)) 
            {
                // Parent Process : Checking all children have ended (to avoid zombie / defunct threads)
                while(!empty($this->_activeThreads)) 
                {
                    $endedPid = pcntl_wait($status); //, WNOHANG
                    if ($endedPid === 0) {
                        //usleep(500);
                        continue;
                    }
                    
                    if(-1 == $endedPid) 
                    {
                        $this->_activeThreads = array();
                    }
                    unset($this->_activeThreads[$endedPid]);
                    
                    // Break the loop and continue when a child exited so we don't have to wait for all children to exit to call the next thread
                    if ($continueWhenChildExited) {
                        break 1;
                    }
                }
            }
        } 
        // Child thread
        else 
        {
            $task->initialize();

            // On success
            if ($task->process())
            {
                $task->onSuccess();
            } 
            else 
            {
                $task->onFailure();
            }
            
            posix_kill(getmypid(), 9);
        }
        pcntl_wait($status, WNOHANG);
    }
    
    /**
     * Wait for all remaining children to complete
     *
     * @return void
     */
    public function wait()
    {
        if ($this->parentPID != getmypid()) {
            throw new \Exception("Wait() should only be ran on the parent process!");
        }
        
        // Parent Process : Waiting for all children to complete
        while(!empty($this->_activeThreads)) 
        {
            $endedPid = pcntl_wait($status); //, WNOHANG
            if ($endedPid === 0) {
                //usleep(500);
                continue;
            }
            
            if(-1 == $endedPid) 
            {
                $this->_activeThreads = array();
            }
            unset($this->_activeThreads[$endedPid]);
        }
    }
}
