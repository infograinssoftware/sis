// Javascript functions for Weeks course format
function add_role()
{
	
	if(confirm("Are you sure you want to add the user to the role?"))
	{
		var role = document.form1.role.value;
		var subrole = document.form1.subrole.value;
		var user = document.getElementById('user');
		var role_value = document.getElementById('role_value');
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url="role_action.php";
		url = url+"?emplid=" + user.value;
		url = url+"&role=" + role;
		url = url+"&subrole=" + subrole;
		url = url+"&role_value=" + role_value.value;
		url = url+"&action=" + 1;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}	
}

function delete_role(user)
{
	if(confirm("Are you sure you want to remove the user from the role?"))
	{
		var role = document.form1.role.value;
		var subrole = document.form1.subrole.value;
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url="role_action.php";
		url = url+"?emplid=" + user;
		url = url+"&role=" + role;
		url = url+"&subrole=" + subrole;
		url = url+"&action=" + 2;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}	
}

function refresh_role()
{
	var role = document.form1.role.value;
	url="role.php?role=" + role;
	location = url;
}

function handleKeyPress2(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		add_role();
	}
}

function search_role()
{
	var role = document.form1.role.value;
	var subrole = document.form1.subrole.value;
	if(role != "" && subrole != "") //at least one must not be empty
	{
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url="role_action.php";
		url = url+"?role=" + role;
		url = url+"&subrole=" + subrole;
		url = url+"&action=" + 0;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}
	else
		alert("Please select a role and permission");
	return false;
}

function search_user()
{
	var emplid = document.form1.emplid.value;
	var name = document.form1.name.value;
	var theType = document.form1.type.value;
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url="user_action.php";
	//GET example
	url = url+"?emplid=" + emplid;
	url = url+"&name=" + name;
	url = url+"&type=" + theType;
	url = url+"&action=" + 0;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);
	return false;
}

function reset_password(emplid)
{
	if(confirm("Are you sure you want to reset the password?"))
	{
		var emplid = document.form1.emplid.value;
		var name = document.form1.name.value;
		var theType = document.form1.type.value;
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url="user_action.php";
		url = url+"?emplid=" + emplid;
		url = url+"&name=" + name;
		url = url+"&type=" + theType;
		url = url+"&action=" + 1;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}
}

function handleKeyPress(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		search_user();
	}
}

function handleKeyPress3(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		search_suspen_user();
	}
}

function search_suspend_user()
{
	var emplid = document.form1.emplid.value;
	if(emplid != "") //at least one must not be empty
	{
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url="suspend_action.php";
		url = url+"?emplid=" + emplid;
		url = url+"&action=" + 0;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}
	else
		alert("Please enter a student id");
	return false;
}

function remove_suspension(emplid)
{
	if(confirm("Are you sure you want to remove the suspension for this user?"))
	{
		document.form1.delete_id.value = emplid;
		document.form1.submit();
	}
}

function validateForm()
{
	if(document.form1.course.value == "")
	{
		alert("Employee ID / Student ID cannot be empty");
		return false;
	}
	else
		return true;
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
