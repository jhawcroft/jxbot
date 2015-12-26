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

if (!defined('JXBOT_ADMIN')) die('Direct script access not permitted.');


$inputs = JxBotUtil::inputs('input');
if (trim($inputs['input']) != '') 
{
	JxBotConverse::resume_conversation('admin');
	
	$response = JxBotConverse::get_response($inputs['input']);
}
else $response = JxBotConverse::get_greeting();


?>


<?php JxWidget::textfield(array(
	'name'=>'input', 
	'label'=>'Administrator',
	'max'=>150,
	'autofocus'=>true
)); ?>


<p><?php JxWidget::button('Talk'); ?></p>


<div class="left">
<img src="<?php print JxBotConfig::bot_url(); ?>jxbot/core/gfx/robot-small.png" id="chat-robot">
</div>


<div class="bubble" style="max-width: 80%;">
<div class="bubble-top"><div class="bubble-corner-tl"></div><div class="bubble-corner-tr"></div></div>
<div class="bubble-left"></div>
<div class="bubble-content">

<?php print $response; ?>

</div>
<div class="bubble-right"></div>
<div class="bubble-bot"><div class="bubble-corner-bl"></div><div class="bubble-corner-br"></div></div>
</div>


<!--
<hr>

<-- STATS GO HERE ->

<h2>Status</h2>

-->