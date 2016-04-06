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
 * File:        Task.php
 * Project:     PHP Multi threading
 *
 * @author      Al-Fallouji Bashar
 */
namespace Threading\Task;

/**
 * Abstract base inherited from all tasks
 */
abstract class Base
{
    public $params = array();
    
    public function __construct(array $params = array())
    {
        $this->params = $params;
    }
    
    /**
     * Initialize (called first by the task manager)
     * 
     * @return mixed
     */
    public function initialize() 
    {
        return true;
    }

    /**
     * Called by the task manager upon sucess (when the process method returned true)
     * 
     * @return mixed
     */
    public function onSuccess()
    {
        return true;
    }

    /**
     * Called by the task manager upon failure (when the process method returned false)
     * 
     * @return mixed
     */
    public function onFailure() 
    {
        return false;
    }

    /**
     * Called by the task manager after the task has ended, This is called on the PARENT process and has no access to varibles created within the child
     * 
     * @return void
     */
    public function onComplete() 
    {
    }
    
    /**
     * Main method containing the logic to be executed by the task
     * 
     * @param void
     *
     * @return boolean True upon success, false otherwise
     */
    abstract public function process();
}
