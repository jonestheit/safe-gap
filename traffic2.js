var surl = unescape(window.location);
var mtoft = 3.28084;
var boo = (surl.match(/newsway|localhost|sony|safegap/) == null); //not a newsway site
var element_comfort = document.getElementById('comfort');
var element_msg_to = document.getElementById('msg_to');
var element_msg_to_name = document.getElementById('msg_to_name');

if(boo){
//alert('about to relocate to newsway.co.uk');
//2020-08-29 skip this - just run it wherever with a thumbs up to Brian Jones
//window.location='https://www.newsway.co.uk/safegap/traffic2.htm';
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


<!-- function highlighter(obj) { -->
	<!-- element_msg_to_name.style.backgroundColor = "#DCDCDC"; -->
<!-- } -->


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
		//cars[i].X = cars[i-1].X + cars[i-1].width + 20;
		cars[i].X = cars[i-1].X + cars[i-1].width + gapsec[i]*SPEED;
        cars[i].obj.left = cars[i].X + "px";
        cars[i].obj.top = cars[i].Y + "px";
	}

	for (i = 0; i < nDashes; i++) {

		dashes[i].X = i * n_dash_spacing;
        dashes[i].Y = Ypos;

		dashes[i].obj.left = dashes[i].X + "px";
        dashes[i].obj.top = dashes[i].Y + "px";
	}

	//beyond i
	dashes[i].X = 0;
	dashes[i].Y = 0;

	dashes[i].obj.left = dashes[i].X + "px";
	dashes[i].obj.top = dashes[i].Y + "px";

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
        cars[i].obj.left = cars[i].X + "px";
        cars[i].obj.top = cars[i].Y + "px";
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
	cars[1].obj.left = cars[1].X + "px";
	cars[1].obj.top = Ypos + CarOffsetY + "px"; //cars[0].Y;

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
	var elapsedsecs = 0;
	var allCarsYpospx = Ypos + CarOffsetY + "px";

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

		if (gapnow < gapsec_this) {
			//slow down after thinking
			if (cars[i].thinknow < (cars[i].thinkmax * REACTION_TIME_FACTOR)) {
				cars[i].thinknow += DELTAT;
			} else {
				cars[i].carspeed -= cars[i].decel * BRAKING_DISTANCE_FACTOR * DELTAT;
				if (gapnow == 0) cars[i].carspeed = cars[i - 1].carspeed - 1;
			}
			if (Math.abs(SPEED - cars[i].carspeed) > 1) {bsteady = false};
		}

		if (gapnow > gapsec_this) {
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

        // move img to new position
        cars[i].obj.left = cars[i].X + "px";
		cars[i].obj.top = allCarsYpospx;
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
	
	//9 is empirical - don't know what happened - it was fine before HTML5
	var dashesYpospx = Ypos + 9 + "px";

	for (i = start ; i < nDashes; i++ ) {
		// move to new position
		dashes[i].X += (SPEED * DASH_RECALC);

		if (dashes[i].X >= width) {
			var lastdash = 0;
			lastdash = i + 1;
			if (lastdash > nDashes) lastdash = 0;
			dashes[i].X = 1 - DASHSIZE;
		}

		// move img to new X position 
		//-2 to stop gaps appearing
		dashes[i].obj.left = dashes[i].X -2 + "px";	
		dashes[i].obj.top = dashesYpospx;			
	}
}


function Mouse(evnt){
	var targetElementID="maintable0";
	var targetElement=document.getElementById(targetElementID);

	if (targetElement && document.documentElement.offsetHeight
	        && targetElement.offsetHeight && targetElement.offsetTop) {
	Ypos = targetElement.offsetTop + targetElement.offsetHeight - 1;
	}

	dashes[3].obj.top =  Ypos + RoadWidth + "px";
}

window.onresize = Mouse;
window.onload = Mouse;