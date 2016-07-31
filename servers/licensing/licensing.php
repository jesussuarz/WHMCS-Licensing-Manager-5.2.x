<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 * */
function licensing_ConfigOptions() {
	global $whmcs;
	/* if (!LICENSINGADDONLICENSE) {
		return array( "License Required" => array( "Type" => "na", "Description" => "You need to purchase the licensing addon from <a href=\"http://go.whmcs.com/94/licensing-addon\" target=\"_blank\">www.whmcs.com/addons/licensing-addon</a> before you can use this functionality. If you just purchased it recently, please <a href=\"configproducts.php?action=edit&id=" . $whmcs->get_req_var( "id" ) . "&tab=2&larefresh=1\">click here</a> to refresh this message" ) );
	}*/ 
	$supportupdatesaddons = "0|None,";
	global $id;
	$result = select_query( "tbladdons", "", "", "name", "ASC" );
	while ($data = mysql_fetch_array( $result )) {
		$addonid = $data['id'];
		$addonname = $data['name'];
		$addonpackages = $data['packages'];
		$addonpackages = explode( ",", $addonpackages );
		if (in_array( $id, $addonpackages )) {
			$supportupdatesaddons .= "" . $addonid . "|" . $addonname . ",";
		}
	}
	$supportupdatesaddons = substr( $supportupdatesaddons, 0, 0 - 1 );
	$configarray = array( "Key Length" => array( "Type" => "text", "Size" => "10", "Description" => "String Length eg. 10" ), "Key Prefix" => array( "Type" => "text", "Size" => "20", "Description" => "eg. Leased-" ), "Allow Reissue" => array( "Type" => "yesno", "Description" => "Tick to allow clients to self-reissue from the client area" ), "Allow Domain Conflict" => array( "Type" => "yesno", "Description" => "Tick to not validate Domains" ), "Allow IP Conflict" => array( "Type" => "yesno", "Description" => "Tick to not validate IPs" ), "Allow Directory Conflict" => array( "Type" => "yesno", "Description" => "Tick to not validate installation path" ), "Support/Updates Addon" => array( "Type" => "dropdown", "Options" => $supportupdatesaddons ), "Secret Key" => array( "Type" => "text", "Size" => "20", "Description" => "Used in MD5 Verification" ), "Free Trial" => array( "Type" => "yesno", "Description" => "Restricts license to one instance per Domain" ) );
	return $configarray;
}
function licensing_genkey($length, $prefix) {
	if (!$length) {
		$length = 15;
	}
	$seeds = "abcdef0123456789";
	$key = null;
	$seeds_count = strlen( $seeds ) - 1;
	$i = 5;
	while ($i < $length) {
		$key .= $seeds[rand( 0, $seeds_count )];
		++$i;
	}
	$licensekey = $prefix . $key;
	$result = select_query( "mod_licensing", "COUNT(*)", array( "licensekey" => $licensekey ) );
	$data = mysql_fetch_array( $result );
	if ($data[0]) {
		$licensekey = licensing_genkey( $length, $prefix );
	}
	return $licensekey;
}
function licensing_CreateAccount($params) {
	/* if (!LICENSINGADDONLICENSE) {
		return "Your WHMCS license key is not enabled to use the Licensing Addon yet. Navigate to Addons > Licensing Manager to resolve.";
	} */
	$result = select_query( "mod_licensing", "COUNT(*)", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );
	if ($data[0]) {
		return "A license has already been generated for this item";
	}
	$length = $params['configoption1'];
	$prefix = $params['configoption2'];
	$licensekey = licensing_genkey( $length, $prefix );
	insert_query( "mod_licensing", array( "serviceid" => $params['serviceid'], "licensekey" => $licensekey, "validdomain" => "", "validip" => "", "validdirectory" => "", "reissues" => "0", "status" => "Reissued" ) );
	updateService( array( "domain" => $licensekey, "username" => "", "password" => "" ) );
	$addonid = explode( "|", $params['configoption7'] );
	$addonid = $addonid[0];
	if ($addonid) {
		$result = select_query( "tblhosting", "orderid,paymentmethod", array( "id" => $params['serviceid'] ) );
		$data = mysql_fetch_array( $result );
		$orderid = $data['orderid'];
		$paymentmethod = $data['paymentmethod'];
		$result = select_query( "tbladdons", "", array( "id" => $addonid ) );
		$data = mysql_fetch_array( $result );
		$addonname = $data['name'];
		$result = select_query( "tblpricing", "", array( "relid" => $addonid, "type" => "addon", "currency" => $params['clientsdetails']['currency'] ) );
		$data2 = mysql_fetch_array( $result );
		$addonsetupfee = $data2['msetupfee'];
		$addonrecurring = $data2['monthly'];
		$addonbillingcycle = $data['billingcycle'];
		$addontax = $data['tax'];
		if ($addonbillingcycle == "Monthly") {
			$nextduedate = date( "Y-m-d", mktime( 0, 0, 0, date( "m" ) + 1, date( "d" ), date( "Y" ) ) );
		}
		else {
			if ($addonbillingcycle == "Quarterly") {
				$nextduedate = date( "Y-m-d", mktime( 0, 0, 0, date( "m" ) + 3, date( "d" ), date( "Y" ) ) );
			}
			else {
				if ($addonbillingcycle == "Semi-Annually") {
					$nextduedate = date( "Y-m-d", mktime( 0, 0, 0, date( "m" ) + 6, date( "d" ), date( "Y" ) ) );
				}
				else {
					if ($addonbillingcycle == "Annually") {
						$nextduedate = date( "Y-m-d", mktime( 0, 0, 0, date( "m" ) + 12, date( "d" ), date( "Y" ) ) );
					}
					else {
						if ($addonbillingcycle == "Biennially") {
							$nextduedate = date( "Y-m-d", mktime( 0, 0, 0, date( "m" ) + 24, date( "d" ), date( "Y" ) ) );
						}
						else {
							$nextduedate = "0000-00-00";
						}
					}
				}
			}
		}
		insert_query( "tblhostingaddons", array( "orderid" => $orderid, "hostingid" => $params['serviceid'], "addonid" => $addonid, "setupfee" => $addonsetupfee, "recurring" => $addonrecurring, "billingcycle" => $addonbillingcycle, "tax" => $addontax, "status" => "Active", "regdate" => "now()", "nextduedate" => $nextduedate, "nextinvoicedate" => $nextduedate, "paymentmethod" => $paymentmethod ) );
	}
	return "success";
}
function licensing_SuspendAccount($params) {
	/* if (!LICENSINGADDONLICENSE) {
		return "Your WHMCS license key is not enabled to use the Licensing Addon yet. Navigate to Addons > Licensing Manager to resolve.";
	}*/ 
	$result = select_query( "mod_licensing", "COUNT(*)", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );
	if (!$data[0]) {
		return "No license exists for this item";
	}
	update_query( "mod_licensing", array( "status" => "Suspended" ), array( "serviceid" => $params['serviceid'] ) );
	return "success";
}
function licensing_UnsuspendAccount($params) {
/*	if (!LICENSINGADDONLICENSE) {
		return "Your WHMCS license key is not enabled to use the Licensing Addon yet. Navigate to Addons > Licensing Manager to resolve.";
	} */
	$result = select_query( "mod_licensing", "COUNT(*)", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );
	if (!$data[0]) {
		return "No license exists for this item";
	}
	update_query( "mod_licensing", array( "status" => "Active" ), array( "serviceid" => $params['serviceid'] ) );
	return "success";
}
function licensing_TerminateAccount($params) {
	/* if (!LICENSINGADDONLICENSE) {
		return "Your WHMCS license key is not enabled to use the Licensing Addon yet. Navigate to Addons > Licensing Manager to resolve.";
	} */
	$result = select_query( "mod_licensing", "COUNT(*)", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );
	if (!$data[0]) {
		return "No license exists for this item";
	}
	update_query( "mod_licensing", array( "status" => "Expired" ), array( "serviceid" => $params['serviceid'] ) );
	return "success";
}
function licensing_AdminCustomButtonArray() {
	$buttonarray = array( "Reissue License" => "reissue", "Reset Reissues" => "reissuereset", "Revoke License" => "revoke", "Manage" => "manage" );
	return $buttonarray;
}
function licensing_ClientAreaCustomButtonArray() {
	$buttonarray = array( "Reissue License" => "reissue" );
	return $buttonarray;
}
function licensing_reissue($params) {
	/* if (!LICENSINGADDONLICENSE) {
		return "Your WHMCS license key is not enabled to use the Licensing Addon yet. Navigate to Addons > Licensing Manager to resolve.";
	} */
	$result = select_query( "mod_licensing", "id,status,reissues", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );
	if (!$data[0]) {
		return "No license exists for this item";
	}
	if (!$_SESSION['adminid'] && !$params['configoption3']) {
		return "This license key is not allowed to be reissued";
	}
	if ($data[1] != "Active") {
		return "License must be active to be reissued";
	}
	$maxreissues = get_query_val( "tbladdonmodules", "value", array( "module" => "licensing", "setting" => "maxreissues" ) );
	if (( !$_SESSION['adminid'] && $maxreissues ) && $maxreissues <= $data[2]) {
		return "The maximum number of reissues allowed has been reached for this license - please contact support";
	}
	update_query( "mod_licensing", array( "reissues" => "+1", "status" => "Reissued" ), array( "serviceid" => $params['serviceid'] ) );
	run_hook( "LicensingAddonReissue", array( "licenseid" => get_query_val( "mod_licensing", "id", array( "serviceid" => $params['serviceid'] ) ), "serviceid" => $params['serviceid'] ) );
	return "success";
}
function licensing_reissuereset($params) {
	/* if (!LICENSINGADDONLICENSE) {
		return "Your WHMCS license key is not enabled to use the Licensing Addon yet. Navigate to Addons > Licensing Manager to resolve.";
	} */
	$result = select_query( "mod_licensing", "id,status,reissues", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );
	if (!$data[0]) {
		return "No license exists for this item";
	}
	update_query( "mod_licensing", array( "reissues" => "0" ), array( "serviceid" => $params['serviceid'] ) );
	return "success";
}
function licensing_revoke($params) {
	/* if (!LICENSINGADDONLICENSE) {
		return "Your WHMCS license key is not enabled to use the Licensing Addon yet. Navigate to Addons > Licensing Manager to resolve.";
	}*/ 
	$result = select_query( "mod_licensing", "COUNT(*)", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );
	if (!$data[0]) {
		return "No license exists for this item";
	}
	delete_query( "mod_licensing", array( "serviceid" => $params['serviceid'] ) );
	update_query( "tblhosting", array( "domain" => "" ), array( "id" => $params['serviceid'] ) );
	return "success";
}
function licensing_manage($params) {
	$result = select_query( "mod_licensing", "id", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );
	if (!$data[0]) {
		return "No license exists for this item";
	}
	return "redirect|addonmodules.php?module=licensing&action=manage&id=" . $data[0];
}
function licensing_valid_input_clean($vals) {
	$vals = explode( ",", $vals );
	foreach ($vals as $k => $v) {
		$vals[$k] = trim( $v, "
" );
	}
	return implode( ",", $vals );
}
function licensing_AdminServicesTabFields($params) {
	global $aInt;
	/* if (!LICENSINGADDONLICENSE) {
		return "Your WHMCS license key is not enabled to use the Licensing Addon yet. Navigate to Addons > Licensing Manager to resolve.";
	} */
	$result = select_query( "mod_licensing", "", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );
	$licenseid = $data['id'];
	if ($licenseid) {
		$licensekey = $data['licensekey'];
		$validdomain = $data['validdomain'];
		$validip = $data['validip'];
		$validdirectory = $data['validdirectory'];
		$reissues = $data['reissues'];
		$status = $data['status'];
		$lastaccess = $data['lastaccess'];
		if ($lastaccess == "0000-00-00 00:00:00") {
			$lastaccess = "Never";
		}
		else {
			$lastaccess = fromMySQLDate( $lastaccess, "time" );
		}
		$statusoptions = "<option";
		if ($status == "Reissued") {
			$statusoptions .= " selected";
		}
		$statusoptions .= ">Reissued</option><option";
		if ($status == "Active") {
			$statusoptions .= " selected";
		}
		$statusoptions .= ">Active</option><option";
		if ($status == "Suspended") {
			$statusoptions .= " selected";
		}
		$statusoptions .= ">Suspended</option><option";
		if ($status == "Expired") {
			$statusoptions .= " selected";
		}
		$statusoptions .= ">Expired</option>";
		$result = select_query( "mod_licensinglog", "", array( "licenseid" => $licenseid ), "id", "DESC", "0,10" );
		while ($data = mysql_fetch_array( $result )) {
			$domain = $data['domain'];
			$ip = $data['ip'];
			$path = $data['path'];
			$message = $data['message'];
			$datetime = $data['datetime'];
			$datetime = fromMySQLDate( $datetime, true );
			$tabledata[] = array( $datetime, $domain, $ip, $path, $message );
		}
		$aInt->sortableTableInit( "nopagination" );
		$recentaccesslog = $aInt->sortableTable( array( "Date", "Domain", "IP", "Path", "Result" ), $tabledata );
		$fieldsarray = array( "Valid Domains" => "<textarea name=\"modulefields[0]\" rows=\"2\" cols=\"80\">" . $validdomain . "</textarea>", "Valid IPs" => "<textarea name=\"modulefields[1]\" rows=\"2\" cols=\"80\">" . $validip . "</textarea>", "Valid Directory" => "<textarea name=\"modulefields[2]\" rows=\"2\" cols=\"80\">" . $validdirectory . "</textarea>", "License Status" => "<select name=\"modulefields[3]\" id=\"licensestatus\">" . $statusoptions . "</select>", "Recent Access Log" => $recentaccesslog, "Number of Reissues" => $reissues, "Last Access" => $lastaccess );
		return $fieldsarray;
	}
}
function licensing_AdminServicesTabFieldsSave($params) {
	update_query( "mod_licensing", array( "validdomain" => licensing_valid_input_clean( $_POST['modulefields'][0] ), "validip" => licensing_valid_input_clean( $_POST['modulefields'][1] ), "validdirectory" => licensing_valid_input_clean( $_POST['modulefields'][2] ), "status" => $_POST['modulefields'][3] ), array( "serviceid" => $params['serviceid'] ) );
}
function licensing_ChangePackage($params) {
	/* if (!LICENSINGADDONLICENSE) {
		return "Your WHMCS license key is not enabled to use the Licensing Addon yet. Navigate to Addons > Licensing Manager to resolve.";
	} */
	$addonid = explode( "|", $params['configoption7'] );
	$addonid = $addonid[0];
	if ($addonid) {
		$currentaddon = get_query_val( "tblhostingaddons", "id", array( "hostingid" => $params['serviceid'], "addonid" => $addonid, "status" => "Active" ) );
		if (!$currentaddon) {
			$data = get_query_vals( "tblhosting", "billingcycle,paymentmethod", array( "id" => $params['serviceid'] ) );
			$paymentmethod = $data['paymentmethod'];
			$billingcycle = ($data['billingcycle'] == "One Time" ? "onetime" : strtolower( $data['billingcycle'] ));
			$orderid = get_query_val( "tblupgrades", "orderid", array( "type" => "package", "relid" => $params['serviceid'], "newvalue" => "" . $params['packageid'] . "," . $billingcycle ) );
			$data = get_query_vals( "tbladdons", "name,tax,billingcycle", array( "id" => $addonid ) );
			$addonname = $data['name'];
			$addonbillingcycle = $data['billingcycle'];
			$addontax = $data['tax'];
			$data = get_query_vals( "tblpricing", "", array( "relid" => $addonid, "type" => "addon", "currency" => $params['clientsdetails']['currency'] ) );
			$addonsetupfee = $data['msetupfee'];
			$addonrecurring = $data['monthly'];
			if ($addonbillingcycle == "Monthly") {
				$nextduedate = date( "Y-m-d", mktime( 0, 0, 0, date( "m" ) + 1, date( "d" ), date( "Y" ) ) );
			}
			else {
				if ($addonbillingcycle == "Quarterly") {
					$nextduedate = date( "Y-m-d", mktime( 0, 0, 0, date( "m" ) + 3, date( "d" ), date( "Y" ) ) );
				}
				else {
					if ($addonbillingcycle == "Semi-Annually") {
						$nextduedate = date( "Y-m-d", mktime( 0, 0, 0, date( "m" ) + 6, date( "d" ), date( "Y" ) ) );
					}
					else {
						if ($addonbillingcycle == "Annually") {
							$nextduedate = date( "Y-m-d", mktime( 0, 0, 0, date( "m" ) + 12, date( "d" ), date( "Y" ) ) );
						}
						else {
							if ($addonbillingcycle == "Biennially") {
								$nextduedate = date( "Y-m-d", mktime( 0, 0, 0, date( "m" ) + 24, date( "d" ), date( "Y" ) ) );
							}
							else {
								$nextduedate = "0000-00-00";
							}
						}
					}
				}
			}
			insert_query( "tblhostingaddons", array( "orderid" => $orderid, "hostingid" => $params['serviceid'], "addonid" => $addonid, "setupfee" => $addonsetupfee, "recurring" => $addonrecurring, "billingcycle" => $addonbillingcycle, "tax" => $addontax, "status" => "Active", "regdate" => "now()", "nextduedate" => $nextduedate, "nextinvoicedate" => $nextduedate, "paymentmethod" => $paymentmethod ) );
		}
	}
	return "success";
}
 /* if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}
if (defined( "LICENSINGADDONLICENSE" )) {
	exit( "License Hacking Attempt Detected" );
}
global $whmcs;
global $licensing;
if ($whmcs->get_req_var( "larefresh" )) {
	$licensing->forceRemoteCheck();
}
define( "LICENSINGADDONLICENSE", $licensing->isActiveAddon( "Licensing Addon" ) );
*/ ?>