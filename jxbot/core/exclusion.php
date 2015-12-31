<?php
/*******************************************************************************
JxBot - conversational agent for the web
Copyright (c) 2015 Joshua Hawcroft

    May all beings have happiness and the cause of happiness.
    May all beings be free of suffering and the cause of suffering.
    May all beings rejoice in the happiness of others.
    May all beings abide in equanimity; free of attachment and delusion.

Permission is hereby granted, free of charge, to any person obtaining a copy of 
this software and associated documentation files (the "Software"), to deal in 
the Software without restriction, including without limitation the rights to 
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so, 
subject to the following conditions:

The above copyright notice, preamble and this permission notice shall be 
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR 
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER 
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN 
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*******************************************************************************/

/* utilities to prevent the same script from running twice simultaneously;
used by the asyncronous AIML loader */

if (!defined('JXBOT')) die('Direct script access not permitted.');


class JxBotExclusion
{
	private static $sem = null;
	private static $socket = null;
	
	
	public static function get_exclusive()
	{
		return JxBotExclusion::get_exclusive_by_socket();
	}
	
	
	private static function get_exclusive_by_socket()
	{
		JxBotExclusion::$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if (JxBotExclusion::$socket === false)
			throw new Exception("Can't create exclusion socket: ".socket_last_error($socket));
		
		if (@socket_bind(JxBotExclusion::$socket, '127.0.0.1', 10000) === false) 
			return false;
		else
			return true;
	}
	

	private static function get_exclusive_by_semaphore()
	/* returns true if this script is the only current invocation to call get_exclusive,
	or false otherwise.  if there's a problem, this will throw an exception, which
	should be logged. */
	{
		if (JxBotExclusion::$sem = sem_get( ftok(__FILE__, "j") , 1)) 
		{
			if (!sem_acquire(JxBotExclusion::$sem, true)) return false;
			return true;
		}
		throw new Exception("Couldn't acquire System V semaphore.");
		return false;
	}
	
	
	public static function release_exclusive()
	{
		 // this should be called automatically anyway by PHP or the OS :
		sem_release(JxBotExclusion::$sem);
	}
}

/*
Two viable mechanisms:

Semaphores
----------

// no wait doesn't work until very new versions of PHP.


if ($theSemaphore = sem_get("123456",1)) { // this "1" ensures that there is nothing parallel
  if (sem_acquire($theSemaphore)) {  // this blocks the execution until other processes or threads are finished
    <put your code to serialize here>
  }
  sem_release($theSemaphore);
}


Sockets
-------

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (false === $socket) {
    throw new Exception("can't create socket: ".socket_last_error($socket));
}
## set $port to something like 10000
## hide warning, because error will be checked manually
if (false === @socket_bind($socket, '127.0.0.1', $port)) {
    ## some instanse of the script is running
    return false;
} else {
    ## let's do your job
    return $socket;
}

*/


