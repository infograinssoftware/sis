// Javascript functions
function delete_record(id)
{
	if(confirm("Are you sure you want to delete the selected certificate?"))
	{
		url = "index.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}

function delete_recepient(id, emplid, page)
{
	if(confirm("Are you sure you want to delete the recepient for this certificate?"))
	{
		url = "user.php";
		url=url+"?id=" + id;
		url=url+"&emplid=" + emplid;
		url=url+"&page=" + page;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}
}

function delete_all_recepient()
{
	if(confirm("Are you sure you want to delete all the recepients for this certificate?"))
	{
		document.form1.delete_all.value = 2;
		document.form1.submit();
	}
}

function generate_serial()
{
	if(confirm("Are you sure you want to generate the serial number for the recepients without serial number in this certificate?"))
	{
		document.form1.delete_all.value = 3;
		document.form1.submit();
	}
}

function certificate_search()
{
	document.form1.submit();
}

function certificate_print_report()
{
	var report = document.form1.report.value;
	var respondant = document.form1.respondant.value;
	var sortOrder = document.form1.sort.value;
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url="report_action.php";
	url = url+"?report=" + report;
	url = url+"&respondant=" + respondant;
	url = url+"&sort=" + sortOrder;
	url = url+"&action=" + 1;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);				
}

function handleKeyPress(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		certificate_search();
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
