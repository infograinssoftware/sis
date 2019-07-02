// Javascript functions
function delete_record(id)
{
	if(confirm("Are you sure you want to delete the selected certificate?"))
	{
		url = "external.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}

function external_resource_search()
{
	document.form1.submit();
}

function handleKeyPress2(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		external_resource_search();
	}
}

////////////Projector functions
//status = 0 - turn off
//status = 1 - turn on
//status = 2 - update status
function update_projector_status(status)
{
	proceed = true;
	if(status == 0)// try to turn off, give warning message
		proceed = confirm("Are you sure you want to turn off the projector?");
	if(proceed)
	{
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		url = "projector_action.php";
		room = document.form1.sc_roomlist.value;
		url=url+"?room=" + room;
		url=url+"&action=" + status;
		url=url+"&sid="+Math.random();
		xmlhttp.onreadystatechange=stateChanged;
		xmlhttp.open("GET",url,true);
		xmlhttp.send(null);
//		return false;
	}
}

////////////////////////////////////////////////////
function show_envelope()
{
	var code = document.form1.code.value;
	var exam_type = document.form1.exam_type.value;
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url="envelope_action.php";
	url = url+"?code=" + code;
	url = url+"&type=" + exam_type;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);				
	return false;	
}

function print_envelope()
{
	//var venue = document.form1.venue.value;
	var venue = getValueFromRadioButton("venue");
	if(venue == '')
		alert("Please select an examination venue");
	else
	{
		var code = document.form2.code.value;
		var exam_type = document.form2.exam_type.value;
		var hour = document.form2.hour.value;
		var minute = document.form2.minute.value;
		var extra_paper = document.form2.extra_paper.value;
		var coordinator = document.form2.coordinator.value;
		var comment = document.form2.comment.value;
		var url="envelope_print.php";
		url=url+"?code=" + code;
		url=url+"&type=" + exam_type;
		url=url+"&hour=" + hour;
		url=url+"&minute=" + minute;
		url=url+"&paper=" + extra_paper;
		url=url+"&coordinator=" + coordinator;
		url=url+"&comment=" + comment;
		url=url+"&venue=" + venue;
		url=url+"&sid="+Math.random();
		window.open(url,'','scrollbars=yes,menubar=yes,height=600,width=800,resizable=yes,toolbar=yes,location=no,status=yes');
	}
}

function getValueFromRadioButton(name) {
   //Get all elements with the name
   var buttons = document.getElementsByName(name);
   for(var i = 0; i < buttons.length; i++) {
      //Check if button is checked
      var button = buttons[i];
      if(button.checked) {
         //Return value
         return button.value;
      }
   }
   //No radio button is selected. 
   return null;
}

function filter_college()
{
	var college = document.form1.college.value;
	var url="envelope.php";
	url=url+"?college=" + college;
	location = url;
	return false;
}


function handleKeyPress(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		show_envelope();
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
