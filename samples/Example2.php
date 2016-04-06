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
require(__DIR__ . '/../../../autoload.php');

$maxThreads = 5;
echo 'Example of the multi-thread manager with ' . $maxThreads . ' threads' . PHP_EOL . PHP_EOL;
$exampleTask = new Threading\Task\Example();
$multithreadManager = new Threading\Multiple();

$cpt = 0;
while (++$cpt <= 34)
{
    $multithreadManager->add($exampleTask);
}

$multithreadManager->run();

echo "Waiting for remaining threads to finish." . PHP_EOL;

$multithreadManager->wait();

echo "Done." . PHP_EOL;
