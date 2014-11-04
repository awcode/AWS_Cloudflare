<?php
/*
 * Class to open EC2 security groups based on current cloudlfare IP's
 *
 * Requires: PHP-curl
 * Requires: AWS PHP SDK (bundled)
 * 
 * Created by Mark Walker (AWcode) 2014
 */

use Aws\Common\Aws;
use Aws\Common\Credentials\Credentials;

class AwsCf{
	private $config;
	private $aws;
	private $credentials;

	/* 
	 * public __construct
	 * $set_config 		array	(optional, overrides config file values if set)	
	 * return void
	 */

	public function __construct($set_config = array()){
		include_once("aws-cloudflare.config.php");
		if(is_array($set_config) && count($set_config) > 0){
			foreach($set_config as $k=>$v){
				$aws_cf_config[$k] = $v;
			}
		}
		$this->config = $aws_cf_config;
	
		require_once($this->config['sdk_path']."/aws-autoloader.php");
		


		$this->credentials = new Credentials($this->config['access_key_id'], $this->config['secret_access_key']);

		$this->aws = Aws::factory(array(
			'credentials'	=> $this->credentials,
			'region'	=> $this->config['region']
		));
	}

	/* 
	 * public open_port
	 * $ip_range_arr	array	(required - format is array(array("CidrIp"=>"1.2.3.4/24"))	  )	
	 * $port 		string	(required, but can be set in config file or class constructor)
	 * $protocol 		string	(optional, default tcp)
	 * $security_group	string	(required, but can be set in config file or class constructor)
	 * 
	 * return true on success, text on error
	 */

	public function open_port($ip_range_arr, $port="", $protocol="tcp", $security_group=""){
		$security_group = ((strlen($security_group))?$security_group:$this->config['security_group']);
		if($security_group == ""){return "No security Group set";}

		$port = (($port !="")?$port:$this->config['open_ports']);
		if($port == ""){return "No port set";}


		if(!is_array($ip_range_arr) || (count($ip_range_arr)==0) || ($ip_range_arr[0][0]['CidrIp'])){
			return "Invalid IP range Array";
		}
		
		$ec2 = $this->aws->get('ec2');
		
		try{
			$result = $ec2->authorizeSecurityGroupIngress(array(
				'GroupId' 	=> $security_group,
				'IpPermissions' => array(
					array(
						'IpProtocol' 	=> $protocol,
						'FromPort' 	=> $port,
						'ToPort'	=> $port,
						'IpRanges'	=> $ip_range_arr
					)
				)
			));
		}catch(\Aws\Ec2\Exception\Ec2Exception $e){ 
			if($this->config['verbose']){echo "A fatal error was caught ".$e->getResponse()."\n";}
			return "A fatal error was caught ".$e->getResponse();
		}

		return true;
	}

	/* 
	 * public close_port
	 * $ip_range_arr	array	(required - format is array(array("CidrIp"=>"1.2.3.4/24"))	  )	
	 * $port 		string	(required, but can be set in config file or class constructor)
	 * $protocol 		string	(optional, default tcp)
	 * $security_group	string	(required, but can be set in config file or class constructor)
	 * 
	 * return true on success, text on error
	 */

	public function close_port($ip_range_arr, $port="", $protocol="tcp", $security_group=""){
		$security_group = ((strlen($security_group))?$security_group:$this->config['security_group']);
		if($security_group == ""){return "No security Group set";}

		$port = (($port !="")?$port:$this->config['open_ports']);
		if($port == ""){return "No port set";}
		
		if($this->config['verbose']){echo "Close Port ".$port." protocol ".$protocol."  group ".$security_group."\n";}
		if($this->config['verbose'] > 1){print_r($ip_range_arr);}

		if(!is_array($ip_range_arr) || (count($ip_range_arr)==0) || ($ip_range_arr[0][0]['CidrIp'])){
			return "Invalid IP range Array";
		}
		
		$ec2 = $this->aws->get('ec2');
		
		try{
			$result = $ec2->revokeSecurityGroupIngress(array(
				'GroupId' 	=> $security_group,
				'IpPermissions' => array(
					array(
						'IpProtocol' 	=> $protocol,
						'FromPort' 	=> $port,
						'ToPort'	=> $port,
						'IpRanges'	=> $ip_range_arr
					)
				)
			));
		}catch(\Aws\Ec2\Exception\Ec2Exception $e){ 
			echo "A fatal error was caught ".$e->getResponse();
		}
		return true;
	}

	/* 
	 * public close_all_by_port
	 * $port 		string	(required, but can be set in config file or class constructor)
	 * $security_group	string	(required, but can be set in config file or class constructor)
	 * 
	 * return true on success, text on error
	 */

	public function close_all_by_port($port="", $security_group=""){
		$security_group = ((strlen($security_group))?$security_group:$this->config['security_group']);
		if($security_group == ""){return "No security Group set";}
		
		$port = (($port !="")?$port:$this->config['open_ports']);
		if($port == ""){return "No port set";}

		if($this->config['verbose']){echo "Close all by port ".$port." on ".$security_group."\n";}

		$ec2 = $this->aws->get('ec2');

		try{
			$result = $ec2->describeSecurityGroups(array(
				'GroupId' 	=> $security_group
			));
		}catch(\Aws\Ec2\Exception\Ec2Exception $e){ return "A fatal error was caught ".$e->getResponse();}		
		if($this->config['verbose'] > 1){print_r($result['SecurityGroups']);}
		
		foreach($result['SecurityGroups'] as $group){
			if($group['GroupId'] == $security_group){
				$all_group_rules = $group['IpPermissions'];
				break;
			}
		}
		

		if(is_array($all_group_rules) && (count($all_group_rules) > 0)){
			foreach($all_group_rules as $rule){
				if($this->config['verbose']){echo "Check Rule port ".$rule['FromPort']." to ".$rule['ToPort']."\n";}
				if($rule['FromPort'] == $port && $rule['ToPort'] == $port){
					if($this->config['verbose']){echo "Prepare Close Port ".$port." protocol ".$rule['IpProtocol']."  group ".$security_group."\n";}
					if($this->config['verbose'] > 1){print_r($rule['IpRanges']);}
					$this->close_port($rule['IpRanges'], $port, $rule['IpProtocol'], $security_group);
				}
			}
		}else{ 
			if($this->config['verbose']){echo "Error retrieving security group\n";}
			return "Error retrieving security group";
		}

		return true;
	}

	/* 
	 * public cloudflare_firewall
	 * $port 		string	(required, but can be set in config file or class constructor)
	 * $protocol 		string	(optional, default tcp)
	 * $security_group	string	(required, but can be set in config file or class constructor)
	 * $clear		boolean	(optional, controls whether existing rules for port are cleared first, default true)	
	 * 
	 * return true on success, text on error
	 */

	public function cloudflare_firewall($port="", $protocol="tcp", $security_group="", $clear=true){
		$security_group = ((strlen($security_group))?$security_group:$this->config['security_group']);
		if($security_group == ""){return "No security Group set";}

		$port = (($port !="")?$port:$this->config['open_ports']);
		if($port == ""){return "No port set";}


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->config['cloudflare_url']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$data = curl_exec($ch);
		curl_close($ch);

		$ip_range_arr_raw= explode("\n", $data);
		if(is_array($ip_range_arr_raw) && (count($ip_range_arr_raw) > 0)){
			foreach($ip_range_arr_raw as $ip){
				$ip_range_arr[] = array("CidrIp" => $ip);
			}
		}else{
			if($this->config['verbose']){echo "Error retrieving cloudflare IPs\n";}
			return "Error retrieving cloudflare IPs";
		}
		
		if($clear){$this->close_all_by_port($port, $security_group);}
		
		return $this->open_port($ip_range_arr, $port, $protocol, $security_group);
		
	}
}

?>
