
function jxbot_submit_input(in_input, in_url)
{
	var user_input = in_input.value;
	in_input.value = '';
	
	var response_div = in_input.nextElementSibling;
	
	var request = new XMLHttpRequest();
	request.onreadystatechange = function()
	{
		if (request.readyState == 4)
		{
			if (request.status == 200)
				response_div.innerHTML = request.responseText;
			else
				response_div.textContent = 'Sorry, the bot appears to be down for maintenance.';
			response_div.style.display = 'block';
			user_input.value = '';
			user_input.focus();
		}
	};
	request.open('GET', in_url + '?' + user_input, true);
	request.send();
}

function jxbot_input_keypress(event, in_url)
{
	if (event.which == 13 || event.keyCode == 13)
	{
		jxbot_submit_input(event.target, jxbot_url);
		event.stopPropagation();
		event.preventDefault();
	}
}

