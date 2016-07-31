<?php

function licensing_config() {
	$configarray = array("name" => "Licensing Manager", "description" => "License, restrict & distribute your own coding and applications using the same system we use for WHMCS.<br />Find out more & purchase @ <a href=\"http://go.whmcs.com/94/licensing-addon\" target=\"_blank\">www.whmcs.com/addons/licensing-addon</a>", "premium" => true, "version" => "3.0", "author" => "WHMCS", "language" => "english", "fields" => array());
	/* if (!LICENSINGADDONLICENSE) {
		$configarray['fields']['license'] = array("FriendlyName" => "License Check Failed", "Type" => "", "Description" => "You need to purchase the licensing addon from <a href=\"http://go.whmcs.com/94/licensing-addon\" target=\"_blank\">www.whmcs.com/addons/licensing-addon</a> before you can use this functionality. If you just purchased it recently, please <a href=\"configaddonmods.php?larefresh=1#licensing\">click here</a> to refresh this message");
	}
	else { */ 
		$configarray['fields'] = array("clientverifytool" => array("FriendlyName" => "Public License Verification Tool", "Type" => "yesno", "Description" => "Tick this box to enable the Client Area License Verification Tool (accessed via /index.php?m=licensing)"), "maxreissues" => array("FriendlyName" => "Maximum Allowed Reissues", "Type" => "text", "Size" => "4", "Default" => "10", "Description" => "Enter the maximum number of reissues you want to allow (abuse protection)"), "logprune" => array("FriendlyName" => "Auto Logs Prune", "Type" => "text", "Size" => "4", "Default" => "90", "Description" => "Enter the number of days to keep license access log history for"));
	/* } */
	return $configarray;
}
function licensing_activate() {
	$query = "CREATE TABLE `mod_licensing` (`id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`serviceid` INT(10) NOT NULL ,`licensekey` TEXT NOT NULL ,`validdomain` TEXT NOT NULL ,`validip` TEXT NOT NULL ,`validdirectory` TEXT NOT NULL ,`reissues` INT(1) NOT NULL ,`status` ENUM('Active', 'Reissued', 'Suspended', 'Expired') NOT NULL ,`lastaccess` datetime NOT NULL default '0000-00-00 00:00:00')";
	full_query($query);
	$query = "CREATE TABLE `mod_licensinglog` (`id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`licenseid` INT(10) NOT NULL ,`domain` TEXT NOT NULL ,`ip` TEXT NOT NULL ,`path` TEXT NOT NULL ,`message` TEXT NOT NULL ,`datetime` datetime NOT NULL default '0000-00-00 00:00:00')";
	full_query($query);
	$query = "CREATE TABLE `mod_licensingbans` (`id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`type` VARCHAR(1) NOT NULL ,`value` TEXT NOT NULL ,`notes` TEXT NOT NULL)";
	full_query($query);
}
function licensing_deactivate() {
	$query = "DROP TABLE `mod_licensing`";
	full_query($query);
	$query = "DROP TABLE `mod_licensinglog`";
	full_query($query);
	$query = "DROP TABLE `mod_licensingbans`";
	full_query($query);
}
function licensing_addon_valid_input_clean($vals) {
	$vals = explode(",", $vals);
	foreach ($vals as $k => $v) {
		$vals[$k] = trim($v, "\r\n\r\n");
	}
	return implode(",", $vals);
}
function licensing_output($vars) {
	global $whmcs;
	global $licensing;
	global $aInt;
	global $numrows;
	global $tabledata;
	global $orderby;
	global $order;
	global $page;
	global $limit;
	global $jscode;
	/* if (!LICENSINGADDONLICENSE) {
		if ($whmcs->get_req_var("refresh")) {
			$licensing->forceRemoteCheck();
			redir("module=licensing");
		} 
		echo "<div class=\"gracefulexit\">
Your WHMCS license key is not enabled to use the Licensing Addon yet.<br /><br />
You can find out more about it and purchase @ <a href=\"http://go.whmcs.com/94/licensing-addon\" target=\"_blank\">www.whmcs.com/addons/licensing-addon</a><br /><br />
If you have only recently purchased the addon, please <a href=\"addonmodules.php?module=licensing&refresh=1\">click here</a> to perform a license refresh.
</div>";
		return false;
	}  */
	$modulelink = $vars['modulelink'];
	$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : "");
	$id = (int)$_REQUEST['id'];
	echo "<style>
.licensinglinksbar {
    padding:10px 25px 10px 25px;
    background-color:#6CAD41;
    font-weight:bold;
    font-size: 14px;
    color: #5A7B2F;
    margin: 0 0 15px 0;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    -o-border-radius: 5px;
    border-radius: 5px;
}
.licensinglinksbar a {
    color: #fff;
    font-weight: normal;
}
</style>
<div class=\"licensinglinksbar\">
<a href=\"" . $modulelink . "\">Home</a> | <a href=\"" . $modulelink . "&action=list\">Search/Browse Licenses</a> | <a href=\"" . $modulelink . "&action=bans\">Ban Control</a> | <a href=\"" . $modulelink . "&action=log\">License Access Logs</a> | <a href=\"http://docs.whmcs.com/Licensing_Addon\" target=\"_blank\">Help</a>
</div>
";
	if (!$action) {
		echo "
<h2>Statistics</h2>
<table width=\"90%\" align=\"center\">
<tr><td width=\"33%\">
<div style=\"margin:0 25px;padding:15px;font-family:Trebuchet MS,Tahoma;text-align:center;font-size:20px;background-color:#E7F1C0;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">
Active Licenses<br />
";
		echo "<s";
		echo "trong>";
		echo get_query_val("mod_licensing", "COUNT(*)", "status='Reissued' OR status='Active'");
		echo "</strong>
</div>
</td><td width=\"33%\">
<div style=\"margin:0 25px;padding:15px;font-family:Trebuchet MS,Tahoma;text-align:center;font-size:20px;background-color:#F2E8BF;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">
Suspended Licenses<br />
";
		echo "<s";
		echo "trong>";
		echo get_query_val("mod_licensing", "COUNT(*)", "status='Suspended'");
		echo "</strong>
</div>
</td><td width=\"33%\">
<div style=\"margin:0 25px;padding:15px;font-family:Trebuchet MS,Tahoma;text-align:center;font-size:20px;background-color:#F2BFBF;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">
Expired Licenses<br />
";
		echo "<s";
		echo "trong>";
		echo get_query_val("mod_licensing", "COUNT(*)", "status='Expired'");
		echo "</strong>
</div>
</td></tr>
</table>
<table width=\"90%\" align=\"center\">
<tr><td width=\"50%\">
<div style=\"margin:0 25px;padding:15px;font-family:Trebuchet MS,Tahoma;text-align:center;font-size:20px;background-color:#efefef;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">
Total Licenses in Database<br />
";
		echo "<s";
		echo "trong>";
		echo get_query_val("mod_licensing", "COUNT(*)", "");
		echo "</strong>
</div>
</td><td width=\"50%\">
<div style=\"margin:0 25px;padding:15px;font-family:Trebuchet MS,Tahoma;text-align:center;font-size:20px;background-color:#efefef;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">
Accessed within the Past 30 Days<br />
";
		echo "<s";
		echo "trong>";
		echo get_query_val("mod_licensing", "COUNT(*)", "lastaccess>='" . date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y"))) . "'");
		echo "</strong>
</div>
</td></tr>
</table>
<br />
<h2>Search</h2>
<form method=\"post\" action=\"";
		echo $modulelink;
		echo "&action=list\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">Product/License</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"search_pid\"><option value=\"0\">- Any -</option>";
		$result = select_query("tblproducts", "id,name", array("servertype" => "licensing"), "name", "ASC");
		while ($data = mysql_fetch_array($result)) {
			echo "<option value=\"" . $data['id'] . "\">" . $data['name'] . "</option>";
		}
		echo "</select></td></tr>
<tr><td width=\"15%\" class=\"fieldlabel\">License Key</td><td class=\"fieldarea\"><input type=\"text\" name=\"search_licensekey\" size=\"30\" value=\"";
		echo $search_licensekey;
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">Domain</td><td class=\"fieldarea\"><input type=\"text\" name=\"search_domain\" size=\"30\" value=\"";
		echo $search_domain;
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">IP</td><td class=\"fieldarea\"><input type=\"text\" name=\"search_ip\" size=\"30\" value=\"";
		echo $search_ip;
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">Directory</td><td class=\"fieldarea\"><input type=\"text\" name=\"search_dir\" size=\"60\" value=\"";
		echo $search_dir;
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">Status</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"search_status\">
<option value=\"\">- Any -</option>
<option>Reissued</option>
<option>Active</option>
<option>Suspended</option>
<option>Expired</option>
</select></td></tr>
</table>
<p align=\"center\"><input type=\"submit\" value=\"Search\" class=\"button\" /></p>
</form>
";
		return null;
	}
	if ($action == "list") {
		echo "
<form method=\"post\" action=\"";
		echo $modulelink;
		echo "&action=list\">
<h2>Search/Browse Licenses</h2>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">Product/License</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"search_pid\"><option value=\"0\">- Any -</option>";
		$result = select_query("tblproducts", "id,name", array("servertype" => "licensing"), "name", "ASC");
		while ($data = mysql_fetch_array($result)) {
			echo "<option value=\"" . $data['id'] . "\"";
			if ($_REQUEST['search_pid'] == $data['id']) {
				echo " selected";
			}
			echo ">" . $data['name'] . "</option>";
		}
		echo "</select></td></tr>
<tr><td width=\"15%\" class=\"fieldlabel\">License Key</td><td class=\"fieldarea\"><input type=\"text\" name=\"search_licensekey\" size=\"30\" value=\"";
		echo $_REQUEST['search_licensekey'];
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">Domain</td><td class=\"fieldarea\"><input type=\"text\" name=\"search_domain\" size=\"30\" value=\"";
		echo $_REQUEST['search_domain'];
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">IP</td><td class=\"fieldarea\"><input type=\"text\" name=\"search_ip\" size=\"30\" value=\"";
		echo $_REQUEST['search_ip'];
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">Directory</td><td class=\"fieldarea\"><input type=\"text\" name=\"search_dir\" size=\"60\" value=\"";
		echo $_REQUEST['search_dir'];
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">Status</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"search_status\">
<option value=\"\">- Any -</option>
<option";
		if ($_REQUEST['search_status'] == "Reissued") {
			echo " selected";
		}
		echo ">Reissued</option>
<option";
		if ($_REQUEST['search_status'] == "Active") {
			echo " selected";
		}
		echo ">Active</option>
<option";
		if ($_REQUEST['search_status'] == "Suspended") {
			echo " selected";
		}
		echo ">Suspended</option>
<option";
		if ($_REQUEST['search_status'] == "Expired") {
			echo " selected";
		}
		echo ">Expired</option>
</select></td></tr>
</table>
<p align=\"center\"><input type=\"submit\" value=\"Search\" class=\"button\" /></p>
</form>
";
		$where = array();
		if ($_REQUEST['search_pid']) {
			$where['packageid'] = $_REQUEST['search_pid'];
		}
		if ($_REQUEST['search_licensekey']) {
			$where['licensekey'] = array("sqltype" => "LIKE", "value" => trim($_REQUEST['search_licensekey']));
		}
		if ($_REQUEST['search_domain']) {
			$where['validdomain'] = array("sqltype" => "LIKE", "value" => trim($_REQUEST['search_domain']));
		}
		if ($_REQUEST['search_ip']) {
			$where['validip'] = array("sqltype" => "LIKE", "value" => trim($_REQUEST['search_ip']));
		}
		if ($_REQUEST['search_dir']) {
			$where['validdirectory'] = array("sqltype" => "LIKE", "value" => trim($_REQUEST['search_dir']));
		}
		if ($_REQUEST['search_status']) {
			$where['status'] = $_REQUEST['search_status'];
		}
		$aInt->sortableTableInit("id", "ASC");
		if (!in_array($orderby, array("id", "licensekey", "validdomain", "validip", "lastaccess", "status"))) {
			$orderby = "id";
		}
		$result = select_query("mod_licensing", "mod_licensing.*", $where, $orderby, $order, "", "tblhosting ON tblhosting.id=mod_licensing.serviceid");
		$numrows = mysql_num_rows($result);
		if (count($where) && $numrows == 1) {
			$data = mysql_fetch_array($result);
			$id = $data['id'];
			redir("module=licensing&action=manage&id=" . $id);
		}
		$result = select_query("mod_licensing", "mod_licensing.*", $where, $orderby, $order, $page * $limit . ("," . $limit), "tblhosting ON tblhosting.id=mod_licensing.serviceid");
		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$serviceid = $data['serviceid'];
			$licensekey = $data['licensekey'];
			$validdomain = $data['validdomain'];
			$validip = $data['validip'];
			$validdirectory = $data['validdirectory'];
			$status = $data['status'];
			$lastaccess = $data['lastaccess'];
			if ($lastaccess == "0000-00-00 00:00:00") {
				$lastaccess = "Never";
			}
			else {
				$lastaccess = fromMySQLDate($lastaccess, "time");
			}
			$validdomain = explode(",", $validdomain);
			$validip = explode(",", $validip);
			$validdirectory = explode(",", $validdirectory);
			$validdomain = $validdomain[0];
			$validip = $validip[0];
			$validdirectory = $validdirectory[0];
			$tabledata[] = array("<a href=\"clientshosting.php?id=" . $serviceid . "\" target=\"_blank\">" . $licensekey . "</a>", $validdomain, $validip, $lastaccess, $status, "<a href=\"" . $modulelink . "&action=manage&id=" . $id . "\"><img src=\"images/edit.gif\" border=\"0\"></a>");
		}
		echo $aInt->sortableTable(array(array("licensekey", "License Key"), array("validdomain", "Valid Domains"), array("validip", "Valid IPs"), array("lastaccess", "Last Access"), array("status", "Status"), ""), $tabledata);
		return null;
	}
	if ($action == "manage") {
		if ($_REQUEST['save']) {
			update_query("mod_licensing", array("validdomain" => licensing_addon_valid_input_clean($_REQUEST['validdomain']), "validip" => licensing_addon_valid_input_clean($_REQUEST['validip']), "validdirectory" => licensing_addon_valid_input_clean($_REQUEST['validdirectory']), "reissues" => $_REQUEST['reissues'], "status" => $_REQUEST['status']), array("id" => $id));
			redir("module=licensing&action=manage&id=" . $id);
		}
		$result = select_query("mod_licensing", "", array("id" => $id));
		$data = mysql_fetch_array($result);
		$id = $data['id'];
		if (!$id) {
			echo "<p>License Not Found. Please go back and try again.</p>";
			return false;
		}
		$serviceid = $data['serviceid'];
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
			$lastaccess = fromMySQLDate($lastaccess, "time");
		}
		$data = get_query_vals("tblhosting", "tblproductgroups.name,tblproducts.name", array("tblhosting.id" => $serviceid), "", "", "", "tblproducts ON tblhosting.packageid=tblproducts.id INNER JOIN tblproductgroups ON tblproductgroups.id=tblproducts.gid");
		$productname = $data[0] . " - " . $data[1];
		echo "
<h2>Manage License Key</h2>
<form method=\"post\" action=\"";
		echo $modulelink;
		echo "&action=manage&id=";
		echo $id;
		echo "\">
<input type=\"hidden\" name=\"save\" value=\"true\" />
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\" width=\"20%\">Product/Service</td><td class=\"fieldarea\" height=\"24\">";
		echo $productname;
		echo "</td></tr>
<tr><td class=\"fieldlabel\">License Key</td><td class=\"fieldarea\"><input type=\"text\" size=\"40\" value=\"";
		echo $licensekey;
		echo "\" readonly=\"true\" /> ";
		echo "<s";
		echo "pan style=\"color:#cccccc;\">(Not Editable)</span></td></tr>
<tr><td class=\"fieldlabel\">Valid Domains</td><td class=\"fieldarea\"><textarea name=\"validdomain\" rows=2 cols=80>";
		echo $validdomain;
		echo "</textarea></td></tr>
<tr><td class=\"fieldlabel\">Valid IPs</td><td class=\"fieldarea\"><textarea name=\"validip\" rows=2 cols=80>";
		echo $validip;
		echo "</textarea></td></tr>
<tr><td class=\"fieldlabel\">Valid Directory</td><td class=\"fieldarea\"><textarea name=\"validdirectory\" rows=2 cols=80>";
		echo $validdirectory;
		echo "</textarea></td></tr>
<tr><td class=\"fieldlabel\">Number of Reissues</td><td class=\"fieldarea\"><input type=\"text\" name=\"reissues\" size=\"10\" value=\"";
		echo $reissues;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">Status</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"status\">
<option";
		if ($status == "Reissued") {
			echo " selected";
		}
		echo ">Reissued</option>
<option";
		if ($status == "Active") {
			echo " selected";
		}
		echo ">Active</option>
<option";
		if ($status == "Suspended") {
			echo " selected";
		}
		echo ">Suspended</option>
<option";
		if ($status == "Expired") {
			echo " selected";
		}
		echo ">Expired</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">Last Access</td><td class=\"fieldarea\" height=\"24\">";
		echo $lastaccess;
		echo "</td></tr>
</table>
<p align=\"center\"><input type=\"button\" value=\"&laquo; Back to List\" onclick=\"history.go(-1)\" /> <input type=\"submit\" value=\"Save Changes\" class=\"button\" /> <input type=\"button\" value=\"Product Details &raquo;\" onclick=\"window.location='clientshosting.php?id=";
		echo $serviceid;
		echo "'\" /></p>
</form>
<h2>Recent Access</h2>
";
		$aInt->sortableTableInit("nopagination");
		$result = select_query("mod_licensinglog", "", array("licenseid" => $id), "id", "DESC", "0,10");
		while ($data = mysql_fetch_array($result)) {
			$domain = $data['domain'];
			$ip = $data['ip'];
			$path = $data['path'];
			$message = $data['message'];
			$datetime = $data['datetime'];
			$datetime = fromMySQLDate($datetime, true);
			$tabledata[] = array($datetime, $domain, $ip, $path, $message);
		}
		echo $aInt->sortableTable(array("Date", "Domain", "IP", "Path", "Result"), $tabledata);
		return null;
	}
	if ($action == "bans") {
		if ($_REQUEST['save']) {
			check_token();
			if (trim($_REQUEST['banvalue'])) {
				insert_query("mod_licensingbans", array("value" => trim($_REQUEST['banvalue']), "notes" => trim($_REQUEST['bannote'])));
			}
			redir("module=licensing&action=bans");
		}
		if ($_REQUEST['delete']) {
			check_token();
			delete_query("mod_licensingbans", array("id" => $_REQUEST['delete']));
			redir("module=licensing&action=bans");
		}
		$jscode = "function doDelete(id) {
    if (confirm(\"Are you sure you want to delete this ban entry?\")) {
        window.location='" . $modulelink . "&action=bans&delete='+id+'" . generate_token("link") . "';
    }
}
";
		echo "
<h2>Ban Control</h2>
<form method=\"post\" action=\"";
		echo $modulelink;
		echo "&action=bans\">
<input type=\"hidden\" name=\"save\" value=\"true\" />
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\" width=\"20%\">Value</td><td class=\"fieldarea\"><input type=\"text\" name=\"banvalue\" size=\"40\" /></td></tr>
<tr><td class=\"fieldlabel\">Reason/Notes</td><td class=\"fieldarea\"><input type=\"text\" name=\"bannote\" size=\"80\" /></td></tr>
</table>
<p ali";
		echo "gn=\"center\"><input type=\"submit\" value=\"Add Ban\" /></p>
</form>
";
		$aInt->sortableTableInit("nopagination");
		$result = select_query("mod_licensingbans", "", "", "value", "ASC");
		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$value = $data['value'];
			$notes = $data['notes'];
			$tabledata[] = array($value, $notes, "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
		}
		echo $aInt->sortableTable(array("Domain/IP", "Ban Reason/Notes", ""), $tabledata);
		return null;
	}
	if ($action == "log") {
		echo "
<h2>License Access Logs</h2>
<form method=\"post\" action=\"";
		echo $modulelink;
		echo "&action=log\">
<p align=\"center\"><b>Search/Filter</b>
Domain: <input type=\"text\" name=\"search_domainlog\" size=\"30\" value=\"";
		echo $_REQUEST['search_domainlog'];
		echo "\" />
IP: <input type=\"text\" name=\"search_iplog\" size=\"15\" value=\"";
		echo $_REQUEST['search_iplog'];
		echo "\" />
Dir: <input type=\"text\" name=\"search_dirlog\" size=\"25\" value=\"";
		echo $_REQUEST['search_dirlog'];
		echo "\" />
Status: <input type=\"text\" name=\"search_message\" size=\"25\" value=\"";
		echo $_REQUEST['search_message'];
		echo "\" />
<input type=\"submit\" value=\"Go\" class=\"button\" /></p>
</form>
";
		$where = array();
		if ($_REQUEST['search_domainlog']) {
			$where['domain'] = array("sqltype" => "LIKE", "value" => trim($_REQUEST['search_domainlog']));
		}
		if ($_REQUEST['search_iplog']) {
			$where['ip'] = array("sqltype" => "LIKE", "value" => trim($_REQUEST['search_iplog']));
		}
		if ($_REQUEST['search_dirlog']) {
			$where['path'] = array("sqltype" => "LIKE", "value" => trim($_REQUEST['search_dirlog']));
		}
		if ($_REQUEST['search_message']) {
			$where['message'] = array("sqltype" => "LIKE", "value" => trim($_REQUEST['search_message']));
		}
		$result = select_query("mod_licensinglog", "", $where, "id", "DESC");
		$numrows = mysql_num_rows($result);
		$result = select_query("mod_licensinglog", "", $where, "id", "DESC", $page * $limit . ("," . $limit));
		$aInt->sortableTableInit("datetime", "ASC");
		while ($data = mysql_fetch_array($result)) {
			$domain = $data['domain'];
			$ip = $data['ip'];
			$path = $data['path'];
			$message = $data['message'];
			$datetime = $data['datetime'];
			$datetime = fromMySQLDate($datetime, true);
			$tabledata2[] = array($datetime, $domain, $ip, $path, $message);
		}
		echo $aInt->sortableTable(array("Date", "Domain", "IP", "Path", "Status Message"), $tabledata2);
	}
 } 
function licensing_clientarea($vars) {
	if (!$vars['clientverifytool']) {
		return false;
	}
	$domain = trim($_POST['domain']);
	$check = false;
	$results = array();
	if ($domain) {
		$check = true;
		$result = select_query("mod_licensing", "mod_licensing.*,tblproducts.name", "validdomain LIKE '%" . db_escape_string($domain) . "%' OR validip LIKE '%" . db_escape_string($domain) . "%'", "", "", "", "tblhosting ON tblhosting.id=mod_licensing.serviceid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid");
		while ($data = mysql_fetch_array($result)) {
			$licenseid = $data['id'];
			$productname = $data['name'];
			$status = $data['status'];
			$validdomains = explode(",", $data['validdomain']);
			$validips = explode(",", $data['validip']);
			if (in_array($domain, $validdomains) || in_array($domain, $validips)) {
				$results[] = array("productname" => $productname, "domain" => $validdomains[0], "ip" => $validips[0], "status" => $status);
			}
		}
	}
	return array("pagetitle" => "License Verification Tool", "breadcrumb" => array("index.php?m=licensing" => "License Verification Tool"), "templatefile" => "licenseverify", "vars" => array("domain" => $domain, "check" => $check, "results" => $results));
}
/* if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}
if (defined("LICENSINGADDONLICENSE")) {
	exit("License Hacking Attempt Detected");
}
global $whmcs;
global $licensing;
if ($whmcs->get_req_var("larefresh")) {
	$licensing->forceRemoteCheck();
} */ 
define(/* "LICENSINGADDONLICENSE", */ $licensing->isActiveAddon("Licensing Addon"));


?>