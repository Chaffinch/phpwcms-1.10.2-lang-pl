<?php

/**
* Project:     GoogleMapAPI: a PHP library inteface to the Google Map API
* File:        GoogleMapAPI.class.php
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or (at your option) any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
*
* @link http://www.phpinsider.com/php/code/GoogleMapAPI/
* @link http://www.phpwcms.org/
* @copyright 2005 New Digital Group, Inc.
* @author Monte Ohrt <monte at ohrt dot com>
* @author Oliver Georgi <oliver at phpwcms dot de>
* @package GoogleMapAPI
* @version 2.5
*/

/* $Id: GoogleMapAPI.class.php,v 1.63 2007/08/03 16:29:40 mohrt Exp $ */

/*

************
Enhanced by Oliver Georgi, 2009-06-29 for use with phpwcms Content Management System
************

For best results with GoogleMaps, use XHTML compliant web pages with this header:

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">

For database caching, you will want to use this schema:

CREATE TABLE `phpwcms_geocodes` (
  `address` varchar(255) NOT NULL,
  `lon` double DEFAULT NULL,
  `lat` double DEFAULT NULL,
  PRIMARY KEY (`address`),
  KEY `lon` (`lon`),
  KEY `lat` (`lat`)
);

*/

class GoogleMapAPI
{
	/**
	* PEAR::DB DSN for geocode caching. example:
	* $dsn = 'mysql://user:pass@localhost/dbname';
	*
	* @var string
	*/
	var $dsn = null;

	/**
	* YOUR GooglMap API KEY for your site.
	* (http://maps.google.com/apis/maps/signup.html)
	*
	* @var string
	*/
	var $api_key = '';

	/**
	* current map id, set when you instantiate
	* the GoogleMapAPI object.
	*
	* @var string
	*/
	var $map_id = null;

	/**
	* sidebar <div> used along with this map.
	*
	* @var string
	*/
	var $sidebar_id = null;

	/**
	* GoogleMapAPI uses the Yahoo geocode lookup API.
	* This is the application ID for YOUR application.
	* This is set upon instantiating the GoogleMapAPI object.
	* (http://developer.yahoo.net/faq/index.html#appid)
	*
	* @var string
	*/
	var $app_id = null;

	/**
	* use onLoad() to load the map javascript.
	* if enabled, be sure to include on your webpage:
	* <html onload="onLoad()">
	*
	* @var string
	*/
	var $onload = true;

	/**
	* map center latitude (horizontal)
	* calculated automatically as markers
	* are added to the map.
	*
	* @var float
	*/
	var $center_lat = null;

	/**
	* map center longitude (vertical)
	* calculated automatically as markers
	* are added to the map.
	*
	* @var float
	*/
	var $center_lon = null;

	/**
	* enables map controls (zoom/move/center)
	*
	* @var boolean
	*/
	var $map_controls = true;

	/**
	* determines the map control type
	* small -> show move/center controls
	* large -> show move/center/zoom controls
	*
	* @var string
	*/
	var $control_size = 'large';

	/**
	* enables map type controls (map/satellite/hybrid)
	*
	* @var boolean
	*/
	var $type_controls = true;

	/**
	* default map type (G_NORMAL_MAP/G_SATELLITE_MAP/G_HYBRID_MAP)
	*
	* @var boolean
	*/
	var $map_type = 'G_NORMAL_MAP';

	/**
	* enables scale map control
	*
	* @var boolean
	*/
	var $scale_control = true;

	/**
	* enables overview map control
	*
	* @var boolean
	*/
	var $overview_control = false;

	/**
	* determines the default zoom level
	*
	* @var integer
	*/
	var $zoom = 16;

	/**
	* determines the map width
	*
	* @var integer
	*/
	var $width = '500px';

	/**
	* determines the map height
	*
	* @var integer
	*/
	var $height = '500px';

	/**
	* message that pops up when the browser is incompatible with Google Maps.
	* set to empty string to disable.
	*
	* @var integer
	*/
	var $browser_alert = 'Sorry, the Google Maps API is not compatible with this browser.';

	/**
	* message that appears when javascript is disabled.
	* set to empty string to disable.
	*
	* @var string
	*/
	var $js_alert = '<b>Javascript must be enabled in order to use Google Maps.</b>';

	/**
	* determines if sidebar is enabled
	*
	* @var boolean
	*/
	var $sidebar = true;

	/**
	* determines if list of point should given
	*
	* @var boolean
	*/
	var $pointlist = false;

	/**
	* determines if to/from directions are included inside info window
	*
	* @var boolean
	*/
	var $directions = true;

	/**
	* determines if map markers bring up an info window
	*
	* @var boolean
	*/
	var $info_window = true;

	/**
	* determines if info window appears with a click or mouseover
	*
	* @var string click/mouseover
	*/
	var $window_trigger = 'click';

	/**
	* what server geocode lookups come from
	*
	* available: YAHOO  Yahoo! API. US geocode lookups only.
	*             GOOGLE Google Maps. This can do international lookups,
	*                    but not an official API service so no guarantees.
	*             Note: GOOGLE is the default lookup service, please read
	*                   the Yahoo! terms of service before using their API.
	*
	* @var string service name
	*/
	var $lookup_service = 'GOOGLE';
	var $lookup_server = array( 'GOOGLE' => 'maps.google.de', 'YAHOO' => 'api.local.yahoo.com' );

	var $driving_dir_text = array( 'dir_to' => 'Startadresse: (Stra&szlig;e, PLZ Stadt, Land)',
		'to_button_value' => 'Los!',
		'to_button_type' => 'submit',
		'dir_from' => 'Zieladresse: (Straße, PLZ Stadt, Land)',
		'from_button_value' => 'Los!',
		'from_button_type' => 'submit',
		'dir_text' => 'Route berechnen: ',
		'dir_tohere' => 'Hierher',
		'dir_fromhere' => 'Von hier'
		);

	/**
	* version number
	*
	* @var string
	*/
	var $_version = '2.5';

	/**
	* list of added markers
	*
	* @var array
	*/
	var $_markers = array();

	/**
	* maximum longitude of all markers
	*
	* @var float
	*/
	var $_max_lon = -1000000;

	/**
	* minimum longitude of all markers
	*
	* @var float
	*/
	var $_min_lon = 1000000;

	/**
	* max latitude
	*
	* @var float
	*/
	var $_max_lat = -1000000;

	/**
	* min latitude
	*
	* @var float
	*/
	var $_min_lat = 1000000;

	/**
	* determines if we should zoom to minimum level (above this->zoom value) that will encompass all markers
	*
	* @var boolean
	*/
	var $zoom_encompass = true;

	/**
	* determines if we can zoom using a mouse's scroll wheel.
	*
	* @var boolean
	*/
	var $zoom_wheel = false;

	/**
	* factor by which to fudge the boundaries so that when we zoom encompass, the markers aren't too close to the edge
	*
	* @var float
	*/
	var $bounds_fudge = 0.01;

	/**
	* use the first suggestion by a google lookup if exact match not found
	*
	* @var float
	*/
	var $use_suggest = false;

	/**
	* list of added polylines
	*
	* @var array
	*/
	var $_polylines = array();

	/**
	* icon info array
	*
	* @var array
	*/
	var $_icons = array();

	/**
	* icon cache array
	*
	* @var array
	*/
	var $_icon_cache = array();

	/**
	* database cache table name
	*
	* @var string
	*/
	var $_db_cache_table = 'phpwcms_geocodes';

	/**
	* JSON class path
	*
    * @deprecated
	* @var string
	*/
	var $class_json_path = null;
    /**
     * @var bool
     */
	var $use_json = false;

	/**
	* geocode placemarks returned by getGeocode
	* it's helpful to check this too and show
	* alternative addresses returned by Google Maps
	*
	* @var array
	*/
	var $Geocode_Placemark = array();


	/**
	* debug enable
	*
	* @var bool
	*/
	var $_debug = false;

	/**
	* debug log path/file
	*
	* @var string
	*/
	var $_debug_file = '';

	/**
	* use MooTools to access element by ID
	*
	* @var bool
	*/
	var $_mootools = FALSE;


	/**
	* class constructor
	*
	* @param string $map_id the id for this map
	* @param string $app_id YOUR Yahoo App ID
	*/
	function __construct($map_id = 'map', $app_id = 'MyMapApp' )
	{
		$this->map_id = $map_id;
		$this->sidebar_id = 'sidebar_' . $map_id;
		$this->app_id = $app_id;
		$this->document_root = $_SERVER['DOCUMENT_ROOT'];
		$this->_debug_file = empty($this->_debug_file) ? PHPWCMS_TEMP.'maps_url.log' : $this->_debug_file;
	}

	function initMap($map_id = 'map', $app_id = 'MyMapApp')
	{
		$this->map_id = $map_id;
		$this->sidebar_id = 'sidebar_' . $map_id;
		$this->app_id = $app_id;
	}

	/**
	* sets the PEAR::DB dsn
	*
	* @param string $dsn
	*/
	function setDSN( $dsn )
	{
		$this->dsn = $dsn;
	}

	function enableMooTools()
	{
		$this->_mootools = TRUE;
	}

	function disableMooTools()
	{
		$this->_mootools = FALSE;
	}

	function useJson($path=NULL) {
		$this->use_json = true;
	}

	function useCSV() {
		$this->use_json = false;
	}

	/**
	* return the element by ID JavaScript String
	*
	* @param string $dsn
	*/
	function getElementById()
	{
		return $this->_mootools ? '$' : 'document.getElementById';
	}

	/**
	* sets YOUR Google Map API key
	*
	* @param string $key
	*/
	function setAPIKey( $key )
	{
		$this->api_key = $key;
	}

	/**
	* sets the width of the map
	*
	* @param string $width
	*/
	function setWidth( $width )
	{
		if ( !preg_match( '!^(\d+)(.*)$!', $width, $_match ) )
			return false;

		$_width = $_match[1];
		$_type = $_match[2];
		if ( $_type == '%' )
			$this->width = $_width . '%';
		else
			$this->width = $_width . 'px';

		return true;
	}

	/**
	* sets the height of the map
	*
	* @param string $height
	*/
	function setHeight( $height )
	{
		if ( !preg_match( '!^(\d+)(.*)$!', $height, $_match ) )
			return false;

		$_height = $_match[1];
		$_type = $_match[2];
		if ( $_type == '%' )
			$this->height = $_height . '%';
		else
			$this->height = $_height . 'px';

		return true;
	}

	/**
	* sets the default map zoom level
	*
	* @param string $level
	*/
	function setZoomLevel( $level )
	{
		$this->zoom = ( int ) $level;
	}

	/**
	* enables the map controls (zoom/move)
	*/
	function enableMapControls()
	{
		$this->map_controls = true;
	}

	/**
	* disables the map controls (zoom/move)
	*/
	function disableMapControls()
	{
		$this->map_controls = false;
	}

	/**
	* sets the map control size (large/small)
	*
	* @param string $size
	*/
	function setControlSize( $size )
	{
		if ( in_array( $size, array( 'large', 'small', 'medium' ) ) )
			$this->control_size = $size;
	}

	/**
	* enables the type controls (map/satellite/hybrid)
	*/
	function enableTypeControls()
	{
		$this->type_controls = true;
	}

	/**
	* disables the type controls (map/satellite/hybrid)
	*/
	function disableTypeControls()
	{
		$this->type_controls = false;
	}

	/**
	* set default map type (map/satellite/hybrid)
	*/
	function setMapType( $type )
	{
		switch ( $type )
		{
			case 'hybrid':			$this->map_type = 'G_HYBRID_MAP';			break;
			case 'satellite':		$this->map_type = 'G_SATELLITE_MAP';		break;
			case 'physical':		$this->map_type = 'G_PHYSICAL_MAP';			break;
			case 'moon_elevation':	$this->map_type = 'G_MOON_ELEVATION_MAP';	break;
			case 'moon':			$this->map_type = 'G_MOON_VISIBLE_MAP';		break;
			case 'mars_elevation':	$this->map_type = 'G_MARS_ELEVATION_MAP';	break;
			case 'mars':			$this->map_type = 'G_MARS_VISIBLE_MAP';		break;
			case 'mars_infrared':	$this->map_type = 'G_MARS_INFRARED_MAP';	break;
			case 'nightsky':		$this->map_type = 'G_SKY_VISIBLE_MAP';		break;

			case 'map':
			case 'normal':
			default:			$this->map_type = 'G_NORMAL_MAP';		break;
		}
	}

	/**
	* enables onload
	*/
	function enableOnLoad()
	{
		$this->onload = true;
	}

	/**
	* disables onload
	*/
	function disableOnLoad()
	{
		$this->onload = false;
	}

	/**
	* enables sidebar
	*/
	function enableSidebar()
	{
		$this->sidebar = true;
	}

	/**
	* disables sidebar
	*/
	function disableSidebar()
	{
		$this->sidebar = false;
	}

	/**
	* enables pointlist
	*/
	function enablePointlist()
	{
		$this->pointlist = true;
	}

	/**
	* disables sidebar
	*/
	function disablePointlist()
	{
		$this->pointlist = false;
	}

	/**
	* enables map directions inside info window
	*/
	function enableDirections()
	{
		$this->directions = true;
	}

	/**
	* disables map directions inside info window
	*/
	function disableDirections()
	{
		$this->directions = false;
	}

	/**
	* set browser alert message for incompatible browsers
	*
	* @params $message string
	*/
	function setBrowserAlert( $message )
	{
		$this->browser_alert = $message;
	}

	/**
	* set <noscript> message when javascript is disabled
	*
	* @params $message string
	*/
	function setJSAlert( $message )
	{
		$this->js_alert = $message;
	}

	/**
	* enable map marker info windows
	*/
	function enableInfoWindow()
	{
		$this->info_window = true;
	}

	/**
	* disable map marker info windows
	*/
	function disableInfoWindow()
	{
		$this->info_window = false;
	}

	/**
	* set the info window trigger action
	*
	* @params $message string click/mouseover
	*/
	function setInfoWindowTrigger( $type )
	{
		switch ( $type )
		{
			case 'mouseover':
				$this->window_trigger = 'mouseover';
				break;
			default:
				$this->window_trigger = 'click';
				break;
		}
	}

	/**
	* enable zoom to encompass makers
	*/
	function enableZoomEncompass()
	{
		$this->zoom_encompass = true;
	}

	/**
	* disable zoom to encompass makers
	*/
	function disableZoomEncompass()
	{
		$this->zoom_encompass = false;
	}

	/**
	* enable zoom using a mouse's scroll wheel.
	*/
	function enableZoomWheel()
	{
		$this->zoom_wheel = true;
	}

	/**
	* disable zoom using a mouse's scroll wheel.
	*/
	function disableZoomWheel()
	{
		$this->zoom_wheel = false;
	}

	/**
	* set the boundary fudge factor
	*/
	function setBoundsFudge( $val )
	{
		$this->bounds_fudge = $val;
	}

	/**
	* enables the scale map control
	*/
	function enableScaleControl()
	{
		$this->scale_control = true;
	}

	/**
	* disables the scale map control
	*/
	function disableScaleControl()
	{
		$this->scale_control = false;
	}

	/**
	* enables the overview map control
	*/
	function enableOverviewControl()
	{
		$this->overview_control = true;
	}

	/**
	* disables the overview map control
	*/
	function disableOverviewControl()
	{
		$this->overview_control = false;
	}

	/**
	* set the lookup service to use for geocode lookups
	* default is YAHOO, you can also use GOOGLE.
	* NOTE: GOOGLE can to intl lookups, but is not an
	* official API, so use at your own risk.
	*/
	function setLookupService( $service )
	{
		switch ( $service )
		{
			case 'GOOGLE':
				$this->lookup_service = 'GOOGLE';
				break;
			case 'YAHOO':
			default:
				$this->lookup_service = 'YAHOO';
				break;
		}
	}

	/**
	* adds a map marker by address
	*
	* @param string $address the map address to mark (street/city/state/zip)
	* @param string $title the title display in the sidebar
	* @param string $html the HTML block to display in the info bubble (if empty, title is used)
	*/
	function addMarkerByAddress( $address, $title = '', $html = '', $tooltip = '' )
	{
		$_geocode = $this->getGeocode( $address );
		if ( $_geocode === false ) {
			return false;
		}
		return $this->addMarkerByCoords( $_geocode['lon'], $_geocode['lat'], $title, $html, $tooltip );
	}

	/**
	* adds a map marker by geocode
	*
	* @param string $lon the map longitude (horizontal)
	* @param string $lat the map latitude (vertical)
	* @param string $title the title display in the sidebar
	* @param string $html |array $html
	*      string: the HTML block to display in the info bubble (if empty, title is used)
	*      array: The title => content pairs for a tabbed info bubble
	*/
	// TODO make it so you can specify which tab you want the directions to appear in (add another arg)
	function addMarkerByCoords( $lon, $lat, $title = '', $html = '', $tooltip = '' )
	{
		$_marker['lon'] = $lon;
		$_marker['lat'] = $lat;
		$_marker['html'] = ( is_array( $html ) || strlen( $html ) > 0 ) ? $html : $title;
		$_marker['title'] = $title;
		$_marker['tooltip'] = $tooltip;
		$this->_markers[] = $_marker;
		$this->adjustCenterCoords( $_marker['lon'], $_marker['lat'] );
		// return index of marker
		return count( $this->_markers ) - 1;
	}

	/**
	* adds a map polyline by address
	* if color, weight and opacity are not defined, use the google maps defaults
	*
	* @param string $address1 the map address to draw from
	* @param string $address2 the map address to draw to
	* @param string $color the color of the line (format: #000000)
	* @param string $weight the weight of the line in pixels
	* @param string $opacity the line opacity (percentage)
	*/
	function addPolyLineByAddress( $address1, $address2, $color = '', $weight = 0, $opacity = 0 )
	{
		if ( ( $_geocode1 = $this->getGeocode( $address1 ) ) === false )
			return false;
		if ( ( $_geocode2 = $this->getGeocode( $address2 ) ) === false )
			return false;
		return $this->addPolyLineByCoords( $_geocode1['lon'], $_geocode1['lat'], $_geocode2['lon'], $_geocode2['lat'], $color, $weight, $opacity );
	}

	/**
	* adds a map polyline by map coordinates
	* if color, weight and opacity are not defined, use the google maps defaults
	*
	* @param string $lon1 the map longitude to draw from
	* @param string $lat1 the map latitude to draw from
	* @param string $lon2 the map longitude to draw to
	* @param string $lat2 the map latitude to draw to
	* @param string $color the color of the line (format: #000000)
	* @param string $weight the weight of the line in pixels
	* @param string $opacity the line opacity (percentage)
	*/
	function addPolyLineByCoords( $lon1, $lat1, $lon2, $lat2, $color = '', $weight = 0, $opacity = 0 )
	{
		$_polyline['lon1'] = $lon1;
		$_polyline['lat1'] = $lat1;
		$_polyline['lon2'] = $lon2;
		$_polyline['lat2'] = $lat2;
		$_polyline['color'] = $color;
		$_polyline['weight'] = $weight;
		$_polyline['opacity'] = $opacity;
		$this->_polylines[] = $_polyline;
		$this->adjustCenterCoords( $_polyline['lon1'], $_polyline['lat1'] );
		$this->adjustCenterCoords( $_polyline['lon2'], $_polyline['lat2'] );
		// return index of polyline
		return count( $this->_polylines ) - 1;
	}

	/**
	* adjust map center coordinates by the given lat/lon point
	*
	* @param string $lon the map latitude (horizontal)
	* @param string $lat the map latitude (vertical)
	*/
	function adjustCenterCoords( $lon, $lat )
	{
		if ( strlen( ( string )$lon ) == 0 || strlen( ( string )$lat ) == 0 )
			return false;
		$this->_max_lon = ( float ) max( $lon, $this->_max_lon );
		$this->_min_lon = ( float ) min( $lon, $this->_min_lon );
		$this->_max_lat = ( float ) max( $lat, $this->_max_lat );
		$this->_min_lat = ( float ) min( $lat, $this->_min_lat );

		$this->center_lon = ( float ) ( $this->_min_lon + $this->_max_lon ) / 2;
		$this->center_lat = ( float ) ( $this->_min_lat + $this->_max_lat ) / 2;
		return true;
	}

	/**
	* set map center coordinates to lat/lon point
	*
	* @param string $lon the map latitude (horizontal)
	* @param string $lat the map latitude (vertical)
	*/
	function setCenterCoords( $lon, $lat )
	{
		$this->center_lat = ( float ) $lat;
		$this->center_lon = ( float ) $lon;
	}

	/**
	* generate an array of params for a new marker icon image
	* iconShadowImage is optional
	* If anchor coords are not supplied, we use the center point of the image by default.
	* Can be called statically. For private use by addMarkerIcon() and setMarkerIcon()
	* Uses icon cache to speed up marker image handling
	*
	* @param string $iconImage URL to icon image
	* @param string $iconShadowImage URL to shadow image
	* @param string $iconAnchorX X coordinate for icon anchor point
	* @param string $iconAnchorY Y coordinate for icon anchor point
	* @param string $infoWindowAnchorX X coordinate for info window anchor point
	* @param string $infoWindowAnchorY Y coordinate for info window anchor point
	*/
	function createMarkerIcon( $iconImage, $iconShadowImage = '', $iconAnchorX = 'x', $iconAnchorY = 'x', $infoWindowAnchorX = 'x', $infoWindowAnchorY = 'x' )
	{
		$_icon_image_path	= str_starts_with($iconImage, 'http') ? $iconImage : $this->document_root . $iconImage;
		$_icon_cache_key	= md5($_icon_image_path . $iconShadowImage . $iconAnchorX . $iconAnchorY . $infoWindowAnchorX . $infoWindowAnchorY);
		if(isset($this->_icon_cache[ $_icon_cache_key ]))
		{
			return $this->_icon_cache[ $_icon_cache_key ];
		}
		if ( !( $_image_info = @getimagesize( $_icon_image_path ) ) )
		{
			return FALSE;
			//die( 'GoogleMapAPI:createMarkerIcon: Error reading image: ' . $iconImage );
		}
		if ( $iconShadowImage )
		{
			$_shadow_image_path = str_starts_with($iconShadowImage, 'http') ? $iconShadowImage : $this->document_root . $iconShadowImage;
			if ( !( $_shadow_info = @getimagesize( $_shadow_image_path ) ) )
			{
				return FALSE;
				//die( 'GoogleMapAPI:createMarkerIcon: Error reading image: ' . $iconShadowImage );
			}
		}

		if ( $iconAnchorX === 'x' )
		{
			$iconAnchorX = ( int ) ( $_image_info[0] / 2 );
		}
		if ( $iconAnchorY === 'x' )
		{
			$iconAnchorY = ( int ) ( $_image_info[1] / 2 );
		}
		if ( $infoWindowAnchorX === 'x' )
		{
			$infoWindowAnchorX = ( int ) ( $_image_info[0] / 2 );
		}
		if ( $infoWindowAnchorY === 'x' )
		{
			$infoWindowAnchorY = ( int ) ( $_image_info[1] / 2 );
		}

		$icon_info = array( 'image' => $iconImage,
			'iconWidth' => $_image_info[0],
			'iconHeight' => $_image_info[1],
			'iconAnchorX' => $iconAnchorX,
			'iconAnchorY' => $iconAnchorY,
			'infoWindowAnchorX' => $infoWindowAnchorX,
			'infoWindowAnchorY' => $infoWindowAnchorY
			);
		if ( $iconShadowImage )
		{
			$icon_info = array_merge( $icon_info, array( 'shadow' => $iconShadowImage,
					'shadowWidth' => $_shadow_info[0],
					'shadowHeight' => $_shadow_info[1] ) );
		}

		$this->_icon_cache[ $_icon_cache_key ] = $icon_info;

		return $icon_info;
	}

	/**
	* set the marker icon for ALL markers on the map
	*/
	function setMarkerIcon( $iconImage, $iconShadowImage = '', $iconAnchorX = 'x', $iconAnchorY = 'x', $infoWindowAnchorX = 'x', $infoWindowAnchorY = 'x' )
	{
		$result = $this->createMarkerIcon( $iconImage, $iconShadowImage, $iconAnchorX, $iconAnchorY, $infoWindowAnchorX, $infoWindowAnchorY );
		if($result !== FALSE) {
			$this->_icons = array( $result );
		}
	}

	/**
	* add an icon to go with the correspondingly added marker
	*/
	function addMarkerIcon( $iconImage, $iconShadowImage = '', $iconAnchorX = 'x', $iconAnchorY = 'x', $infoWindowAnchorX = 'x', $infoWindowAnchorY = 'x' )
	{
		$result = $this->createMarkerIcon( $iconImage, $iconShadowImage, $iconAnchorX, $iconAnchorY, $infoWindowAnchorX, $infoWindowAnchorY );
		if($result !== FALSE) {
			$this->_icons[] = $result;
		}
		return count( $this->_icons ) - 1;
	}

	/**
	* print map header javascript (goes between <head></head>)
	*/
	function printHeaderJS()
	{
		echo $this->getHeaderJS();
	}

	/**
	* return map header javascript (goes between <head></head>)
	*/
	function getHeaderJS()
	{
		return sprintf( '<script src="http://maps.google.de/maps?file=api&amp;v=2.x&amp;key=%s" type="text/javascript" charset="utf-8"></script>', $this->api_key );
	}

	/**
	* prints onLoad() without having to manipulate body tag.
	* call this after the print map like so...
	*       $map->printMap();
	*       $map->printOnLoad();
	*/
	function printOnLoad()
	{
		echo $this->getOnLoad();
	}

	/**
	* return js to set onload function
	*/
	function getOnLoad()
	{
		return '<script type="text/javascript" charset="utf-8">window.onload=onLoad;</script>';
	}

	/**
	* print map javascript (put just before </body>, or in <header> if using onLoad())
	*/
	function printMapJS()
	{
		echo $this->getMapJS();
	}

	/**
	* return map javascript
	*/
	function getMapJS()
	{
		$_output = '<script type="text/javascript">' . "\n";
		$_output .= '//<![CDATA[' . "\n";
		$_output .= "/*************************************************\n";
		$_output .= " * Created with GoogleMapAPI " . $this->_version . "\n";
		$_output .= " * Author: Monte Ohrt <monte AT ohrt DOT com>\n";
		$_output .= " * Copyright 2005-2006 New Digital Group\n";
		$_output .= " * http://www.phpinsider.com/php/code/GoogleMapAPI/\n";
		$_output .= " *************************************************/\n";

		$_output .= 'var points = [];' . "\n";
		$_output .= 'var markers = [];' . "\n";
		$_output .= 'var counter = 0;' . "\n";
		if ( $this->sidebar )
		{
			$_output .= 'var sidebar_html = "";' . "\n";
			$_output .= 'var marker_html = [];' . "\n";
		}

		if ( $this->pointlist )
		{
			$_output .= 'var pointlist_html = "";' . "\n";
			if ( !$this->sidebar )
			{
				$_output .= 'var marker_html = [];' . "\n";
			}
		}

		if ( $this->directions )
		{
			$_output .= 'var to_htmls = [];' . "\n";
			$_output .= 'var from_htmls = [];' . "\n";
		}

		if ( !empty( $this->_icons ) )
		{
			// define temporary icon storage
			$exist_icn = array();
			$_output .= 'var icon = [];' . "\n";
			for( $i = 0, $j = count( $this->_icons ); $i < $j; $i++ )
			{
				$info = $this->_icons[$i];
				// hash the icon data to see if we've already got this one; if so, save some javascript
				$icon_key = md5( serialize( $info ) );
				if ( !isset( $exist_icn[$icon_key] ) ) {
					$_output .= "icon[$i] = new GIcon();\n";
					$_output .= sprintf( 'icon[%s].image = "%s";', $i, $info['image'] ) . "\n";
					if ( $info['shadow'] )
					{
						$_output .= sprintf( 'icon[%s].shadow = "%s";', $i, $info['shadow'] ) . "\n";
						$_output .= sprintf( 'icon[%s].shadowSize = new GSize(%s,%s);', $i, $info['shadowWidth'], $info['shadowHeight'] ) . "\n";
					}
					$_output .= sprintf( 'icon[%s].iconSize = new GSize(%s,%s);', $i, $info['iconWidth'], $info['iconHeight'] ) . "\n";
					$_output .= sprintf( 'icon[%s].iconAnchor = new GPoint(%s,%s);', $i, $info['iconAnchorX'], $info['iconAnchorY'] ) . "\n";
					$_output .= sprintf( 'icon[%s].infoWindowAnchor = new GPoint(%s,%s);', $i, $info['infoWindowAnchorX'], $info['infoWindowAnchorY'] ) . "\n";

					// save current icon
					$exist_icn[$icon_key] = $i;

				} else {
					$_output .= 'icon['.$i.'] = icon[' . $exist_icn[ $icon_key] . "];\n";
				}
			}
		}

		$_output .= 'var map = null;' . "\n";

		if ( $this->onload )
		{
			$_output .= 'function onLoad() {' . "\n";
		}

		if ( !empty( $this->browser_alert ) )
		{
			$_output .= 'if (GBrowserIsCompatible()) {' . "\n";
		}

		$_output .= sprintf( 'var mapObj = '.$this->getElementById().'("%s");', $this->map_id ) . "\n";
		$_output .= 'if (mapObj != "undefined" && mapObj != null) { map = new GMap2( mapObj );' . "\n";
		if ( isset( $this->center_lat ) && isset( $this->center_lon ) )
		{
			// Special care for decimal point in lon and lat, would get lost if "wrong" locale is set; applies to (s)printf only
			$_output .= sprintf( 'map.setCenter(new GLatLng(%s, %s), %d, %s);', number_format( $this->center_lat, 6, ".", "" ), number_format( $this->center_lon, 6, ".", "" ), $this->zoom, $this->map_type ) . "\n";
		}
		// zoom so that all markers are in the viewport
		if ( $this->zoom_encompass && count( $this->_markers ) > 1 )
		{
			// increase bounds by fudge factor to keep
			// markers away from the edges
			$_len_lon = $this->_max_lon - $this->_min_lon;
			$_len_lat = $this->_max_lat - $this->_min_lat;
			$this->_min_lon -= $_len_lon * $this->bounds_fudge;
			$this->_max_lon += $_len_lon * $this->bounds_fudge;
			$this->_min_lat -= $_len_lat * $this->bounds_fudge;
			$this->_max_lat += $_len_lat * $this->bounds_fudge;

			$_output .= "var bds = new GLatLngBounds(new GLatLng($this->_min_lat, $this->_min_lon), new GLatLng($this->_max_lat, $this->_max_lon));\n";
			$_output .= 'map.setZoom(map.getBoundsZoomLevel(bds));' . "\n";
		}

		if( $this->zoom_wheel ) {
			$_output .= 'map.enableScrollWheelZoom();' . "\n";
		} else {
			$_output .= 'map.disableScrollWheelZoom();' . "\n";
		}

		if ( $this->map_controls )
		{
			if ( $this->control_size == 'large' )
				$_output .= 'map.addControl(new GLargeMapControl());' . "\n";
			else
				$_output .= 'map.addControl(new GSmallMapControl());' . "\n";
		}
		if ( $this->type_controls )
		{
			$_output .= 'map.addControl(new GMapTypeControl());' . "\n";
		}

		if ( $this->scale_control )
		{
			$_output .= 'map.addControl(new GScaleControl());' . "\n";
		}

		if ( $this->overview_control )
		{
			$_output .= 'map.addControl(new GOverviewMapControl());' . "\n";
		}

		$_output .= $this->getAddMarkersJS();

		$_output .= $this->getPolylineJS();

		if ( $this->sidebar )
		{
			$_output .= 'var sidebar_el = ' . $this->getElementById().'("'.$this->sidebar_id.'");' . "\n";
			$_output .= 'if(sidebar_el){' . 'sidebar_el.innerHTML = "<"+"ul class=\"gmapSidebar\">"+ sidebar_html +"<"+"\/ul>";}' . "\n";
		}

		$_output .= '}' . "\n";

		if ( !empty( $this->browser_alert ) )
		{
			$_output .= '} else {' . "\n";
			$_output .= 'alert("' . str_replace( '"', '\"', $this->browser_alert ) . '");' . "\n";
			$_output .= '}' . "\n";
		}

		if(isset($this->jump_index))
		{
			$_output .= 'click_sidebar('.$this->jump_index.');' . "\n";
		}

		if ( $this->onload )
		{
			$_output .= '}' . "\n";
		}

		$_output .= $this->getCreateMarkerJS();
		// Utility functions used to distinguish between tabbed and non-tabbed info windows
		$_output .= 'function isArray(a){return isObject(a) && a.constructor == Array;}' . "\n";
		$_output .= 'function isObject(a){return (a && typeof a == \'object\') || isFunction(a);}' . "\n";
		$_output .= 'function isFunction(a){return typeof a == \'function\';}' . "\n";

		if ( $this->sidebar || $this->pointlist ) // use it for pointlist too
		{
			$_output .= 'function click_sidebar(idx) {';
			$_output .= 'if(isArray(marker_html[idx])){markers[idx].openInfoWindowTabsHtml(marker_html[idx]);}';
			$_output .= 'else{markers[idx].openInfoWindowHtml(marker_html[idx]);}}' . "\n";
		}
		$_output .= 'function showInfoWindow(idx,html){map.centerAtLatLng(points[idx]);markers[idx].openInfoWindowHtml(html);}' . "\n";
		if ( $this->directions )
		{
			$_output .= 'function tohere(idx){markers[idx].openInfoWindowHtml(to_htmls[idx]);}' . "\n";
			$_output .= 'function fromhere(idx){markers[idx].openInfoWindowHtml(from_htmls[idx]);}' . "\n";
		}

		if($this->_mootools) {
			$_output .= "\nfunction unloadGoogleMaps(){window.addEvent('unload', function(){GUnload();});}\n";
			$_output .= "window.addEvent('domready', function(){if($('".$this->map_id."')){onLoad();unloadGoogleMaps();}});\n";
		}
		$_output .= "\n//]]>\n";
		$_output .= "</script>\n";
		return $_output;
	}

	/**
	* overridable function for generating js to add markers
	*/
	function getAddMarkersJS()
	{
		$SINGLE_TAB_WIDTH = 88; // constant: width in pixels of each tab heading (set by google)
		$i = 0;
		$_output = '';
		foreach( $this->_markers as $_marker )
		{
			if ( is_array( $_marker['html'] ) )
			{
				// warning: you can't have two tabs with the same header. but why would you want to?
				$ti = 0;
				$num_tabs = count( $_marker['html'] );
				$tab_obs = array();
				foreach( $_marker['html'] as $tab => $info )
				{
					if ( $ti == 0 && $num_tabs > 2 )
					{
						$width_style = sprintf( ' style=\"width: %spx\"', $num_tabs * $SINGLE_TAB_WIDTH );
					}
					else
					{
						$width_style = '';
					}
					$tab = str_replace( '"', '\"', $tab );
					$info = str_replace( '"', '\"', $info );
					$info = str_replace( array( "\n", "\r" ), "", $info );
					$tab_obs[] = sprintf( 'new GInfoWindowTab("%s", "%s")', $tab, '<div id=\"gmapmarker\"' . $width_style . '>' . $info . '</div>' );
					$ti++;
				}
				$iw_html = '[' . join( ',', $tab_obs ) . ']';
			}
			else
			{
				$iw_html = sprintf( '"%s"', str_replace( '"', '\"', '<div id="gmapmarker">' . str_replace( array( "\n", "\r" ), "", $_marker['html'] ) . '</div>' ) );
			}

			// make it smaller, save all JS vars and do functions call direct in map.addOverlay
			$_p  = 'new GLatLng('.$_marker['lat'].','.$_marker['lon'].')';
			$_m  = 'createMarker('.$_p.',"'.str_replace( '"', '\"', $_marker['title'] );
			$_m .= '",'.str_replace( '/', '\/', $iw_html ).', '.$i.',"'.str_replace( '"', '\"', $_marker['tooltip'] ).'")';
			$_output .= 'map.addOverlay('.$_m.');' . "\n";
			$i++;
		}
		return $_output;
	}

	/**
	* overridable function to generate polyline js
	*/
	function getPolylineJS()
	{
		$_output = '';
		foreach( $this->_polylines as $_polyline )
		{
			$_output .= sprintf( 'var polyline = new GPolyline([new GLatLng(%s,%s),new GLatLng(%s,%s)],"%s",%s,%s);',
				$_polyline['lat1'], $_polyline['lon1'], $_polyline['lat2'], $_polyline['lon2'], $_polyline['color'], $_polyline['weight'], $_polyline['opacity'] / 100.0 ) . "\n";
			$_output .= 'map.addOverlay(polyline);' . "\n";
		}
		return $_output;
	}

	/**
	* overridable function to generate the js for the js function for creating a marker.
	*/
	function getCreateMarkerJS()
	{
		$_output = 'function createMarker(point, title, html, n, tooltip) {' . "\n";
		$_output .= 'if(n >= ' . sizeof( $this->_icons ) . ') { n = ' . ( sizeof( $this->_icons ) - 1 ) . "; }\n";
		if ( !empty( $this->_icons ) )
		{
			$_output .= 'var marker = new GMarker(point,{\'icon\': icon[n], \'title\': tooltip});' . "\n";
		}
		else
		{
			$_output .= 'var marker = new GMarker(point,{\'title\': tooltip});' . "\n";
		}
		// TODO: make it so you can specify which tab you want the directions in.
		if ( $this->directions )
		{
			// WARNING: If you are using a tabbed info window AND directions: this uses an UNDOCUMENTED field
			// of the GInfoWindowTab object, contentElem. Google may CHANGE this name or other aspects of their
			// GInfoWindowTab implementation without warning and BREAK this code.
			// NOTE: If you are NOT using a tabbed info window, you'll be fine.
			$_output .= 'var tabFlag = isArray(html);' . "\n";
			$_output .= 'if(!tabFlag) { html = [{"contentElem": html}]; }' . "\n";
			$_output .= sprintf( "to_htmls[counter] = html[0].contentElem + '<form class=\"gmapDir\" id=\"gmapDirTo\" style=\"white-space: nowrap;\" action=\"http://maps.google.de/maps\" method=\"get\" target=\"_blank\">' +
                     '<span class=\"gmapDirHead\" id=\"gmapDirHeadTo\">%s<strong>%s<'+'/strong> - <a href=\"javascript:fromhere(' + counter + ')\">%s<' + '/a><' + '/span>' +
                     '<p class=\"gmapDirItem\" id=\"gmapDirItemTo\"><label for=\"gmapDirSaddr\" class=\"gmapDirLabel\" id=\"gmapDirLabelTo\">%s<br /' + '><' + '/label>' +
                     '<input type=\"text\" size=\"40\" maxlength=\"40\" name=\"saddr\" class=\"gmapTextBox\" id=\"gmapDirSaddr\" value=\"\" /' + '>' +
                     '<span class=\"gmapDirBtns\" id=\"gmapDirBtnsTo\"><input value=\"%s\" type=\"%s\" class=\"gmapDirButton\" id=\"gmapDirButtonTo\" /' + '><' + '/span><' + '/p>' +
                     '<input type=\"hidden\" name=\"daddr\" value=\"' +
                     point.y + ',' + point.x + \"(\" + title.replace(new RegExp(/\"/g),'&quot;') + \")\" + '\" /' + '><' + '/form>';
                      from_htmls[counter] = html[0].contentElem + '<form class=\"gmapDir\" id=\"gmapDirFrom\" style=\"white-space: nowrap;\" action=\"http://maps.google.com/maps\" method=\"get\" target=\"_blank\">' +
                     '<span class=\"gmapDirHead\" id=\"gmapDirHeadFrom\">%s<a href=\"javascript:tohere(' + counter + ')\">%s<'+'/a> - <strong>%s<' + '/strong><' + '/span>' +
                     '<p class=\"gmapDirItem\" id=\"gmapDirItemFrom\"><label for=\"gmapDirSaddr\" class=\"gmapDirLabel\" id=\"gmapDirLabelFrom\">%s<br /' + '><' + '/label>' +
                     '<input type=\"text\" size=\"40\" maxlength=\"40\" name=\"daddr\" class=\"gmapTextBox\" id=\"gmapDirSaddr\" value=\"\" /' + '>' +
                     '<span class=\"gmapDirBtns\" id=\"gmapDirBtnsFrom\"><input value=\"%s\" type=\"%s\" class=\"gmapDirButton\" id=\"gmapDirButtonFrom\" /' + '><' + '/span><' + '/p>' +
                     '<input type=\"hidden\" name=\"saddr\" value=\"' +
                     point.y + ',' + point.x + encodeURIComponent(\"(\" + title.replace(new RegExp(/\"/g),'&quot;') + \")\") + '\" /' + '><' + '/form>';
                     html[0].contentElem = html[0].contentElem + '<p /' + '><div id=\"gmapDirHead\" class=\"gmapDir\" style=\"white-space: nowrap;\">%s<a href=\"javascript:tohere(' + counter + ')\">%s<' + '/a> - <a href=\"javascript:fromhere(' + counter + ')\">%s<' + '/a><' + '/div>';\n",
				$this->driving_dir_text['dir_text'],
				$this->driving_dir_text['dir_tohere'],
				$this->driving_dir_text['dir_fromhere'],
				$this->driving_dir_text['dir_to'],
				$this->driving_dir_text['to_button_value'],
				$this->driving_dir_text['to_button_type'],
				$this->driving_dir_text['dir_text'],
				$this->driving_dir_text['dir_tohere'],
				$this->driving_dir_text['dir_fromhere'],
				$this->driving_dir_text['dir_from'],
				$this->driving_dir_text['from_button_value'],
				$this->driving_dir_text['from_button_type'],
				$this->driving_dir_text['dir_text'],
				$this->driving_dir_text['dir_tohere'],
				$this->driving_dir_text['dir_fromhere']
				);
			$_output .= 'if(!tabFlag) { html = html[0].contentElem; }';
		}

		if ( $this->info_window )
		{
			$_output .= sprintf( 'if(isArray(html)) { GEvent.addListener(marker, "%s", function() { marker.openInfoWindowTabsHtml(html); }); }', $this->window_trigger ) . "\n";
			$_output .= sprintf( 'else { GEvent.addListener(marker, "%s", function() { marker.openInfoWindowHtml(html); }); }', $this->window_trigger ) . "\n";
		}
		$_output .= 'points[counter] = point;' . "\n";
		$_output .= 'markers[counter] = marker;' . "\n";
		if ( $this->sidebar || $this->pointlist ) // pointlist too
		{
			$_output .= 'marker_html[counter] = html;' . "\n";
			if ( $this->sidebar )
			{
				$_output .= "sidebar_html += '<li class=\"gmapSidebarItem\" id=\"gmapSidebarItem_'+ counter +'\"><a href=\"javascript:click_sidebar(' + counter + ')\">' + title + '<\/a><\/li>';" . "\n";
			}
			if ( $this->pointlist )
			{
				$_output .= "pointlist_html += '<option value=\"' + counter + '\">' + title + '<\/option>';" . "\n";
			}
		}
		$_output .= 'counter++;' . "\n";
		$_output .= 'return marker;' . "\n";
		$_output .= '}' . "\n";
		return $_output;
	}

	/**
	* print map (put at location map will appear)
	*/
	function printMap()
	{
		echo $this->getMap();
	}

	/**
	* return map
	*/
	function getMap()
	{
		if($this->_mootools) {

			$js_alert = empty( $this->js_alert ) ? '' : '<span class="' . $this->map_id . '-jsalert">' . $this->js_alert . '</span>';

			if ( strlen( $this->width ) > 0 && strlen( $this->height ) > 0 )
			{
				$_output  = sprintf( '<div id="%s" style="width:%s;height:%s">', $this->map_id, $this->width, $this->height );
			}
			else
			{
				$_output  = sprintf( '<div id="%s">', $this->map_id );
			}

			$_output .= $js_alert . '</div>';

		} else {

			$_output  = '<script type="text/javascript" charset="utf-8">' . "\n" . '//<![CDATA[' . "\n";
			$_output .= 'if (GBrowserIsCompatible()) {' . "\n";
			if ( strlen( $this->width ) > 0 && strlen( $this->height ) > 0 )
			{
				$_output .= sprintf( 'document.write(\'<div id="%s" style="width:%s;height:%s"><\/div>\');', $this->map_id, $this->width, $this->height ) . "\n";
			}
			else
			{
				$_output .= sprintf( 'document.write(\'<div id="%s"><\/div>\');', $this->map_id ) . "\n";
			}
			$_output .= '}';

			if ( !empty( $this->browser_alert ) )
			{
				$_output .= ' else {' . "\n";
				$_output .= sprintf( 'document.write(\'%s\');', str_replace( '/', '\/', $this->browser_alert ) ) . "\n";
				$_output .= '}' . "\n";
			}

			$_output .= '//]]>' . "\n" . '</script>' . "\n";

			if ( !empty( $this->js_alert ) )
			{
				$_output .= '<noscript>' . $this->js_alert . '</noscript>' . "\n";
			}

		}

		return $_output;
	}

	/**
	* print sidebar (put at location sidebar will appear)
	*/
	function printSidebar()
	{
		echo $this->getSidebar();
	}

	/**
	* return sidebar html
	*/
	function getSidebar()
	{
		return sprintf( '<div id="%s"></div>', $this->sidebar_id ) . "\n";
	}

	/**
	* get the geocode lat/lon points from given address
	* look in cache first, otherwise get from Yahoo
	*
	* @param string $address
	*/
	function getGeocode( $address )
	{
		$this->Geocode_Placemark = array();

		if ( empty( $address ) ) {
			return false;
		}

		$_geocode = $this->getCache( $address );

		if ( $_geocode === false )
		{
			$_geocode = $this->geoGetCoords( $address );

			if ( $_geocode !== false )
			{
				if( count($this->Geocode_Placemark) > 1) {
					return false;
				}
				$this->putCache( $address, $_geocode['lon'], $_geocode['lat'] );
			}
		}

		return $_geocode;
	}

	/**
	* get the geocode lat/lon points from cache for given address
	*
	* @param string $address
	*/
	function getCache( $address )
	{
		$_ret = array();
		$_row = _dbQuery( 'SELECT lon,lat FROM ' . DB_PREPEND . $this->_db_cache_table . " WHERE address = " . _dbEscape( $address ) );
		if ( isset( $_row[0] ) ) {

			$_ret['lon'] = $_row[0]['lon'];
			$_ret['lat'] = $_row[0]['lat'];

		} elseif( $_row === false && $this->_db_cache_table ) {

			$sql  = 'CREATE TABLE IF NOT EXISTS `' . DB_PREPEND . $this->_db_cache_table . '` (';
			$sql .= '`address` varchar(255) NOT NULL, ';
			$sql .= '`lon` double DEFAULT NULL, ';
			$sql .= '`lat` double DEFAULT NULL, ';
			$sql .= 'PRIMARY KEY (`address`), ';
			$sql .= 'KEY `lon` (`lon`), ';
			$sql .= 'KEY `lat` (`lat`) ';
			$sql .= ')';

			_dbQuery($sql, 'CREATE');

		}
		return isset( $_ret['lon'] ) ? $_ret : false;
	}

	/**
	* put the geocode lat/lon points into cache for given address
	*
	* @param string $address
	* @param string $lon the map latitude (horizontal)
	* @param string $lat the map latitude (vertical)
	*/
	function putCache( $address, $lon, $lat )
	{
		if ( strlen( $address ) == 0 || strlen( $lon ) == 0 || strlen( $lat ) == 0 ) {
			return false;
		}
		_dbInsert( $this->_db_cache_table, array( 'address' => $address, 'lon' => $lon, 'lat' => $lat ), 'LOW_PRIORITY' );

		return true;
	}

	/**
	* get geocode lat/lon points for given address from Yahoo
	*
	* @param string $address
	*/
    function geoGetCoords($address,$depth=0) {

		$_coords = array('lon'=>NULL, 'lat'=>NULL);

        switch($this->lookup_service) {

            case 'GOOGLE':

				// use JSON
				if($this->use_json) {

					// changed to JSON, it's more accurate
					$_url = sprintf( 'http://%s/maps/geo?&q=%s&output=json&key=%s', $this->lookup_server['GOOGLE'], rawurlencode( $address ), $this->api_key );
					$_result = $this->fetchURL( $_url );

					if ( $_result ) {

						$_result_parts = json_decode( $_result );

						if(!isset($_result_parts->Placemark[0]) || !isset($_result_parts->Status->code) || $_result_parts->Status->code != 200) {
							return false;
						}

						$_coords['lat']		= $_result_parts->Placemark[0]->Point->coordinates[1];
						$_coords['lon']		= $_result_parts->Placemark[0]->Point->coordinates[0];

						$this->Geocode_Placemark = $_result_parts->Placemark;

					}

				// Use CSV
				} else {

					$_url = sprintf('http://%s/maps/geo?&q=%s&output=csv&key=%s',$this->lookup_server['GOOGLE'],rawurlencode($address),$this->api_key);

					if($_result = $this->fetchURL($_url)) {

						$_result_parts = explode(',',$_result);
						if($_result_parts[0] != 200) {
							return false;
						}
						$_coords['lat'] = $_result_parts[2];
						$_coords['lon'] = $_result_parts[3];
					}

				}

                break;

            case 'YAHOO':
            default:

                $_url = sprintf('http://%s/MapsService/V1/geocode?appid=%s&location=%s', $this->lookup_server['YAHOO'], $this->app_id,rawurlencode($address));

                if($_result = $this->fetchURL($_url)) {
                    if (preg_match('!<Latitude>(.*)</Latitude><Longitude>(.*)</Longitude>!U', $_result, $_match)) {
                        $_coords['lon'] = $_match[2];
                        $_coords['lat'] = $_match[1];
                    }
                }

                break;
        }

		return $_coords;
    }

	/**
	* fetch a URL. Override this method to change the way URLs are fetched.
	*
	* @param string $url
	*/
	function fetchURL( $url )
	{
		if($this->_debug && $this->_debug_file) {
			@file_put_contents($this->_debug_file, date('Y/m/d H:i:s') . ' - ' . $url . LF, FILE_APPEND);
		}
		return file_get_contents( $url );
	}

	/**
	* get distance between to geocoords using great circle distance formula
	*
	* @param float $lat1
	* @param float $lat2
	* @param float $lon1
	* @param float $lon2
	* @param float $unit M=miles, K=kilometers, N=nautical miles, I=inches, F=feet
	*/
	function geoGetDistance( $lat1, $lon1, $lat2, $lon2, $unit = 'M' )
	{
		// calculate miles
		$M = 69.09 * rad2deg( acos( sin( deg2rad( $lat1 ) ) * sin( deg2rad( $lat2 ) ) + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * cos( deg2rad( $lon1 - $lon2 ) ) ) );

		switch ( strtoupper( $unit ) )
		{
			case 'K':
				// kilometers
				return $M * 1.609344;
            case 'N':
				// nautical miles
				return $M * 0.868976242;
            case 'F':
				// feet
				return $M * 5280;
            case 'I':
				// inches
				return $M * 63360;
            case 'M':
			default:
				// miles
				return $M;
        }
	}

	/**
	* get distance between two geocoords using great circle distance formula and return in kilometer (KM)
	*
	* @param float $lat1
	* @param float $lat2
	* @param float $lon1
	* @param float $lon2
	*/
	function geoGetDistanceInKM(  $lat1, $lon1, $lat2, $lon2 )
	{
		$earthRadius = 6371.0;
		return acos( ( sin( deg2rad($lat1) ) * sin( deg2rad($lat2) ) ) + ( cos( deg2rad($lat1) ) * cos( deg2rad($lat2) ) * cos( deg2rad($lon1 - $lon2) ) ) ) * $earthRadius;
	}
}
