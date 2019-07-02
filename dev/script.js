// Javascript functions
//typical ajax function
function tp_admin_xxx()
{
	var code = document.form1.room.value;
	if(code != "")
	{
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url="room_action.php";
		url = url+"?code=" + code;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = StateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}
	else
		alert("Please enter the room code");
	return false;
}
