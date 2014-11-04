<?php
/*
 * Config file to set basic options
 * 
 * Created by Mark Walker (AWcode) 2014
 */


/* 
 * Folder path for AWS PHP SDK
 * Relative or absolute paths allowed
 * (optional in config file - required in constructor if blank)
 */
$aws_cf_config['sdk_path'] = "aws-php-sdk";

/* 
 * Cloudflare IPs URL
 * (optional in config file - required in constructor if blank)
 */
$aws_cf_config['cloudflare_url'] = "https://www.cloudflare.com/ips-v4";

/* 
 * IAM security credentials
 * (optional in config file - required in constructor if blank)
 */
$aws_cf_config['access_key_id'] = "";
$aws_cf_config['secret_access_key'] = "";

/* 
 * Region
 * (optional in config file - required in constructor if blank)
 */
$aws_cf_config['region'] = "";


/* 
 * Default ports to open
 * (optional)
 */
$aws_cf_config['open_ports'] = "";

/* 
 * Default Security Group
 * (optional)
 */
$aws_cf_config['security_group'] = "";


?>
