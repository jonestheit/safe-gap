<?php
#header("Content-Type: text/html; charset=iso-8859-1");

// start a session 
session_start(); 

// #open the log file
// #$file = fopen("logs/log.log",  "a");
// $filecsv = fopen("logs/logcars2.csv",  "a");

// #write the local address
// $bj = @$_SERVER["SERVER_NAME"];
// if( $bj != null)
// {
  // #fwrite( $file, "<b>Site:</b> $bj <br/>");
  // fwrite( $filecsv, "$bj ,");
// }
// else {
  // fwrite( $filecsv, ",");
// }
// #reset
// $bj = null;


// #write the time of access
// $time = date("H:i dS F Y");
// #fwrite( $file, "<b>Time:</b> $time<br/>" );
// fwrite( $filecsv, "$time," );


// #write the user's IP address if available
// $bj = @$_SERVER["REMOTE_ADDR"];
// if( $bj != null)
// {
 // # fwrite( $file, "<b>IP Address:</b> $bj <br/>");
  // $bannedip = (substr($bj, 0, 10) === "208.80.195");
  // fwrite( $filecsv, "$bj,");    
// }
// else {
  // fwrite( $filecsv, ",");
// }
// #reset
// $bj = null;


// #write the URL of the forwarding page if available
// $bj = @$_SERVER["HTTP_REFERER"];
// if( $bj != null)
// {
  // #fwrite( $file, "<b>Referer:</b> $bj <br/>");
  // fwrite( $filecsv, "$bj ,");
// }
// else {
  // fwrite( $filecsv, ",");
// }
// #reset
// $bj = null;


// #write the user's browser details if available
// $bj = @$_SERVER["HTTP_USER_AGENT"];
// if( $bj != null)
// {
  // #fwrite( $file, "<b>Browser:</b> $bj <br/>");
  // fwrite( $filecsv, "$bj,");
// }
// else {
  // fwrite( $filecsv, ",");
// }
// #reset
// $bj = null;

// #blankline to separate
// #fwrite( $file, "<br/>");
// fwrite( $filecsv, "\n");

// #close the log file
// fclose($filecsv);


// #prevent some IPs from viewing
// if($bannedip > 0) {
	// header("HTTP/1.0 404 Not Found");
	// exit;
// }


include("cars_inks/connsql.php");
/*
	#connect to MySQL
	$conn = @mysql_connect("213.171.200.62", "webuser", "traffic1")
		or die("Could not connect");
		
	#select the specified database
	$db = @mysql_select_db("bj20080527", $conn) 
		or die ('Can\'t use selected database : ' . mysql_error());
*/




function thinktime() {
	#not called too often - maybe inefficient - attempt at skewed normal bell curve
	$array = array(0.7,0.8,0.9,1.0,1.0,1.1,1.1,1.1,1.2,1.2,1.2,1.2,1.3,1.3,1.3,1.4,1.4,1.5,1.5,1.6,1.7,1.8,1.9,2.0,2.1,2.2,2.3,2.4,2.5);
	return $array[rand(0, 28)];
}

$sessid = substr(session_id(),0,20);

$msgcars="open";

if ($sql_effect !="emailsent") {
  #Of all things, sessid must be unique - find current session or allocation session to a new slot

  $sql = "select fldID, fldEmail, fldGap from tbldrivers where fldSession = \"$sessid\";";
//echo "sql=".$sql;
  $result = @mysql_query($sql,$conn)
		        or die("Could not execute query1" );
				
  if($result) { 
    $row = @mysql_fetch_array($result);

	if ($row['fldEmail']) {	
		$email=$row['fldEmail'];
		$gapsecs=$row['fldGap'];
		//20090825
		$thisphpid=$row['fldID'];
	} else {
		$sql_effect="need_to_login";
	}
  }
}

if ($sql_effect=="need_to_login") {
	$msgcars = "Need to login";
	#look for the most recent space vacated in the last 2 minutes - hopefully keeps all online in a bunch
	$sql="select fldID, fldTimeLast from tbldrivers where SUBTIME(Now(),'00:02:00')>fldTimeLast and (fldID > 20) order by fldTimeLast desc limit 1";

	    $result = @mysql_query($sql,$conn)
		        or die("Could not execute query3");

	    if($result) { 

			#get thinktime from array above
			$tt=thinktime();
		
		    $row = @mysql_fetch_array($result)
				 or die("Could not execute query - no slot available");	

			#for use in continuous communication with server
			$thisphpid=$row['fldID'];

			$IP=@$_SERVER["REMOTE_ADDR"];	

			#$email= substr($sessid,2,8);
			$email = "Driver A".rand(1,999);
			#$gapsecs=2;	
			#actually gapsecs can also be selected from that array - almost, so:
			$gapsecs=thinktime() + 0.3;	
			
			$sql = "update tbldrivers set fldEmail=\"$email\", fldReactionTime=\"$tt\", fldGap=\"$gapsecs\", fldTimeStart=Now(), fldSession=\"$sessid\", fldIP=\"$IP\" where fldID=".$row['fldID'];

		    $result = @mysql_query($sql,$conn)
			        or die("Could not execute query4a");
		
				$msgcars ="<br>You have been allocated car " . $row['fldID']. " and a name <strong>" . $email .  "</strong>. You can change the name and desired gap ... ";
				$sql_effect="emailsent"; //to signify success
		} else {
				$msgcars = "New applicant could not be added.";
		}
	}

#by now we should have opened

?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>

<style>

.colhead {
	font: bold 15px arial, sans-serif;
}

</style>


<script type="text/javascript">
//<script type="text/javascript">
//script block begin 1 of 4 (was 5 until January 2017 when I got rid of sliders because they didn't work on tablet)

//To keep messages scrolled down
// <![CDATA[
function scrollToBottom(elm_id) {
	var elm = document.getElementById(elm_id);
	try
		{
		elm.scrollTop = elm.scrollHeight;
		}
	catch(e)
		{
		var f = document.createElement("input");
		if (f.setAttribute) f.setAttribute("type","text")
		if (elm.appendChild) elm.appendChild(f);
		f.style.width = "0px";
		f.style.height = "0px";
		if (f.focus) f.focus();
		if (elm.removeChild) elm.removeChild(f);
		}
}
// ]]>



var mouseDown = 0;
function handleDown(e) {
	mouseDown = 1;
}

function handleOut(e) {
	mouseDown = 0;
}

// to scroll messages
function scrollMessages() {
	if(mouseDown==0) {
		scrollToBottom('msg_received');
	}
}

var thisid=<?php echo $thisphpid; ?>;
var carsinfront=<?php echo rand(10, 25); ?>; 
var xmlHttp

function showUser(str) { 
	//(str) thisid (id in the table) is used to update the time for this user in the table so that he remains current
	//str not used from 26 June 2009
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	 {
	 alert ("Browser does not support HTTP Request")
	 return
	 }

	var frontcar = (thisid - carsinfront);
	var queryString = "?frontcar=" + frontcar + "&thisid=" + thisid + "&sessid=<?php echo $sessid;?>";

	var url="responsexml.php"
		url=url+queryString+"&sid="+Math.random();
	
	xmlHttp.onreadystatechange=stateChanged 
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
	//alert(url);	 
}



function send_message(dataSource) { 
	var XMLHttpRequestObject = false; 
		
	if (window.XMLHttpRequest) {
	  XMLHttpRequestObject = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
	  XMLHttpRequestObject = new 
		ActiveXObject("Microsoft.XMLHTTP");
	}
	
	var to_driverID = document.getElementById('msg_to').value;
		to_driverID = thisid - carsinfront - 1 + parseInt(to_driverID);
	 
	var msg_text = document.getElementById('msg_text').value;
	var queryString = "&tdid=" + to_driverID + "&sdid=" + thisid +"&msg_text=" + msg_text +"&sessid=<?php echo $sessid;?>";
	var url="update_msg.php"
	url=url+"?q="+queryString;	
	url=url+"&sid="+Math.random();
	
	document.getElementById("for_msg").innerHTML="Write a message above";
	document.getElementById("msg_text").value="";
		
	if(XMLHttpRequestObject) {
	  XMLHttpRequestObject.open("GET", url); 
	  XMLHttpRequestObject.onreadystatechange = function() 
	  { 
		if (XMLHttpRequestObject.readyState == 4 && 
		  XMLHttpRequestObject.status == 200) { 
			//document.getElementById("targetDiv").innerHTML =
			  //XMLHttpRequestObject.responseText; 
			delete XMLHttpRequestObject;
			XMLHttpRequestObject = null;
		} 
	  } 
	  XMLHttpRequestObject.send(null); 
	}
}





function sendMsg() {
	if (document.getElementById('msg_to').value=="") {
		document.getElementById("for_msg").innerHTML="choose who to send to ";
		return true;
	} else {
		document.getElementById("for_msg").innerHTML="<a href=javascript:send_message()>Send Messsage</a>";
		return true;
	}
}




function stateChanged() { 
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
	{
		xmlDoc=xmlHttp.responseXML;
		 
		var alldrivers = xmlDoc.getElementsByTagName("alldrivers");
		 //checking
		//document.getElementById("firstname").innerHTML=alldrivers.length;
		 
		var driver = alldrivers[0].getElementsByTagName("driver");
		 //checking 
		//document.getElementById("lastname").innerHTML=driver.length;
		 
		var ndrivers = 70;
		 
		var myemail=document.getElementById("email").value;		//xyft.email.value;
		var mysessid=document.getElementById("sessid").value;	//xyft.sessid.value;
		var mygapsecs=document.getElementById("gapsecs").value;
		var driveremailhtm="";
		var driverreactiontimehtm="";
		var drivergapsecshtm="";
		var currentnesshtm="";
		var driversessidhtm="";

		var i = 0;
		var table_drivers = new String("<table border='1'>");
		var table_drivers2 = new String("<table border='1'>");
		var table_messages = new String("<table border='1'  class='tright'>");
		
		//added 20090415 to get the messages
		var messages_for_me = alldrivers[0].getElementsByTagName("messages_for_me");
		var messagehtm = "";
		var imsg=messages_for_me.length;
		
		
		// //////////  MAKE TABLE OF MESSAGES ////////////////
		if(imsg>0){
			var ibase0 = 0;		
			//here we need ibase0 at zero because the xml stuff starts at zero
			for (i = 1; i <= imsg; i++ ) {
				ibase0 = i - 1;
				var n_otherparty = messages_for_me[ibase0].getElementsByTagName("senderid")[0].childNodes[0].nodeValue - thisid + carsinfront + 1;
				//only show the message if the other driver appears between 2 and 50
				if((n_otherparty > 1) && (n_otherparty < 51)) {		
					if(messages_for_me[ibase0].getElementsByTagName("sessid")[0].childNodes[0].nodeValue=='<?php echo substr(session_id(),0,20)?>'){
						messagehtm = "<i>" + messages_for_me[ibase0].getElementsByTagName("senttime")[0].childNodes[0].nodeValue; 
						messagehtm = messagehtm + "<br>me";
						
						if(messages_for_me[ibase0].getElementsByTagName("senderid")[0].childNodes[0].nodeValue==messages_for_me[ibase0].getElementsByTagName("targetid")[0].childNodes[0].nodeValue) {
							messagehtm = messagehtm + "&nbsp;to: me</i><br>"; 
						} else {
							nnn = messages_for_me[ibase0].getElementsByTagName("targetid")[0].childNodes[0].nodeValue - thisid + carsinfront + 1;
							messagehtm = messagehtm + "&nbsp;to: " + messages_for_me[ibase0].getElementsByTagName("targetname")[0].childNodes[0].nodeValue + " (" + nnn + ")</i><br>"; 
						}
						
						messagehtm = messagehtm + messages_for_me[ibase0].getElementsByTagName("message")[0].childNodes[0].nodeValue; 				
						table_messages += "<tr><td>" + messagehtm  + "</td></tr>";			
					} else {
						messagehtm = "<i>" + messages_for_me[ibase0].getElementsByTagName("senttime")[0].childNodes[0].nodeValue; 
						messagehtm = messagehtm + "<br>from:";
						messagehtm = messagehtm + "&nbsp;"+messages_for_me[ibase0].getElementsByTagName("sendername")[0].childNodes[0].nodeValue + " (" + n_otherparty + ")</i><br>"; 
						messagehtm = messagehtm + messages_for_me[ibase0].getElementsByTagName("message")[0].childNodes[0].nodeValue; 				
						table_messages += "<tr><td style='color:black;background-color:#ffff66'>" + messagehtm  + "</td></tr>";
					}
				}
			}
			table_messages += "</table>";
			document.getElementById("msg_received").innerHTML = table_messages;
		}
		// //////////  END OF MAKE TABLE OF MESSAGES ////////////////		
		
		
		// /////////////////////  make 2 tables here for display on the page //////////////////////////////////////////
		table_drivers += "<tr><th>Position</th><th>Driver</th><th>Reaction Time (secs)</th><th>Desired Gap (secs)</th><th>Online</th></tr>";
		table_drivers2 += "<tr><th width='15%'>Pos</th><th width='70%'>Driver</th><th width='15%'>Gap</th></tr>";

		//reset
		ibase0 = 0;		
		//here we need ibase0 at zero because the xml stuff starts at zero
		for (i = 1; i <= ndrivers; i++ ) {	

			ibase0 = i - 1;
			driveremailhtm=driver[ibase0].getElementsByTagName("email")[0].childNodes[0].nodeValue; 
			driverreactiontimehtm=driver[ibase0].getElementsByTagName("reaction_time")[0].childNodes[0].nodeValue; 
			driversessidhtm=driver[ibase0].getElementsByTagName("session")[0].childNodes[0].nodeValue;
			
			drivergapsecshtm=driver[ibase0].getElementsByTagName("gapsecs")[0].childNodes[0].nodeValue;
			currentnesshtm=driver[ibase0].getElementsByTagName("currentness")[0].childNodes[0].nodeValue;		

			if (currentnesshtm==1) {
				var str2="<div style='background:#ffffff;color:red'><small>"+i+"</small></div>";
				document.getElementById("flag"+i).innerHTML = str2;					
			} else {
				var str2="<div style='color:black'><small>"+i+"</small></div>";
				document.getElementById("flag"+i).innerHTML = str2;	
			}			
			
			//capture this for display in its own space on the page
			if (driversessidhtm == mysessid) {
			
				//just pop this in here for use on the messaging form
				//document.getElementById("sdid").value = i;			
			
				pos_htm="<span style='color:black;background-color:#ffff66'>" + i + "</span>";

				document.getElementById("sys_pos").innerHTML = pos_htm;					
				document.getElementById("sys_email").innerHTML = driveremailhtm;
				document.getElementById("sys_gapsecs").innerHTML = drivergapsecshtm;
				document.getElementById("sys_reaction_time").innerHTML = driverreactiontimehtm;			
			
				//now change it for display
				driveremailhtm="<font color='red'>" + driveremailhtm + "</font>";
				
				var str2="<div style='background:#ffff66;color:black'><small>"+i+"</small></div>";
				document.getElementById("flag"+i).innerHTML = str2;	
			}
			
			if(currentnesshtm==1) {
				table_drivers += "<tr><td>" + i + "</td><td>" + driveremailhtm  + "</td><td>" + driverreactiontimehtm + "</td><td>"  + drivergapsecshtm + "</td><td align='center'>yes</td></tr>";
			} else {
				table_drivers += "<tr><td>" + i + "</td><td>" + driveremailhtm  + "</td><td>" + driverreactiontimehtm + "</td><td>"  + drivergapsecshtm + "</td><td>&nbsp;</td></tr>";
			}
			
			
			if(currentnesshtm==1) {
				table_drivers2 += "<tr bgcolor=\'#FFFFFF\' onMouseOver=\"this.bgColor=\'gold\';\" onMouseOut=\"this.bgColor=\'#FFFFFF\';\" onclick=\"highlight(this);\"><td>" + i + "</td><td>" + driveremailhtm  + "</td><td>"  + drivergapsecshtm + "</td></tr>";
			}

			//update array values
			cars[i].gapsec=driver[ibase0].getElementsByTagName("gapsecs")[0].childNodes[0].nodeValue;
		}
		table_drivers += "</table>";
		document.getElementById("id_table_drivers").innerHTML = table_drivers;
		
		table_drivers2 += "</table>";
		document.getElementById("table_drivers2").innerHTML = table_drivers2;
		
	 }
	// END OF MAKING TABLES  ////////////////////////////////////////////////
	
	//and this
	scrollMessages('msg_received');
}



function GetXmlHttpObject() {
	var xmlHttp=null;
	try
	 {
	 // Firefox, Opera 8.0+, Safari
	 xmlHttp=new XMLHttpRequest();
	 }
	catch (e)
	 {
	 //Internet Explorer
	 try
	  {
	  xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
	  }
	 catch (e)
	  {
	  xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	 }
	return xmlHttp;
}




var t
function timedCount() {
	showUser("<?php echo($thisphpid); ?>");
	t=setTimeout("timedCount()",15000);
}





function mouseOverFlag(i) {
	var flagi="flag"+i;
	var st=document.getElementById(flagi).innerHTML;
	if(st.indexOf('red')!=-1){
		//returns -1 if no match found.
		document.getElementById(flagi).innerHTML="poke "+i;
	}
}


function mouseOutFlag(i) {
	var flagi="flag"+i;
	var st=document.getElementById(flagi).innerHTML;
	if(st.indexOf('poke')!=-1){
		//returns -1 if no match found.
		document.getElementById(flagi).innerHTML="<div style='background:#ffffff;color:red'><small>"+i+"</small></div>"	
	}
}




function get_driver_name(whoid) { 
	var n_otherparty = thisid - carsinfront - 1 + whoid;
	var XMLHttpRequestObject = false; 

	if (window.XMLHttpRequest) {
	  XMLHttpRequestObject = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
	  XMLHttpRequestObject = new 
		ActiveXObject("Microsoft.XMLHTTP");
	}
	
	var url="get_drivertxt.php?whoid=" + n_otherparty + "&sessid=<?php echo $sessid;?>";	
	url=url+"&sid="+Math.random();
		
	if(XMLHttpRequestObject) {
	  XMLHttpRequestObject.open("GET", url); 
	  XMLHttpRequestObject.onreadystatechange = function() 
	  { 
		if (XMLHttpRequestObject.readyState == 4 && 
		  XMLHttpRequestObject.status == 200) { 
			element_msg_to_name.innerHTML=XMLHttpRequestObject.responseText; 	
			delete XMLHttpRequestObject;
			XMLHttpRequestObject = null;
		} 
	  } 
	  XMLHttpRequestObject.send(null); 
	}
}


function pokeFlag(i) {
	var flagi="flag"+i;
	var st=document.getElementById(flagi).innerHTML;	
	if(st.indexOf('poke')!=-1){
		//returns -1 if no match found.
		element_msg_to.value=i;
		//20090701 get the actual name for responsetxt
		element_msg_to_name.style.backgroundColor = "#00FF00";
		//element_msg_to_name.innerHTML=get_drivertxt.php?whoid=i; //"Driver "+i;
		get_driver_name(i);		
		setTimeout(highlighter, 500);
	}
}

function zupdateFlag(i) {
	var flagi="flag"+i;
	var st=document.getElementById(flagi).innerHTML;
	if(st.indexOf('poke')!=-1){
		//returns -1 if no match found.
		var myurl="add_comment.php?me="+escape(document.forms.xyft.email.value)+"&sessid="+document.forms.xyft.sessid.value+"&target="+i;
		myRef = window.open(myurl,'hiya','left=50,top=50,width=500,height=300,toolbar=0,resizable=0');	
	}
}

//<!-- end block 1 -->
</script>

	<title>Motorway traffic flow - share the road with online drivers</title>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<meta name="Description" content="Traffic flow on a motorway. Communicate with other drivers online. Vary the speed and traffic density to see compression wave effects.">
	<meta name="keywords" content="motorway, traffic flow, traffic density, safe gap, safe distance, stopping distance, braking, wave, driver reaction, reaction time, thinking time">
	<meta name="copyright" content="Copyright © 2009 - 2017 Brian Jones, All Rights Reserved">
	<link rel="shortcut icon" href="./favicon.ico">
	<link href='./cars.css' type='text/css' rel='stylesheet'>
	
</head>

<body bgcolor='FFFFFF'>


<noscript>

	<h2>Your browser does not support JavaScript!</h2>
	<p><strong><font color='red'>
	<h3>This illustration will only work if JavaScript is enabled in your browser.</h3>
	For Internet Explorer this can be enabled from Tools > Internet Options > Security > Custom Level > Active Scripting and choose Enable.<br>
	For Firefox this can be enabled from Tools > Options > Content and tick the Enable JavaScript box.<br>
	For Opera this can be enabled from Tools > Preferences > Advanced > Content and tick the Enable JavaScript box.
	</font></strong></p>

</noscript>


<script type="text/javascript">
//<script type="text/javascript">
//script block begin 3 of 5 (2 is gone)
<!--
var cyclecount = 0; //equivalent to static in function animate
var Xpos = 0;
var Ypos = 0; //top of road
var RoadWidth = 68;

var m_to_ft = 3.28084;



function buttonColorClear(vClass) {
    var buttons = document.getElementsByClassName(vClass);

    for (var J = buttons.length - 1;  J >= 0;  --J) {
        var btn = buttons[J];		
		btn.style.backgroundColor = "Lightgrey";
    }
}



function DriveAspiredSpeed(vSpeed, vID) {
	ASPIRED_SPEED = vSpeed;
	showdanger();
	buttonColorClear("SpeedButton");
	document.getElementById(vID).style.backgroundColor = "Yellow";
}

function Grip(vGrip, vID) {
	BRAKING_DISTANCE_FACTOR = vGrip;
	showdanger();
	buttonColorClear("GripButton");
	document.getElementById(vID).style.backgroundColor = "Yellow";
}

function VehiclesPerMinute(vVPM, vID) {
	CARS_PER_MINUTE = vVPM;
	showdanger();
	buttonColorClear("VPMButton");
	document.getElementById(vID).style.backgroundColor = "Yellow";
}


function DriveLeft() {
	CarOffsetY = 27;
	
		// e.g.
		// var el = document.getElementById(id);
		// el.style.color = "red";
		// el.style.fontSize = "15px";
		// el.style.backgroundColor = "#FFFFFF";

	document.getElementById("btnDriveLeft").style.backgroundColor = "Yellow";
	document.getElementById("btnDriveRight").style.backgroundColor = "Lightgrey";
}

function DriveRight() {
	CarOffsetY = 18;

	document.getElementById("btnDriveLeft").style.backgroundColor = "Lightgrey";
	document.getElementById("btnDriveRight").style.backgroundColor = "Yellow";
}

//offset 27 for driving on the left
//offset 18 for driving on the right


var CarOffsetY = 27;

	//The road . The road moves according to the speed/acceleration of car0
	//called dashes because the notion was to show road markings, but this evolved into pictorial motorway.
var nDashes = 3;

	//22.3mph = 10mps    say 1 metre is a pixel
	//30 m/s = 67mph = 7108kph
	//20 m/s = 45 mph = 72 kph
	//The value of 30 initialised gets set immediately to the slider value (20 as at 20090322) - thus ignored here
var SPEED = 30;
var ASPIRED_SPEED = 30;

	//calculation interval in secs
var DELTAT = 0.02;

var DASHSIZE =1400;
	//recalc road position every n seconds
var DASH_RECALC = DELTAT;
var n_dash_spacing = 1400;

//GAP_MULTIPLIER multiplies standard gapsec (mean 1 sec) therefore GAP_MULTIPLIER 2 makes an average gap of 2 second and GAP_MULTIPLIER 10 makes a gap of 10 secs
//not used after December 2008 but reintroduced March 2009 on 4th slider
//And changed from a default value of 2. And used in a different way than earlier versions
var GAP_MULTIPLIER = 1;
//Note that CARS_PER_MINUTE will immediately assume slider value
var CARS_PER_MINUTE = 30;

//BRAKING_DISTANCE_FACTOR a value less than 1 that reduces braking performance in the wet
var BRAKING_DISTANCE_FACTOR = 1;

//REACTION_TIME_FACTOR increases reaction time in poor visibility - say by 10% in dark and rain
var REACTION_TIME_FACTOR = 1;

var isNetscape = navigator.appName=="well Firefox reports as Netscape but behaves like IE";//"Netscape";
var isOpera = navigator.appName=="Opera";

var ncars = 70;
var SigmaL = 0; //total lenght of cars - metres
var SigmaG = 0; //total gap in seconds
var g = 9.81; //m/s/s notional g force used for max and min decelleration of the lead car and therefore the road

var w = new Array(); //w is width on screen = length of car should be between 2 and 12 m long
var cars = new Array();
var dashes = new Array();

var carspeed = new Array();  //current speed of car
var accel = new Array();     //acceleration per car
var decel = new Array();     //deceleration per car
var thinkmax = new Array();  //how long the driver needs to thing before reacting
var thinknow = new Array();  //how long has driver been thinking
var gapsec = new Array();    //intended gap in seconds

/*
make car lengths = w
base on random length somewhere around 4 metres long but not less than 2
writes the page layout div tags
*/
initw();
function initw() {
 //whilst before 20080709 this originally initiated all these variables later they should be revalued from a SQL table
 //see makecars below
	var i = 0;
	for (i = 1; i <= ncars; i++) {
		carspeed[i] = SPEED;
		
		//g=9.81 m/s/s - assume no-one accelerates more than 2 or decel more than 7
		accel[i] = 2;
		decel[i] = 7;
		
		//reaction time in seconds 0.7 is fastest possible - say 1.25 is middle (0.7+ 0.55)
		thinkmax[i] = 0.7; //+ ( GaussRandom(11) / 10 );
		thinknow[i] = 0;	//how long this driver has been thinking before reacting
	}
}


//write the car divs
makecardivs();
function makecardivs() {
//first just the DIVs
	var i = 0;
 	for (i = 1; i <= ncars; i++) {
 		document.write("<div id='car" + i + "' style='position: absolute; height: 11; width: 10; z-index: 1;'></div>");
 	} 
}



//remakes the cars and their colors and resets other variables
makecarsfromtable();
function makecarsfromtable() {
	<?php

		$ncars=70;
		
		for ($i = 1; $i <= $ncars; $i++) {
		
			// execute the query	
			$sql="SELECT tbldrivers.fldColor, tbldrivers.fldWidth, tbldrivers.fldEmail, tbldrivers.fldGap FROM tbldrivers WHERE tbldrivers.fldID = ".$i;
		
			$rs=mysql_query($sql) 
				or die("Could not execute query");
		
			$row = mysql_fetch_row($rs);
		    if (!$row) {
		        echo "No information available<br />\n";
		    } else {
				echo "var element = document.getElementById('car".$i."');\n"; 
				
				echo "element.innerHTML = \"<div id='flag".$i."' onmouseover='mouseOverFlag(".$i.")' onmouseout='mouseOutFlag(".$i.")' onclick='pokeFlag(".$i.")'><small>$i</small></div><img src='car".$row[0].".gif'  width=".$row[1]." height=3 alt=".$row[2].">\";\n";

				echo "w[".$i."]=".$row[1].";";
				echo "SigmaL +=".$row[1].";";
				echo "gapsec[".$i."]=".$row[3].";";
				echo "SigmaG +=".$row[3].";";		
			}
		}
	?>
}


function showdanger() {
	var V = CARS_PER_MINUTE;
	var S = ASPIRED_SPEED; 
	var G = BRAKING_DISTANCE_FACTOR; 
	
	//empirically guessed
	var d = (V/(11*G))*(0.8+(S/100));
	d = Math.round(d);
	
	
	if (d > 20) d = 20;
	
	var i = 0;
	var danger = "<table width=\"100%\" border=\"1\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" bordercolor=\"#FFFFFF\"><tr>";
		danger += "<td colspan=\"20\"><font face=\"Arial, Helvetica, sans-serif\" size=\"1\">Danger level</font></td></tr><tr>";
	
		for (i = 1; i <= d; i++) {
			danger += "<td bgcolor=\"#FF0000\">&nbsp;</td>"
		}
		
		
		for (i = d+1; i <= 20; i++) {
			danger += "<td bgcolor=\"#00FF00\">&nbsp;</td>"
		}
	
	danger +="</tr></table>";
	
	document.getElementById("danger").innerHTML=danger;
}

//end block 3 -->
</script>



<DIV ID="page" align="center">

<!--   20080130   use pictures for motorway movement -->

<div id="dash0" style="position: absolute; height: 11; width: 11;"><img src="motorwaylong.gif" height=68 width=1400></div>
<div id="dash1" style="position: absolute; height: 11; width: 11;"><img src="motorwaylong.gif" height=68 width=1400></div>
<div id="dash2" style="position: absolute; height: 11; width: 11;"><img src="motorwaylong.gif" height=68 width=1400></div>
<div id="dash3" style="position: absolute; height: 11; width: 11;"><img src="motorwaylongstill.gif" height=6 width=1400></div>

<!-- maintable0 determines where the road appears - underneath -->
  <table width="95%" border="0" cellspacing="2" cellpadding="2" ID="maintable0">
    <tr> 
      <td><div align="center"><font size="4"><a name="top">Traffic flow illustration. How a stream of vehicles responds to varying speed, traffic density and conditions. Share with others.</a></font></div></td>
    </tr>
  </table>
  
  <br><br><br><br><br>

  <table border="1" ID="maintable0x">
    <tr>
		<td width="200px" valign="top">
		<div align="center"><font size="-3">Online (within last 2 minutes)</font></div>
				
		<div id="table_drivers2" class="selectable" style="width:200px; height:465; z-index:0; background-color: #FFFFFF; border: 1px none #000000; overflow: scroll;">tabl</div>
		
		<div align="center"><font size="-3"><a href="#backtotop">see all drivers, past and present</a></font></div>
				
		</td>
		
		
      <td valign="top"> 
        <table border='0' cellspacing='0' cellpadding='70' background='img/satnav.jpg'WIDTH="690" HEIGHT="490">
		
			<tr>
				<td>
				<table border='1' cellspacing='0' cellpadding='6' ID="maintable" WIDTH="528" HEIGHT="328">
                <tr> 
                  <td rowspan="3">&nbsp;&nbsp;</td>
                  <td class="colhead">Lead</td>
				  <td class="colhead">Grip</td>
				  <td class="colhead">VPM</td>
				  <td class="colhead">&nbsp;</td>
                </tr>
                <tr> 
					<td colspan="3"> 
						<form name="AspiredSpeed">
						  <table>
							<tr> 
								<td valign="top"> 
									<input id="txtSpeed" type="Text" size="4"> 

									<input type="button" onclick="DriveAspiredSpeed(36, this.id)" value="80 mph" id="button36" class="SpeedButton">
									<input type="button" onclick="DriveAspiredSpeed(29, this.id)" value="65 mph" id="button29" class="SpeedButton">
									<input type="button" onclick="DriveAspiredSpeed(20, this.id)" value="45 mph" id="button20" class="SpeedButton">
									<input type="button" onclick="DriveAspiredSpeed(15, this.id)" value="33 mph" id="button15" class="SpeedButton">
									<input type="button" onclick="DriveAspiredSpeed(10, this.id)" value="22 mph" id="button10" class="SpeedButton">
								</td>
								<td valign="top"> 
									<input id="txtSurface" type="Text" size="3">
								
									<input type="button" onclick="Grip(1.0, this.id)" value="Dry" id="buttongrip10" class="GripButton">
									<input type="button" onclick="Grip(0.7, this.id)" value="Damp" id="buttongrip07" class="GripButton">
									<input type="button" onclick="Grip(0.4, this.id)" value="Wet" id="buttongrip04" class="GripButton">
									<input type="button" onclick="Grip(0.1, this.id)" value="Icy" id="buttongrip01" class="GripButton">
								
								</td>
								<td valign="top"> 
									<input id="txtVPM" type="Text" size="2"> 
								
									<input type="button" onclick="VehiclesPerMinute(40, this.id)" value="40 VPM" id="buttonVPM40" class="VPMButton">
									<input type="button" onclick="VehiclesPerMinute(35, this.id)" value="35 VPM" id="buttonVPM35" class="VPMButton">
									<input type="button" onclick="VehiclesPerMinute(30, this.id)" value="30 VPM" id="buttonVPM30" class="VPMButton">
									<input type="button" onclick="VehiclesPerMinute(25, this.id)" value="25 VPM" id="buttonVPM25" class="VPMButton">
									<input type="button" onclick="VehiclesPerMinute(20, this.id)" value="20 VPM" id="buttonVPM20" class="VPMButton">
								</td>
							</tr>
						  </table>
						</form>
					</td>
                  <td valign="top" rowspan="1"> <form name="SpeedNow">
                      <p> 
                        <input id="btnDriveLeft" type="button" onclick="DriveLeft()" value="Drive Left" >
                        <input id="btnDriveRight" type="button" onclick="DriveRight()" value="Drive Right" >

                      </p>
                      <font face="Arial, Helvetica, sans-serif" size="1"><span id="comfort">comfort<br><br><br><br></span></font> 
                    </form>
					<div id="danger">
						<table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF"><tr>
						<td colspan="20"><font face="Arial, Helvetica, sans-serif" size="1">Danger level</font></td></tr><tr>
						<td bgcolor="#FF0000">&nbsp;</td>
						<td bgcolor="#FF0000">&nbsp;</td>
						<td bgcolor="#FF0000">&nbsp;</td>
						
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						<td bgcolor="#00FF00">&nbsp;</td>
						</tr></table>
					</div>
				</td>
                </tr>
                <tr> 
					<td colspan="3" valign="top"> <TABLE CLASS=formtab >
						  <TR> 
							<td>Position:</td>
							<TD><span id="sys_pos">&nbsp;</span></TD>
						  </TR>
						  <TR> 
							<td>Name:</td>
							<TD><span id="sys_email">&nbsp;</span></TD>
						  </TR>
						  <TR> 
							<td>Gap:</td>
							<TD><span id="sys_gapsecs">&nbsp;</span></TD>
						  </TR>
						  <TR> 
							<td>Reaction:</td>
							<TD><span id="sys_reaction_time">&nbsp;</span></TD>
						  </TR>
						</TABLE>
					</td>
					<td colspan="1"> 
						<FORM NAME="xyft">
						  <TABLE Class="formtab" BORDER="0">
							<TR> 
							  <TD> &nbsp; <INPUT TYPE=hidden NAME="sessid" ID="sessid" VALUE="<?php echo($sessid); ?>"> 
							  </TD>
							  <TD> <span id="forupdate" align="center">You can change 
								your name and gap</span> </TD>
							</TR>
							<TR> 
							  <TD> Your name </TD>
							  <TD><INPUT TYPE=text NAME="email" ID="email" SIZE="25" MAXLENGTH="20" VALUE="<?php echo($email); ?>"
									 ONKEYUP="updateMe();"> </TD>
							</TR>
							<TR> 
							  <TD> Gap to leave (secs)<br>
								(between 0.2 and 4) </TD>
							  <TD><INPUT TYPE=text NAME="gapsecs" ID="gapsecs" SIZE="25" MAXLENGTH="4" VALUE="<?php echo($gapsecs); ?>"
									 ONKEYUP="updateMe();"> </TD>
							</TR>
						  </TABLE>
						</FORM>
					</td>
                </tr>
              </table>
</td>


</tr>

</table>


		
      	<td width="200px" valign="bottom"> 
	  
		  <div id="msg_received" style="position:relative; width:200px; height:380; z-index:0; background-color:#FFFFFF; border:1px none #000000; overflow:scroll;" onmousedown="handleDown(event);" onmouseout="handleOut(event);">messages</div>
			  
			
        <div class="tright"> 
          <input type="hidden" name="tdid"  id="msg_to">
          
		  <!--
		  <input type="hidden" name="sdid"  id="sdid">
		  -->
		  
				<span>Send a message to:</span><br>
				<span id="msg_to_name">[ select from table on left or click a flag ]</span>					
				<textarea name="msg_text" id="msg_text" cols="22" rows="3" ONKEYUP="sendMsg();"></textarea>
				<span id="for_msg" align="center">Write a message above</span>
			</div>

		</td>
	</tr>
</table><!-- end of maintable0x -->


<br><br>

<table border='0' cellspacing='0' cellpadding='6'  ID="lowertable" width='100%'>
<tbody>

<tr>
        <td width='50%' valign='top'> 
          <p><FONT FACE="Arial, Helvetica, sans-serif" SIZE="2"> <b>This motorway simulation 
            allows other online drivers to alter their desired gaps. You can communicate 
            with other drivers in the stream by sending them messages. Please 
            be polite.</b> </FONT></p>

          <table width="75%" border="0" align="center" cellpadding="2" cellspacing="2" bgcolor="#FFFFCC">
            <tr>
    <td><p><font color="#006600" size="2" face="Arial, Helvetica, sans-serif">If 
                  there are no other flagged drivers online, you can either get 
                  your friends online or simulate the effect by visiting this 
                  url in two different browsers, <br>
e.g. Internet Explorer and Firefox. <br>
                  You can also test the message sending facility by clicking the 
                  driver in the table on the left. </font></p></td>
  </tr>
</table>


			
			
			
          <p><FONT FACE="Arial, Helvetica, sans-serif" SIZE="2">
<font size="+2"><strong>Factors in this traffic illustration</strong></font></FONT> 
		  
		  <p><FONT FACE="Arial, Helvetica, sans-serif" SIZE="2"><strong><font size="+1">Tracking 
            speed</font></strong></br> To keep the action on screen the camera 
            moves to keep pace with the lead car; effectively this is the road 
            speed &#8211; slower cars dropping behind and faster cars catching 
            up. Your vehicle has a yellow flag and may slip off the screen if 
            the lead car gets too far ahead.<br>
            <br>
            <strong><font size="+1">Traffic Flow</font></strong></br> Traffic 
            flow is measured here in vehicles per minute. You can adjust the circumstantial 
            traffic flow using the slider. This way you can simulate the effect 
            sudden changes caused by vehicles joining or leaving the flow from 
            junctions.</FONT></p>

          <p><FONT FACE="Arial, Helvetica, sans-serif" SIZE="2"><strong><font size="+1">The 
            safe gap</font></strong><br>
            If all vehicles could maintain a 2 second gap the flow would be just 
            under 30 vehicles per minute. The vehicles you find in your stream 
            will have a random mix of desired gaps, ranging from 0.2 (crazy) to 
            4 (very safe) seconds. By adjusting the flow, say from 30 VPM to 60 
            VPM, you would force all drivers to compromise their desired gap to 
            accommodate the new circumstance. This would cause the &quot;comfort 
            factor&quot; to be changed from 100% to 50%. The &quot;comfort factor&quot; 
            is a notional measure intended to indicate the variation from the 
            driver's preferred gap. Your desired safe gap may be compromised by 
            events beyond your control.<br>
            <br>
            Some opinions assert that a gap of 2 car lengths should be maintained. 
            A few moments thinking about this will easily show that this advice 
            is flawed. The safe distance to be maintained will depend on speed. 
            The faster you travel the more distance you will need to be able to 
            react and slow down.<br>
            <br>
            <strong><font size="+1">Grip</font></strong></br> The ability of cars 
            to brake in response to an emergency is determined by the grip available. 
            In dry conditions we'll assume this is 100%. Grip reduces through 
            damp and wet conditions to about 10% in icy conditions. <br>
            </FONT></p>
          </td>
<td valign='top' width='50%'>
<p><FONT FACE="Arial, Helvetica, sans-serif" SIZE="2">
<b>This illustration cannot accurately represent all the factors that affect a stream of traffic -
use it only as an aid to understanding your own experiences.</b>
</FONT></p>

<p>
<strong><font size="+1">How the illustration has been programmed</font></strong><br>


<br>
            <font size="2">70 vehicles are randomly created between between 2m 
            and 7m long with most being about 4m long.<br>
            <br>
            Drivers are randomly created to have reaction times between 0.7 seconds 
            and 2.5 seconds with most being around 1.25 seconds. This is the thinking 
            time plus the time to move between accelerator and brake. Drivers 
            cannot change their reactions times, only their desired gaps. <br>
            <br>
            Because the vehicles and drivers are created randomly, every time 
            you visit this website you get a different set of vehicles and drivers. 
            The overall behaviour of the traffic will be different each time.<br>
            <br>
            The lead car can accelerate at 1.0g and decelerate at 1.0g<br>
            All other vehicles can accelerate at 0.2g and decelerate at 0.7g<br>
            <br>
            Drivers do not start braking behind the vehicle in front until they've 
            come within range. That is, they do not even contempate slowing down 
            if they are more than 5 times their intended gap.<br>
            <br>
            The maximum speed of the road is 89mph - no vehicles can travel faster 
            than 89mph. This may give an unrealistic illustration at higher road 
            speeds because following cars can't actually catch the lead car if 
            it's doing 89 mph.<br>
            <br>
            The traffic stream is declared steady when all cars are within 2mph 
            of the road speed. <br>
            <br>
            Please visit <a href="http://www.newsway.co.uk">www.newsway.co.uk</a> 
            to leave your comments. <br>
            </font><br>

<strong><font size="+1">References</font></strong><br>
            <font size="2">The document referenced at <a href="http://www.visualexpert.com/Resources/reactiontime.html" target="_ blank">www.visualexpert.com/Resources/reactiontime.html</a> 
            suggests a thinking time of 1.25 seconds for unexpected events such 
            as a vehicle ahead braking. <br>
            <br>
            Acceleration<br>
            0.2g is approximately 0-60mph in 13 seconds.<br>
            <br>
            Deceleration / braking rate<br>
            <a href="http://www.volvoclub.org.uk/pdf/SpeedStoppingDistances.pdf" target="_ blank"> 
            The rate of deceleration used is 8.5ms-2 [0.85g]. This would represent 
            a very high rate of deceleration such as may be achieved by a car 
            fitted with ABS when braking on a dry road. A rate of deceleration 
            of 4ms-2 [0.4g] would represent the sort of deceleration that might 
            be achieved on a wet road surface. </a></font> </p>
</td>
</tr>

</tbody>
</table>
  <h2>Table of Drivers</h2>

  <div> <a name="backtotop"><a href="#top">back to main screen</a></a> <a name="table_drivers" id="id_table_drivers">ok</a> 

  <a href="#top">back to main screen</a> 
  </div>  
  <HR> <!-- ====================================== -->

<?php #if ($msgcars!="&nbsp;") echo($msgcars); ?>

<NOSCRIPT>

<P> Javascript is not currently enabled on your browser. If you can enable it, your input will be checked as you enter it (on most browsers, at least). You may find this helpful. </P>

</NOSCRIPT>


<HR>
  <!-- ====================================== -->
  <DIV STYLE="clear: left; font-size: 1px; ">&nbsp;</DIV> <!-- needed to make IE display properly :-(  -->

</DIV>  
<!-- end page   -->
<script type="text/javascript">
//<script type="text/javascript">
//script block begin 4 of 5

function update_changes() {
//this was changed 20090522 from showing a popup window to just doing it quietly
//interestingly, update_me.php needed no changes and would have displayed a new window
//probably I will have removed the HTML code in update_me.php to make it slimmer

	var XMLHttpRequestObject = false; 
	
	if (window.XMLHttpRequest) {
	  XMLHttpRequestObject = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
	  XMLHttpRequestObject = new 
		ActiveXObject("Microsoft.XMLHTTP");
	}
		
	var myemail=document.getElementById("email").value;
	var mysessid=document.getElementById("sessid").value;
	var mygapsecs=document.getElementById("gapsecs").value;
	var myurl="update_me.php?email="+escape(myemail)+"&gapsecs="+escape(mygapsecs)+"&sessid="+mysessid;

	myurl=myurl+"&sid="+Math.random();

	if(XMLHttpRequestObject) {
	  XMLHttpRequestObject.open("GET", myurl); 
	
	  XMLHttpRequestObject.onreadystatechange = function() 
	  { 
		if (XMLHttpRequestObject.readyState == 4 && 
		  XMLHttpRequestObject.status == 200) { 
			//document.getElementById("targetDiv").innerHTML =
			  //XMLHttpRequestObject.responseText; 
			delete XMLHttpRequestObject;
			XMLHttpRequestObject = null;
		} 
	  } 
	  XMLHttpRequestObject.send(null); 
	}
	document.getElementById("forupdate").innerHTML = " Things you can change";
	document.getElementById("sys_email").innerHTML = myemail;
	
	mygapsecs = parseFloat(mygapsecs);	
	if (mygapsecs<0.2) {mygapsecs=0.2;}
	if (mygapsecs>4) {mygapsecs=4;}
	
	document.getElementById("sys_gapsecs").innerHTML = mygapsecs;
}


function updateMe() {	
	document.getElementById("forupdate").innerHTML="<a href=javascript:update_changes()>Update the Changes</a>";
	return true;
}
	
//<!-- end block 4 -->
</script>
<script type="text/javascript">
	// var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	// document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
	// var pageTracker = _gat._getTracker("UA-4258749-2");
	// pageTracker._initData();
	// pageTracker._trackPageview();
</script>
	
<script>
    timedCount();
</script>
	
	
</body>
</html>


<script LANGUAGE="JavaScript">
//<script LANGUAGE="JavaScript">
//script block begin 5 of 5
<!-- hide code

var surl = unescape(window.location);
var mtoft = 3.28084;
var boo = (surl.match(/newsway|localhost|sony|safe-gap/) == null); //not a newsway site

var element_comfort = document.getElementById('comfort');
var element_msg_to = document.getElementById('msg_to');
var element_msg_to_name = document.getElementById('msg_to_name');

if(boo){
//alert('about to relocate to newsway.co.uk');
window.location='http://www.newsway.co.uk/safegap/traffic.php';
}




//this section is for the table selectable to send a message
var td1 = null;
var td2 = null;
var td3 = null;

function highlight(obj) {
	if (td1 || td2 || td3) {
		td1.className = null;
		td2.className = null;
		td3.className = null;
	}

	obj.cells[0].className = "select";
	obj.cells[1].className = "select";
	obj.cells[2].className = "select";
	
	td1 = obj.cells[0];
	td2 = obj.cells[1];
	td3 = obj.cells[2];
	
	//alert(td1.innerHTML);
	element_msg_to.value=td1.innerHTML;
	element_msg_to_name.style.backgroundColor = "#00FF00";
	element_msg_to_name.innerHTML=td1.innerHTML + " " + td2.innerHTML;
	setTimeout(highlighter, 500);
}


function highlighter(obj) {
	element_msg_to_name.style.backgroundColor = "#DCDCDC";
}


init();
function init() {
    var i = 0;
    for (i = 1; i <= ncars; i++) {
        cars[i] = new car(i);
    }
	
	for (i = 0; i < nDashes; i++) {
		dashes[i]= new dash(i);
    }
    dashes[i]= new dash(i);
	
    
    // set their positions
	cars[1].X=50;
	
    for (i = 2; i <= ncars; i++) {
		cars[i].X = cars[i-1].X + cars[i-1].width + 20;
		
        cars[i].obj.left = cars[i].X;
        cars[i].obj.top = cars[i].Y;

	}
	
	for (i = 0; i < nDashes; i++) {
		
		dashes[i].X = i * n_dash_spacing;
        dashes[i].Y = Ypos;		
		
		dashes[i].obj.left = dashes[i].X;
        dashes[i].obj.top = dashes[i].Y;
	}
	
	//beyond i
	dashes[i].X = 0;
	dashes[i].Y = 0;
	
	dashes[i].obj.left = dashes[i].X;
	dashes[i].obj.top = dashes[i].Y;
		
	setTimeout("startanimate()", 10);
	
	DriveAspiredSpeed(29, 'button29');
	Grip(1.0, 'buttongrip10');
	VehiclesPerMinute(30, 'buttonVPM30');
	DriveLeft();
}



function car(i) {
    this.X = Xpos;
    this.Y = Ypos;

	this.carspeed = carspeed[i];
	this.accel = accel[i];
	this.decel = decel[i];
	this.thinkmax = thinkmax[i];
	this.thinknow = thinknow[i];
	this.gapsec = gapsec[i];
	
	this.width = w[i];

    if (isNetscape) {	
        this.obj = eval("document.car" + i);
    } else {
        this.obj = eval("car" + i + ".style");
    }
}




function dash(i) {
    this.X = Xpos;
    this.Y = Ypos - 100;
    this.dx = 0;
    this.dy = 0;
    if (isNetscape) {	
        this.obj = eval("document.dash" + i);
    } else {
        this.obj = eval("dash" + i + ".style");
    }
}



function resetpositions() {
	//similar to init
    var i = 0;
	
	//carspermin later get from athe from
	var CPM = 30; 
	
	//total gap in metres
	var SigmaGM = 0; 
	
	//i reckon that
	SigmaGM = (ncars * 60 * SPEED / CPM) - SigmaL;
	
	var gapshare=0;
	
	gapshare = SigmaGM/(ncars-1);
	
    // set their positions
	cars[1].X=50;
	
    for (i = 2; i <= ncars; i++) {
		//cars[i].X = cars[i-1].X + cars[i-1].width + 20;
		cars[i].X = cars[i-1].X + cars[i-1].width + gapshare;	
        cars[i].obj.left = cars[i].X;
        cars[i].obj.top = cars[i].Y;
	}
}



function startanimate() {	
	// recalculate positions every DELTAT millisecs
    setInterval("animate()", DELTAT * 1000);
	//every n milliseconds
	setInterval("movedashes()", DASH_RECALC * 1000);
}



function animate() {	
//every DELTAT secs

var steady_cpm = 0;
var steady_speed = 0;
var steady_gap = 0;
var steady_grip = null;

    cars[1].X = 50;
        // move img to new position
        cars[1].obj.left = cars[1].X;			
        cars[1].obj.top = Ypos + CarOffsetY; //cars[0].Y;
		
	//new speed for lead car/road ----- use 2 as m/s/s
	// SPEED_JITTER is used to stop speed going up and down near aspired speed - stop cars jittering on screen
	var SPEED_JITTER = 0.001;
	if ( ASPIRED_SPEED - SPEED > SPEED_JITTER ) SPEED += DASH_RECALC * g;
	if ( SPEED - ASPIRED_SPEED > SPEED_JITTER ) SPEED -= DASH_RECALC * g;
	
	//new speed for leading car
	cars[1].carspeed = SPEED;

	var bsteady = true;
	
	//From March 2009 gap should be modified by a GAP_MULTIPLIER calculated from a forced CarsPerMinute
	var TimeForAverageCar = SigmaL/(ncars * SPEED);
	var AverageGapSecsNow = (60/CARS_PER_MINUTE) - TimeForAverageCar;
	GAP_MULTIPLIER = AverageGapSecsNow/(SigmaG/ncars);
	


	//after 60 seconds tell the car number as cpm
	var elapsedsecs=0;	
    
	var start = 2;
    for (i = start ; i <= ncars; i++ ) {
	
		var gapsec_this = cars[i].gapsec * GAP_MULTIPLIER;  //try not worrying about a minimum
		
		var gapnow_m = cars[i].X - cars[i - 1].X - cars[i - 1].width //metres
		if (gapnow_m < 0) gapnow_m = 0;	//metres	
		if (cars[i].carspeed > 0) {		
			var gapnow = gapnow_m / cars[i].carspeed; //seconds
		} else {
			var gapnow = 100; //here it locks the queue if 1
		}
		
		//increment per car
		elapsedsecs += gapnow + (cars[i - 1].width / SPEED);
		
		//reset thinking time when at chosen gap
		if (gapnow == gapsec_this) cars[i].thinknow = 0;
		
		if (gapnow < gapsec_this)
		{
			//slow down after thinking
			if (cars[i].thinknow < (cars[i].thinkmax * REACTION_TIME_FACTOR)) {
				cars[i].thinknow += DELTAT;
			} else {
				cars[i].carspeed -= cars[i].decel * BRAKING_DISTANCE_FACTOR * DELTAT;
				if (gapnow == 0) cars[i].carspeed = cars[i - 1].carspeed - 1;
			}
			if (Math.abs(SPEED - cars[i].carspeed) > 1) {bsteady = false};
		}
		
		if (gapnow > gapsec_this)
		{
			//speed up after thinking
			if (cars[i].thinknow < (cars[i].thinkmax * REACTION_TIME_FACTOR)) {
				cars[i].thinknow += DELTAT;
			} else {
				cars[i].carspeed += cars[i].accel * DELTAT;
				//but limit the maximum speed
				if (cars[i].carspeed > 40) cars[i].carspeed = 40;

				//now check distance to car ahead and consider slowing down
				var intendedgap_m = cars[i-1].carspeed * gapsec_this; //metres

				
				if ((gapnow_m > (intendedgap_m * 2)) && (gapnow < (intendedgap_m * 5))) {
					//car is in range where action may be considered
					//is following car going faster?
					if (cars[i].carspeed > cars[i-1].carspeed) {
						//start deceleration to arrive at intendedgap_m evenly
						//a=v^2-u^2/2s
						var d = (cars[i-1].carspeed ^ 2 - cars[i].carspeed ^ 2) / (2 * (gapnow_m - intendedgap_m));
						if (d > decel[i] * BRAKING_DISTANCE_FACTOR) {
							d = decel[i] * BRAKING_DISTANCE_FACTOR; //can't exceed maximum braking deceleration
						}
						cars[i].carspeed -= d * DELTAT;					
					}
				}
			}
			if (Math.abs(SPEED - cars[i].carspeed) > 1) {bsteady = false};
		}
		
		if (cars[i].carspeed < 0)
		{ 
			cars[i].carspeed = 0;
			var newX = cars[i].X + (SPEED * DELTAT);
			//document.Alerts1.alert11.value='Car ' + i + ' was stationary';
		} else {
			//var newX = cars[i - 1].X + cars[i - 1].width + (gapnow * cars[i].carspeed);
			var newX = cars[i].X + ((SPEED - cars[i].carspeed) * DELTAT);
		}
		cars[i].X = newX;
		cars[i].Y = Ypos + CarOffsetY;
	 
        // move img to new position
        cars[i].obj.left = cars[i].X;			
        cars[i].obj.top =  cars[i].Y;	

    }
	
	
	//try here every 18 cycles
	cyclecount++;
	
	if (cyclecount > 18) {
		document.AspiredSpeed.txtSpeed.value = Math.round(ASPIRED_SPEED * 2.23)+" mph";
		document.AspiredSpeed.txtVPM.value = CARS_PER_MINUTE;
		
		var grip = BRAKING_DISTANCE_FACTOR;
		var s_grip = null;
			if (grip > 0.9) { 
				s_grip = 'dry';
			} else if (grip > 0.6) { 
				s_grip = 'damp';
			} else if (grip > 0.3) { 
				s_grip = 'wet';
			} else if (grip > 0.01) { 
				s_grip = 'icy';
			} else { 
				s_grip = 'unknown'
			}	
		
		//Cars per minute
		var CarsPerMinute = Math.round(ncars * 60/elapsedsecs);
		var s_comfort = null;
		
		if (SPEED==steady_speed) {
			s_comfort = "Road speed: " + Math.round(SPEED * 2.23) +"mph / " + Math.round(SPEED * 3.6) +"kph";
		} else {
			s_comfort = "Road speed: <span style='color:black;background-color:#00ff66'>" + Math.round(SPEED * 2.23) +"mph / " + Math.round(SPEED * 3.6) +"kph</span>";		
			steady_speed = SPEED;
		}

		if (s_grip==steady_grip) {
			s_comfort += "<br>Road surface: " + s_grip;
		} else {
			s_comfort += "<br>Road surface: <span style='color:black;background-color:#00ff66'>" + s_grip + "</span>";
			steady_grip = s_grip;
		}

		if(bsteady){
			s_comfort += "<br>Vehicles per minute: " + CarsPerMinute;
		} else {
			s_comfort += "<br>Vehicles per minute: <span style='color:black;background-color:#00ff66'>" + CarsPerMinute + " when steady</span>";
		}
		
		
		if (GAP_MULTIPLIER==steady_gap) {
			s_comfort += "<br>Comfort factor: " + Math.round(GAP_MULTIPLIER * 100) + "% of your desired gap";		
		} else {
			s_comfort += "<br>Comfort factor: <span style='color:black;background-color:#00ff66'>" + Math.round(GAP_MULTIPLIER * 100) + "% of your desired gap</span>";
			steady_gap = GAP_MULTIPLIER;		
		}

		document.AspiredSpeed.txtSurface.value=s_grip;

		element_comfort.innerHTML = s_comfort;

		cyclecount = 0;
	}
}




function movedashes() {
	start = 0;
	
	// get size of window
	var height, width;
	if (isNetscape) {
		height = window.innerHeight + window.pageYOffset;
		//width = window.innerWidth + window.pageXOffset;
		width = window.innerWidth;
	} else {	
		height = document.body.clientHeight + document.body.scrollTop;
		//width = document.body.clientWidth - document.body.scrollLeft;
		width = document.body.clientWidth; 
	}
	
	for (i = start ; i < nDashes; i++ ) {
		// move to new position
		dashes[i].X += (SPEED * DASH_RECALC);
		
		dashes[i].Y = Ypos;
		
		if (dashes[i].X >= width) {
			var lastdash = 0;
			lastdash = i + 1;
			if (lastdash > nDashes) lastdash = 0;
			
			dashes[i].X = 1 - DASHSIZE;
		}
		
		// move img to new position
		dashes[i].obj.left = dashes[i].X;			
		dashes[i].obj.top =  dashes[i].Y;	
	}	
}




function Mouse(evnt){
	var targetElementID="maintable0";
	var targetElement=document.getElementById(targetElementID);

	if (targetElement && document.documentElement.offsetHeight
	        && targetElement.offsetHeight && targetElement.offsetTop) {
	Ypos = targetElement.offsetTop + targetElement.offsetHeight + 1;
	}

	dashes[3].obj.top =  Ypos + RoadWidth;	
}

window.onresize = Mouse;	
window.onload = Mouse;

//end block 5 -->
</SCRIPT>