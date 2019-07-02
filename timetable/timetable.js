// Javascript functions for Weeks course format
function search_room()
{
	var code = document.form1.room.value;
	var week = document.form1.week.value;
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
		url = url+"&week=" + week;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}
	else
		alert("Please enter the room code");
	return false;
}

function refresh_room()
{
	var campus = document.form1.campus.value;
	location = "room.php?campus=" + campus;
}

function validateForm()
{
	if(document.form1.user.value == "")
	{
		alert("Employee ID / Student ID cannot be empty");
		return false;
	}
	else
		return true;
}

function reserve_room()
{
	var e = document.getElementById('room-form');
	e.style.display="block";
}

function cancel_book_room()
{
	var e = document.getElementById('room-form');
	e.style.display="none";
}

function book_room()
{
	var description = document.form2.description.value;
	if(description == "")
		alert("Please enter a booking purpose");
	else
	{
		if(confirm("Are you sure you want to add the room booking?"))
		{
			var code = document.form1.room.value;
			var week = document.form1.week.value;
			var day = document.form2.day.value;
			var time = document.form2.time.value;
			var period = document.form2.period.value;
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
				url = url+"&week=" + week;
				url = url+"&day=" + day;
				url = url+"&time=" + time;
				url = url+"&period=" + period;
				url = url+"&description=" + description;
				url = url+"&sid=" + Math.random();
				xmlhttp.onreadystatechange = stateChanged;
				xmlhttp.open("GET", url, true);
				xmlhttp.send(null);				
			}
			else
				alert("Please enter the room code");
			return false;		
		}
	}
}

////////////////////////////////////////
//Common functions
////////////////////////////////////////
function CheckNumber(testValue, theControl, showAlert)
{
	var anum=/(^\d+$)/	
	if (!anum.test(testValue))
	{
		if(showAlert)
			alert(theControl + " must be a positive integer number.");
		return false;
	}
	else
		return true;	
}


function stateChanged()
{
	if (xmlhttp.readyState==4)
	{
		document.getElementById("ajax-content").innerHTML=xmlhttp.responseText; //comment to remove message
	}
	else //while loading, display an ajax loading image
	{
		document.getElementById("ajax-content").innerHTML="<br><div align=\"center\"><img src=\"../images/ajax-loader.gif\" width=\"100\" height=\"100\" /></div>";
	}
}

function GetXmlHttpObject()
{
	if (window.XMLHttpRequest)
  	{
  		// code for IE7+, Firefox, Chrome, Opera, Safari
  		return new XMLHttpRequest();
  	}
	if (window.ActiveXObject)
  	{
  		// code for IE6, IE5
  		return new ActiveXObject("Microsoft.XMLHTTP");
  	}
	return null;
}
