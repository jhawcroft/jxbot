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

if (!defined('JXBOT_ADMIN')) die('Direct script access not permitted.');

?>
<p>A light-weight natural-language chat system based upon <a href="http://www.alicebot.org/aiml.html">Alice and AIML</a><br>
by <a href="http://joshhawcroft.org/">Joshua Hawcroft</a></p>

<p><strong>version <?php print JxBot::VERSION; ?></strong></p>

<h2>Acknowledgements</h2>

<p>The author would like to gratefully acknowledge the presence of and contributors to 
<a href="https://openclipart.org/">Open Clipart</a> - a public domain clipart library - without which the JxBot administration 
interface would have been extraordinarily dull.</p>

<p>My thanks to the authors of the two fonts used in the administration panel, Christian Robertson and Vernon Adams for making their fonts available under permissive open-source licenses.</p>

<p>Finally, no work of human ingenuity is built in isolation; I'd also like to thank everyone involved in the development of AIML to date, without which this project may not have come about.</p>


<div style="height: 10px;"></div>

<?php JxBotUtil::phpinfo(); ?>
