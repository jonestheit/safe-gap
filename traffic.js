var cyclecount = 0; //equivalent to static in function animate
var Xpos = 0;
var Ypos = 0; //top of road
var RoadWidth = 68;
var m_to_ft = 3.28084;

function buttonColorClear(vClass) {
  var buttons = document.getElementsByClassName(vClass);

  for (var J = buttons.length - 1; J >= 0; --J) {
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
  CarOffsetY = 41;
  document.getElementById("btnDriveLeft").style.backgroundColor = "Yellow";
  document.getElementById("btnDriveRight").style.backgroundColor = "Lightgrey";
}

function DriveRight() {
  CarOffsetY = 32;
  document.getElementById("btnDriveLeft").style.backgroundColor = "Lightgrey";
  document.getElementById("btnDriveRight").style.backgroundColor = "Yellow";
}

//offset 27 for driving on the left
//offset 18 for driving on the right

var CarOffsetY = 41;

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

var DASHSIZE = 1400;
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

var isNetscape =
  navigator.appName == "well Firefox reports as Netscape but behaves like IE"; //"Netscape";
var isOpera = navigator.appName == "Opera";

var ncars = 70;
var SigmaL = 0; //total lenght of cars - metres
var SigmaG = 0; //total gap in seconds
var g = 9.81; //m/s/s notional g force used for max and min decelleration of the lead car and therefore the road

var w = new Array(); //w is width on screen = length of car should be between 2 and 12 m long
var cars = new Array();
var dashes = new Array();

var carspeed = new Array(); //current speed of car
var accel = new Array(); //acceleration per car
var decel = new Array(); //deceleration per car
var thinkmax = new Array(); //how long the driver needs to thing before reacting
var thinknow = new Array(); //how long has driver been thinking
var gapsec = new Array(); //intended gap in seconds

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
    accel[i] = 2; //Many can accelerate faster than 2 but probably don't choose to here
    decel[i] = 7; //Highway Code stopping distance assumes 6.5 m/s/s

    //reaction time in seconds 0.7 is fastest possible - say 1.25 is middle (0.7+ 0.55)
    thinkmax[i] = 0.7; //+ ( GaussRandom(11) / 10 );  //Highway Code says 0.67 seconds if driver is alert etc.
    thinknow[i] = 0; //how long this driver has been thinking before reacting
  }
}

function thinktime() {
  //not called too often - maybe inefficient - attempt at skewed normal bell curve
  var arraythink = [
    0.7,
    0.8,
    0.9,
    1.0,
    1.0,
    1.1,
    1.1,
    1.1,
    1.2,
    1.2,
    1.2,
    1.2,
    1.3,
    1.3,
    1.3,
    1.4,
    1.4,
    1.5,
    1.5,
    1.6,
    1.7,
    1.8,
    1.9,
    2.0,
    2.1,
    2.2,
    2.3,
    2.4,
    2.5,
  ];
  return arraythink[Math.floor(Math.random() * 29)];
}

function carlength() {
  //not called too often
  var arrayw = [
    4,
    4.2,
    4.4,
    4.4,
    4.5,
    4.5,
    4.5,
    4.5,
    4.5,
    4.6,
    4.6,
    4.8,
    5,
    5,
    6,
    7,
  ];
  return arrayw[Math.floor(Math.random() * 16)];
}

//write the car divs
makecardivs();
function makecardivs() {
  //first just the DIVs
  var i = 0;
  for (i = 1; i <= ncars; i++) {
    document.write(
      "<div id='car" +
        i +
        "' style='position: absolute; height:11px; width:10px; z-index: 1;'></div>"
    );
  }
}

//remakes the cars and their colors and resets other variables
makecarsfromtable();
function makecarsfromtable() {
  var i = 0;
  for (i = 1; i <= ncars; i++) {
    var id = "car" + i;
    var thinksecs = thinktime();
    var carw = carlength();
    var element = document.getElementById(id);
    element.innerHTML = "<img src='carb.gif' width='" + carw + "' height='3'>";
    w[i] = carw;
    SigmaL += carw;
    gapsec[i] = thinksecs;
    SigmaG += thinksecs;
  }
}

function showdanger() {
  var V = CARS_PER_MINUTE;
  var S = ASPIRED_SPEED;
  var G = BRAKING_DISTANCE_FACTOR;

  //empirically guessed
  var d = (V / (11 * G)) * (0.8 + S / 100);
  d = Math.round(d);
  if (d > 20) d = 20;

  var i = 0;
  //var danger = "<table width=\"100%\" border=\"1\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" bordercolor=\"#FFFFFF\"><tr>";
  var danger =
    '<table style="border:1px; spacing:0px; padding:0px; text-align=center; width=100%;"><tr>';
  danger +=
    '<td colspan="20" style="font:14px arial, sans-serif;">Danger level</td></tr><tr>';

  for (i = 1; i <= d; i++) {
    danger +=
      '<td style="font:14px Courier monospace; background-color:red">&nbsp;&nbsp;&nbsp;</td>';
  }

  for (i = d + 1; i <= 20; i++) {
    danger +=
      '<td style="font:14px Courier monospace; background-color:green">&nbsp;&nbsp;&nbsp;</td>';
  }

  danger += "</tr></table>";
  document.getElementById("danger").innerHTML = danger;
}
