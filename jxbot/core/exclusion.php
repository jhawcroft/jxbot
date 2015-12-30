<?php
/********************************************************************************
 *  JxBot - conversational agent for the web
 *  Copyright (c) 2015 Joshua Hawcroft
 *
 *      May all beings have happiness and the cause of happiness.
 *      May all beings be free of suffering and the cause of suffering.
 *      May all beings rejoice in the happiness of others.
 *      May all beings abide in equanimity; free of attachment and delusion.
 * 
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 *
 *  1) Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *  2) Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 *
 *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS BE LIABLE FOR ANY
 *  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 *  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 *  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 *  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
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


