# safe-gap
 An illustration of motorway traffic - how phantom traffic jams and motorway pileups occur. 
 
 traffic2.htm uses javascript and images to simulate traffic flow on a motorway.

 Originally written by Brian Jones in 2008 it's offered here for anyone to use it or develop it.
 
 The idea of safe-gap was prompted by a need to understand what is actually a safe gap at different speeds, different road surface conditions and different traffic densities (VPM, vehicles per minute). The generally accepted safe gap is 2 seconds, which is about equivalent to 30 VPM. When traffic is very dense it often exceeds 30 VPM and forces drivers to leave less than 2 seconds from cars in front and behind.
 
 Fun to play with. Set the lead car to 80mph. Let the traffic settle down for a few minutes so they're all doing 80mph then slow the lead car to 45mph, simulating an unforeseen event like traffic ahead being forced into the same lane by slower traffic on inside lanes. See what happens when you alter the traffic density, road surface and lead vehicle speed. 

 If you're comfortable with the physics you could edit the javascript to use different parameters for acceleration, deceleration, friction and response times.

 Back in 2008 there was also a version of this that used PHP and MySQL to make contact with nearby drivers using a WhatsApp style of exchange. This got too much extra work and I abandoned it. Might be worth a second look though, now that internet speeds are faster.

You can see a <a href="https://newsway.co.uk/safegap/traffic2.htm">demo</a> at newsway.co.uk
