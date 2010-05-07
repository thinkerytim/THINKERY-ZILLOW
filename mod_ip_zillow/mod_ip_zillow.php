<?php
/**
 * @copyright Copyright (C) 2010 The Thinkery LLC - www.thethinkery.net. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');
 
require_once(dirname(__FILE__).DS.'helper.php');
jimport( 'joomla.html.pane' );
if(!$params->get('zws-id')) {
	echo "Please provide a valid Zillow API ID";
	return;
}

if (JRequest::getVar('address')) {
	$address 		= JRequest::getString('address');
	$citystatezip 	= JRequest::getString('citystatezip');
	$render_tabs     = true;
}

$this->baseurl = JURI::root(true);
$document = & JFactory::getDocument();
$document->addStyleSheet($this->baseurl.'/modules/mod_ip_zillow/tmpl/ip_zillow.css');

$prop_info = false;

// get params
$init_params = array(
		'title'				=> $params->get('tabtitle'),
		'zws-id' 			=> $params->get('zws-id'),
		'width' 			=> $params->get('width'),
		'height'			=> $params->get('height'),
		'duration'			=> $params->get('chartDuration'),
		'unit_type' 		=> $params->get('unit-type'),
		'count' 			=> $params->get('count'),
		'show_info' 		=> $params->get('show_info'),
		'show_chart' 		=> $params->get('show_chart'),
		'show_region_chart' => $params->get('show_region_chart'),
		'show_comps' 		=> $params->get('show_comps'),
		'class'				=> $params->get('MODULECLASSSUFFIX')
		);

$zillow =& JPane::getInstance('tabs', array('startOffset'=>0));
echo $zillow->startPane( 'pane' );
echo $zillow->startPanel( "Input Address", 'zpanel1' );
	echo "<div class=" . $init_params['class'] . ">
			<table>
				<form name=\"zillow\" action=\"" . JFactory::getURI()->_uri . "\" method=\"post\">	
				<tr>
					<td><b>Address: </b></td><td>&nbsp;</td><td><input type=\"text\" name=\"address\" value=\"" . $address . "\"></td>
				</tr>
				<tr>								
					<td><b>City, State OR Zip: </b></td><td>&nbsp;</td><td><input type=\"text\" name=\"citystatezip\" value=\"" . $citystatezip . "\"></td>
				</tr>
				<tr>	
					<td><input type=\"Submit\" value=\"Submit\"></td><td>&nbsp;</td>
				</tr>
				</form>
		 </table>
		 </div>";
echo $zillow->endPanel();

if ($render_tabs) {
	$zwsid			= $params->get('zws-id'); 
	
	$info_params = array(
		'zws-id' 		=> $zwsid,
		'address' 		=> $address,
		'citystatezip' 	=> $citystatezip
		);
	
		$output = ModIpZillowHelper::GetSearchResults($info_params);
		if ($output->message[0]->code != 0){
			echo "There was a problem with the Zillow request-- Error code " . $output->message[0]->code . "<br /> Please check your address and try again.";
			return;
		}else{
		
			if ($init_params['show_info']){
				echo $zillow->startPanel( $init_params['title'], 'zpanel2' );
					if ($output) {
						
						$zpid	 		=	$output->response[0]->results[0]->result[0]->zpid;
						$neighborhood 	= 	$output->response[0]->results[0]->result[0]->localRealEstate[0]->region['name'];
						$city 			=	$output->response[0]->results[0]->result[0]->address[0]->city;
						$state 			=	$output->response[0]->results[0]->result[0]->address[0]->state;
						$zip 			=	$output->response[0]->results[0]->result[0]->address[0]->zipcode;
						
						echo "<div class=\"ip_zillowmod\">";
						echo "<table>";
						echo "<tr><td><b>Zestimate:</b></td><td>&nbsp;</td><td> $" . number_format($output->response[0]->results[0]->result[0]->zestimate[0]->amount) . "</td></tr>";
						echo "<tr><td><b>Changed:</b></td><td>&nbsp;</td><td> $" . number_format($output->response[0]->results[0]->result[0]->zestimate[0]->valueChange) . " in last " .  $output->response[0]->results[0]->result[0]->zestimate[0]->valueChange['duration'] . " days</td></tr>";
						echo "<tr><td><b>Last updated:</b></td><td>&nbsp;</td><td> " . $output->response[0]->results[0]->result[0]->zestimate[0]->{'last-updated'} . "</td></tr>";
						echo "<tr><td><b>Low value over period:</b></td><td>&nbsp;</td><td> $" . number_format($output->response[0]->results[0]->result[0]->zestimate[0]->valuationRange->low) . "</td></tr>";
						echo "<tr><td><b>High value over period:</b></td><td>&nbsp;</td><td> $" . number_format($output->response[0]->results[0]->result[0]->zestimate[0]->valuationRange->high) . "</td></tr>";
						echo "</table>";
						echo "</div>";
					} else {
						echo "No Chart found";
					}
				echo $zillow->endPanel();
			}
			
			if ($init_params['show_chart']){
				# zwsid, zpid, unit_type, width, duration
				$chart_params = array(
								'zws-id' 		=> $zwsid,
								'zpid' 			=> $zpid,
								'unit_type' 	=> $init_params['unit_type'],
								'width'			=> $init_params['width'],
								'height'			=> $init_params['height'],
								'duration'		=> $init_params['duration']
								);
				
				$chart = ModIpZillowHelper::GetChart( $chart_params );
				echo $zillow->startPanel( 'Zillow Property Value Chart', 'zpanel3' );
					if ($chart) {
						echo "<div class=\"ip_zillowmod\"><img src=\"" . $chart . "\"><br /><br />See more graphs and data about $address on Zillow.com.</div>";
					} else {
						echo "No Chart found";
					}
				echo $zillow->endPanel();
			}
			
			if ($init_params['show_region_chart']){
				# zwsid, zpid, unit_type, width, duration, city, state, zip, type
				$region_params = array(
								'zws-id' 		=> $zwsid,
								'zpid' 			=> $zpid,
								'unit_type' 	=> $init_params['unit_type'],
								'width'			=> $init_params['width'],
								'height'		=> $init_params['height'],
								'duration'		=> $init_params['duration'],
								'city'			=> $city,
								'state'			=> $state,
								'type'			=> 'city'
								);				
				
				$r_chart = ModIpZillowHelper::GetRegionChart( $region_params );
				echo $zillow->startPanel( 'Zillow Region Value Chart', 'zpanel4' );
					if ($r_chart) {
						echo "<div class=\"ip_zillowmod\"><img src=\"" . $r_chart . "\"><br /><br />See more graphs and data about $address on Zillow.com.</div>";
					} else {
						echo "No Chart found";
					}
				echo $zillow->endPanel();
			}
			
			if ($init_params['show_comps']){
				# zwsid, zpid, count
				$comp_params = array(
								'zws-id' 		=> $zwsid,
								'zpid' 			=> $zpid,
								'count' 		=> $init_params['count']
								);
				
				
				$comps = ModIpZillowHelper::GetComps( $comp_params );
				echo $zillow->startPanel( 'Zillow Comps', 'zpanel5' );
					echo $comps;
				echo $zillow->endPanel();
			}
	}		
} 

		echo $zillow->endPane();
		echo "<br /><br /><a href=\"http://zillow.com\" target=\"_blank\"><img src=\"http://www.zillow.com/widgets/GetVersionedResource.htm?path=/static/logos/Zillowlogo_150x40.gif\" width=\"150\" height=\"40\" alt=\"Zillow Real Estate Search\" /></a>";
		echo "<br /><br /><a href=\"http://www.thethinkery.net\" target=\"_blank\">Zillow module by The Thinkery</a>";
		return true;	
 
// include the template for display
require(JModuleHelper::getLayoutPath('mod_ip_zillow'));
?>



