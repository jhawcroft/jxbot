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
 
 



/*
Toggle Bar & Toggle Switch
*/

function switch_was_toggled(in_switch, in_new_state)
{
	//var handler_name = in_switch.getAttribute('data-handler');
	//if (handler_name !== undefined)
	//	eval( handler_name + '(' + in_new_state + ')' );
	
	in_switch.nextElementSibling.checked = (in_new_state == 0);
}


function toggle_switch(in_switch)
{
	var state = 0;
	var opts = in_switch.children;
	for (var o = 0; o < opts.length; o++)
	{
		if (opts[o].className != 'off') state = o;
		opts[o].className = 'off';
	}
	state ++;
	if (state >= opts.length) state = 0;
	if (!in_switch.classList.contains('toggle-bar'))
	{
		if (state == 0) opts[0].className = 'yes';
		else opts[1].className = 'no';
	}
	else opts[state].className = 'on';
	
	switch_was_toggled(in_switch, state);
}


function init_wui()
{
	window.addEventListener('click', 
	function(in_event)
	{
		var tgt = in_event.target;
		if ((tgt.tagName == 'DIV') && (tgt.parentElement)
		&& (tgt.parentElement.classList.contains('widget-toggle')))
		{
			var toggle = tgt.parentElement;
			var state = 0;
			var opts = toggle.children;
			for (var o = 0; o < opts.length; o++)
			{
				if (opts[o] === tgt) state = o;
				//if (opts[o].className != 'off') state = o;
				opts[o].className = 'off';
			}
			if (!toggle.classList.contains('toggle-bar'))
			{
				if (tgt === toggle.children[0]) tgt.className = 'yes';
				else tgt.className = 'no';
			}
			else tgt.className = 'on';
			switch_was_toggled(toggle, state);
		}
	});
	
	window.addEventListener('keydown',
	function(in_event)
	{
		var tgt = document.activeElement;
		if (!tgt) return;
		if ((tgt.tagName == 'DIV')
		&& (tgt.classList.contains('widget-toggle')))
		{
			if (in_event.keyCode === 0 || in_event.keyCode === 32)
			{
				toggle_switch(tgt);
				in_event.preventDefault();
			}
		}
	});
}


init_wui();
