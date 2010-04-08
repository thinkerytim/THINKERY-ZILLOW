<?php
/**
 * @copyright Copyright (C) 2010 The Thinkery LLC - www.thethinkery.net. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');
 
class ModIpZillowHelper
{
    
    // this function grabs the zpid and other data for a given address
	public function GetSearchResults( $params ){
		
		# required params: zwsid, address, citystatezip
		
		$query_string = "";
		
		$Zparams = array(
			'zws-id' 		=> $params['zws-id'],
			'address' 		=> $params['address'],
			'citystatezip' 	=> $params['citystatezip']
		);
		
		foreach ($Zparams as $key => $value) {
			$query_string .= "$key=" . urlencode($value) . "&";
		}		
		
		$url = "http://www.zillow.com/webservice/GetSearchResults.htm?$query_string";	
		
                $ch = curl_init();
                $timeout = 5;
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
                $data = curl_exec($ch);
                curl_close($ch);

		$output = new SimpleXMLElement($data);
		return $output;
	}
	
	// this function grabs the chart for a zpid 
	public function GetChart( $params ){
		
		# required params: zwsid, zpid, unit_type, width, height, duration
		
		$query_string = "";
		
		$Zparams = array(
			'zws-id' 		=> $params['zws-id'],
			'zpid' 			=> $params['zpid'],
			'unit-type' 	=> $params['unit_type'],
			'width' 		=> $params['width'],
			'height'		=> $params['height'],
			'chartDuration' => $params['duration']
		);
		
		foreach ($Zparams as $key => $value) {
			$query_string .= "$key=" . urlencode($value) . "&";
		}		
		
		$url = "http://www.zillow.com/webservice/GetChart.htm?$query_string";
		
                $ch = curl_init();
                $timeout = 5;
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
                $data = curl_exec($ch);
                curl_close($ch);

		$output = new SimpleXMLElement($data);
		if ($output->message[0]->code != 0){
			return "There was a problem with the Zillow Chart request-- Error code " . $output->message[0]->code . "<br />" . $url;
		}else{
			return $output->response[0]->url;
		}
	}	
	
	// this function grabs the region chart
	public function GetRegionChart( $params ){
		
		# required params: zwsid, zpid, unit_type, width, height, duration, city, state, zip, type
		
		$query_string = "";
		
		$Zparams = array(
			'zws-id'		=> $params['zws-id'],
			'unit-type' 	=> $params['unit_type'],
			'width'			=> $params['width'],
			'height'		=> $params['height'],
			'chartDuration' => $params['duration']
		);
		
		
		if ($params['type'] == 'city'){
			$Zparams['city'] = $params['city'];
			$Zparams['state'] = $params['state'];
		} elseif ($params['type'] == 'zip') {
			$Zparams['zip'] = $params['zip'];
		}
							
		foreach ($Zparams as $key => $value) {
			$query_string .= "$key=" . urlencode($value) . "&";
		}		
		
		$url = "http://www.zillow.com/webservice/GetRegionChart.htm?$query_string";
		
                $ch = curl_init();
                $timeout = 5;
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
                $data = curl_exec($ch);
                curl_close($ch);

		$output = new SimpleXMLElement($data);
		if ($output->message[0]->code != 0){
			return "There was a problem with the Zillow Chart request-- Error code " . $output->message[0]->code . "<br />" . $url;
		}else{
			return $output->response[0]->url;
		}
	}	
	
	// this function grabs the comps a zpid
	public function GetComps( $params ){
		
		# required params: zwsid, zpid, count
		
		$query_string = "";
		$i = 0;		
		$count = $params['count'];
		
		$Zparams = array(
			'zws-id' 	=> $params['zws-id'],
			'zpid' 		=> $params['zpid'],
			'count' 	=> $params['count']
		);
		
		foreach ($Zparams as $key => $value) {
			$query_string .= "$key=" . urlencode($value) . "&";
		}		
		
		$url = "http://www.zillow.com/webservice/GetComps.htm?$query_string";	
		
                $ch = curl_init();
                $timeout = 5;
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
                $data = curl_exec($ch);
                curl_close($ch);

		$output = new SimpleXMLElement($data);
		if ($output->message[0]->code != 0){
			return "There was a problem with the Zillow Comps request-- Error code " . $output->message[0]->code . "<br />" . $url;
		}else{	
			
			$html = "<div class=" . $init_params['class'] . "><table>";
			$html .= "<thead><tr><td><b>Address:</b></td><td>&nbsp;</td><td><b>Valuation:</b></td></tr></thead>";
			$html .= "<tbody>";
			while ( $i < $count ) {
				$html .= "<tr><td><a href=\"" . $output->response->properties->comparables->comp[$i]->links->homedetails . "\" target=\"_blank\">" . $output->response->properties->comparables->comp[$i]->address[0]->street . ", " . $output->response->properties->comparables->comp[$i]->address[0]->city;
				$html .= ", " . $output->response->properties->comparables->comp[$i]->address[0]->state;
				$html .= ", " . $output->response->properties->comparables->comp[$i]->address[0]->zipcode;
				$html .= "</a></td><td>&nbsp;</td>";
				$html .= "<td>$" . number_format($output->response->properties->comparables->comp[$i]->zestimate->amount) . "</td></tr>";
				 
				$i++;
			}			
			$html .= "</tbody></table></div>";		
			
			return $html;
		}
	}
 
} //end ModIpZillowHelper
?>
