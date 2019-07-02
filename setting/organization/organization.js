// Javascript functions for Weeks course format
function add()
{
	
}
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
function add_org()
{
	var orgcode =  $("#orgcode").val();
	var nameen = $("#nameen").val();
	var namear = $("#namear").val();
	var orgtype = $("#orgtype").val();
	var institute = $("#institute").val();
	var campus = $("#campus").val();
	var statust = $("#status").val();
	var id_o = $("#id_o").val();
	  var UrlToPass = 'action=add_org&orgcode='+orgcode+'&nameen='+nameen+'&namear='+namear+'&orgtype='+orgtype+'&id_o='+id_o+'&institute='+institute+'&status='+statust+'&campus='+campus;
	$.ajax({ 
			type : 'POST', 
			data : UrlToPass, 
			url  : 'add_organization.php',
			success: function(status){ 
			$("#loading").hide();  
			var status=$.trim(status); 
			if(status=='0')
			{ 
		
			alert('Organization added', 'Note', 'info', true, true);
			window.location.href="index.php";
				return false;  
			} 
				else
			{ 
							
             alert('Organization Updated', 'Note', 'info', true, true);
			window.location.href="index.php";
				return false;  
			} 
		
			}
	    }); 
}
function add_section()
{
	var orgid =  $("#orgid").val();
	var section = $("#section").val();
	var sectionen = $("#sectionen").val();
	var sectionar = $("#sectionar").val();
	var sectionty = $("#sectionty").val();
	var institute = $("#institute").val();
	var campus = $("#campus").val();
	var statust = $("#status").val();
	var id_s = $("#id_s").val();
	
	  var UrlToPass = 'action=add_section&orgid='+orgid+'&section='+section+'&sectionen='+sectionen+'&sectionar='+sectionar+'&sectionty='+sectionty+'&id_s='+id_s+'&institute='+institute+'&status='+statust+'&campus='+campus;
	$.ajax({ 
			type : 'POST', 
			data : UrlToPass, 
			url  : 'add_section.php',
			success: function(status){ 
			$("#loading").hide();  
			var status=$.trim(status); 
			if(status=='0') 
			{ 
		
			alert('Section added', 'Note', 'info', true, true);
			window.location.href="section.php";
				return false;  
			}
				else
			{ 
			 alert('Section Updated', 'Note', 'info', true, true);
			window.location.href="section.php";
				return false;  
			} 
		
			}
	    }); 
}
function add_campus()
{
	var campus =  $("#campus").val();
	var campuser = $("#campuser").val();
	var campusar = $("#campusar").val();
	var institute = $("#institute").val();
	var statust = $("#status").val();
	var id_c = $("#id_c").val();
	
	  var UrlToPass = 'action=add_campus&campus='+campus+'&campuser='+campuser+'&campusar='+campusar+'&id_c='+id_c+'&status='+statust+'&institute='+institute;
	$.ajax({ 
			type : 'POST', 
			data : UrlToPass, 
			url  : 'add_campus.php',
			success: function(status){ 
			$("#loading").hide();  
			var status=$.trim(status); 
        console.log(status);
			if(status=='0')
			{ 
		
			alert('Campus added', 'Note', 'info', true, true);
			 window.location.href="campus.php";
				return false;  
			}
				else
			{     
							
             alert('Campus Updated', 'Note', 'info', true, true);
			window.location.href="campus.php";
				return false;  
			} 
		
			}
	    }); 
}
function add_institute()
{
	var institute =  $("#institute").val();
	var instituteer = $("#instituteer").val();
	var institutear = $("#institutear").val();
	var statust = $("#status").val();
	var id_i = $("#id_i").val();
	  var UrlToPass = 'action=add_institute&institute='+institute+'&instituteer='+instituteer+'&status='+statust+'&id_i='+id_i+'&institutear='+institutear;
	$.ajax({ 
			type : 'POST', 
			data : UrlToPass, 
			url  : 'add_institute.php',
			success: function(status){ 
			$("#loading").hide();  
			var status=$.trim(status); 
			if(status=='0')
			{ 
		
			alert('Institute added', 'Note', 'info', true, true);
			 window.location.href="institute.php";
				return false;  
			}
				else
			{ 
	         alert('Institute Updated', 'Note', 'info', true, true);
			 window.location.href="institute.php";
				return false;  
			} 
		
			}
	    }); 
}
function cancelhref()
{
	document.location.href = 'index.php';
}
function search_handler() {
			  var input, filter, table, tr, td, i, txtValue; 
			  input = document.getElementById("search");
			  filter = input.value.toUpperCase();
			  table = document.getElementById("org_table");
			  tr = table.getElementsByTagName("tr");
			  for (i = 0; i < tr.length; i++) {
			  td = tr[i].getElementsByTagName("td")[0];
			  if (td) {
				txtValue = td.textContent || td.innerText;
			  if (txtValue.toUpperCase().indexOf(filter) > -1) {
					tr[i].style.display = "";
				  } else {
					tr[i].style.display = "none";
				  }
				}       
			  }
}
function delete_organization(id)
{
	if(confirm("Are you sure you want to delete the selected Organization"))
	{
		url = "index.php";
		url=url+"?idr=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}
function delete_section(id)
{
	if(confirm("Are you sure you want to delete the selected Section"))
	{
		url = "section.php";
		url=url+"?idr=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}
function delete_campus(id)
{
	if(confirm("Are you sure you want to delete the selected Campus"))
	{
		url = "campus.php";
		url=url+"?idr=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}
function delete_institute(id)
{
	if(confirm("Are you sure you want to delete the selected Institute"))
	{
		url = "institute.php";
		url=url+"?idr=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}
