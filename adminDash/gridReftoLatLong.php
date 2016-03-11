<?php  

/*
See below for the source code of the JavaScript implementation. 
These functions should be simple to translate into other languages if required.

This file has converted this JavaScript code to php code. 

Creative Commons License
I offer these formulæ & scripts for free use and adaptation as my contribution to the open-source 
info-sphere from which I have received so much. You are welcome to re-use these scripts [under a simple attribution license, 
without any warranty express or implied] provided solely that you retain my COPYRIGHT NOTICE and a link to this page.

http://www.movable-type.co.uk/scripts/latlong-gridref-v1.html
*/
/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
/*  Convert latitude/longitude <=> OS National Grid Reference points (c) Chris Veness 2005-2010   */
/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
/*
* convert geodesic co-ordinates to OS grid reference
*/

function OSGridToLatLong($gridRef)
{
 	$gr = gridrefLetToNum($gridRef);
  	$E = $gr[0];
  	$N = $gr[1];

  	$a = 6377563.396; 
 	$b = 6356256.910;              // Airy 1830 major & minor semi-axes
  	$F0 = 0.9996012717;                             // NatGrid scale factor on central meridian
  	$lat0 = 49*M_PI/180; 
  	$lon0 = -2*M_PI/180;  // NatGrid true origin
  	$N0 = -100000; 
  	$E0 = 400000;                     // northing & easting of true origin, metres
  	$e2 = 1 - ($b*$b)/($a*$a);                          // eccentricity squared
  	$n = ($a-$b)/($a+$b);
  	$n2 = $n*$n;
  	$n3 = $n*$n*$n;

  	$lat=$lat0;
  	$M=0;

  	$isFirstTime = true;

  	while ($N-$N0-$M >= 0.00001 || $isFirstTime) {
  		$isFirstTime = false;
    	$lat = ($N-$N0-$M)/($a*$F0) + $lat;

    	$Ma = (1 + $n + (5/4)*$n2 + (5/4)*$n3) * ($lat-$lat0);
    	$Mb = (3*$n + 3*$n*$n + (21/8)*$n3) * sin($lat-$lat0) * cos($lat+$lat0);
    	$Mc = ((15/8)*$n2 + (15/8)*$n3) * sin(2*($lat-$lat0)) * cos(2*($lat+$lat0));
    	$Md = (35/24)*$n3 * sin(3*($lat-$lat0)) * cos(3*($lat+$lat0));
    	$M = $b * $F0 * ($Ma - $Mb + $Mc - $Md);                // meridional arc

  	}  // ie until < 0.01mm

  	$cosLat = cos($lat);
  	$sinLat = sin($lat);
  	$nu = $a*$F0/sqrt(1-$e2*$sinLat*$sinLat);              // transverse radius of curvature
  	$rho = $a*$F0*(1-$e2)/pow(1-$e2*$sinLat*$sinLat, 1.5);  // meridional radius of curvature
  	$eta2 = $nu/$rho-1;

  	$tanLat = tan($lat);
   $tan2lat = $tanLat*$tanLat; 
   $tan4lat = $tan2lat*$tan2lat; 
   $tan6lat = $tan4lat*$tan2lat;
   $secLat = 1/$cosLat;
   $nu3 = $nu*$nu*$nu; 
   $nu5 = $nu3*$nu*$nu; 
   $nu7 = $nu5*$nu*$nu;
   $VII = $tanLat/(2*$rho*$nu);
   $VIII = $tanLat/(24*$rho*$nu3)*(5+3*$tan2lat+$eta2-9*$tan2lat*$eta2);
   $IX = $tanLat/(720*$rho*$nu5)*(61+90*$tan2lat+45*$tan4lat);
   $X = $secLat/$nu;
   $XI = $secLat/(6*$nu3)*($nu/$rho+2*$tan2lat);
   $XII = $secLat/(120*$nu5)*(5+28*$tan2lat+24*$tan4lat);
   $XIIA = $secLat/(5040*$nu7)*(61+662*$tan2lat+1320*$tan4lat+720*$tan6lat);

   $dE = ($E-$E0);
   $dE2 = $dE*$dE; 
   $dE3 = $dE2*$dE;
   $dE4 = $dE2*$dE2;
   $dE5 = $dE3*$dE2;
   $dE6 = $dE4*$dE2; 
   $dE7 = $dE5*$dE2;
   $lat = $lat - $VII*$dE2 + $VIII*$dE4 - $IX*$dE6;
   $lon = $lon0 + $X*$dE - $XI*$dE3 + $XII*$dE5 - $XIIA*$dE7;

   echo [rad2deg($lat), rad2deg($lon)];
}

/* 
 * convert standard grid reference ('SU387148') to fully numeric ref ([438700,114800])
 *   returned co-ordinates are in metres, centred on grid square for conversion to lat/long
 *
 *   note that northern-most grid squares will give 7-digit northings
 *   no error-checking is done on gridref (bad input will give bad results or NaN)
 */
function gridrefLetToNum($gridref) 
{
	// get numeric values of letter references, mapping A->0, B->1, C->2, etc:
	$l1 = strtoupper(ord($gridref)) - ord('A');
	$l2 = strtoupper(ord(substr($gridref, 1))) - ord('A');
	// shuffle down letters after 'I' since 'I' is not used in grid:
  	if ($l1 > 7) 
	{
  		$l1--;
  	}
  	if ($l2 > 7) 
  	{
  		$l2--;
	}
   // convert grid letters into 100km-square indexes from false origin (grid square SV):
   $e = (($l1-2)%5)*5 + ($l2%5);
   $n = (19-floor($l1/5)*5) - floor($l2/5);

   // skip grid letters to get numeric part of ref, stripping any spaces:
   $gridref = str_replace(' ', '',substr($gridref, 2));

   // append numeric part of references to grid index:
   $e += $gridref.slice(0, strlen($gridref)/2);
   $n += $gridref.slice(strlen($gridref)/2);

   // normalise to 1m grid, rounding up to centre of grid square:
   switch (strlen($gridref)) {
  		case 6: $e += '50'; $n += '50'; break;
   	case 8: $e += '5'; $n += '5'; break;
   // 10-digit refs are already 1m
  	}

  	return [$e, $n];
}

?>