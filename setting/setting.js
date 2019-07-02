function validateAttendanceSetting()
{
	if(!CheckNumber(document.form1.academic_week.value, "Number of academic week", true))
		return false;
	if(!CheckNumber(document.form1.dn_percentage.value, "DN Percentage", true))
		return false;
	if(!CheckNumber(document.form1.excuse_percentage.value, "Excuse Percentage", true))
		return false;
	if(!CheckDecimal(document.form1.green.value, "Green", true))
		return false;
	if(!CheckDecimal(document.form1.yellow.value, "Yellow", true))
		return false;
	if(!CheckDecimal(document.form1.orange.value, "Orange", true))
		return false;
	if(!CheckDecimal(document.form1.red.value, "Red", true))
		return false;
	return true;		
}

function validateGlobalSetting()
{
	if(!CheckNumber(document.form1.pass_fail_value.value, "Pass Grade Value", true))
		return false;
	return true;		
}

function delete_category(id)
{
	if(confirm("Are you sure you want to delete the category?"))
	{
		document.form1.delete_id.value = id;
		document.form1.submit();
	}
}

///////////////////////////////////////////////////
////projector/////////////////////////////////
function edit_record(id)
{
	url = "projector.php";
	url=url+"?id=" + id;
	url=url+"&action=3";
	url=url+"&sid="+Math.random();
	document.location = url;
}

function delete_record(id)
{
	if(confirm("Are you sure you want to delete the projector record?"))
	{
		url = "projector.php";
		url=url+"?id=" + id;
		url=url+"&action=4";
		url=url+"&sid="+Math.random();
		document.location = url;
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

function CheckDecimal(testValue, theControl, showAlert)
{
    var anum = /^\d*\.{0,1}\d+$/;
//	var anum=/(^\d+$)/	
	if (!anum.test(testValue))
	{
		if(showAlert)
			alert(theControl + " must be a positive number.");
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
