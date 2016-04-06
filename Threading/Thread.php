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
 * File:        Thread.php
 * Project:     PHP Multi threading
 *
 * @author      Al-Fallouji Bashar
 * @author      Brandon Lis
 */
namespace Threading;

use Threading\Task\Base as AbstractTask;

/**
 * Thread class
 */
class Thread
{
    /**
     * @var int
     */
    protected $pid;

    /**
     * @var Runnable
     */
    protected $task;

    /**
     * Construct the Thread
     *
     * @param AbstractTask $task Task to start
     *
     * @return void
     */
    public function __construct(AbstractTask $task)
    {
        $this->task = $task;
    }
    
    /**
     * Run a thread
     */
    public function run()
    {
        if ($this->task) {
            $this->task->initialize();

            // On success
            if ($this->task->process())
            {
                $this->task->onSuccess();
            } 
            else 
            {
                $this->task->onFailure();
            }
        }

        return null;
    }
    
    /**
     * Run the onComplete on complete of the task
     */
    public function done() {
        $task =& $this->task;
        if (is_callable(array($task, 'onComplete')))
        {
            $task->onComplete();
        }
    }
    
    /**
     * Waits on a forked child
     */
    public function wait()
    {
        if ($this->pid) {
            pcntl_waitpid($this->pid, $status);
        }
    }
    
    /**
     * Check on a forked child
     */
    public function check()
    {
        if ($this->pid) {
            return pcntl_waitpid($this->pid, $status, WNOHANG);
        }
        throw new \Exception("pid variable is invalid");
    }

    /**
     * Returns process id
     *
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Start a thread
     */
    public function start()
    {
        if ($this->pid) {
            return $this->pid;
        }
        
        $this->fork();

        if (!$this->pid) {
            $this->run();
            posix_kill(getmypid(), 9);
            exit;
        }
        
        return $this->pid;
    }

    /**
     * Fork the process
     *
     * @param void
     *
     * @return void
     */
    protected function fork()
    {
        $this->pid = pcntl_fork();
        if ($this->pid == -1) 
        {
            throw new \Exception('[Pid:' . getmypid() . '] Could not fork process');
        }
    }
}

?>