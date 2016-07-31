{php}

$serviceid = $this->_tpl_vars['serviceid'];
$result = full_query("SELECT mod_licensing.licensekey,mod_licensing.validdomain,mod_licensing.validip,mod_licensing.validdirectory,mod_licensing.status,tblproducts.configoption3 FROM tblhosting,tblproducts,mod_licensing WHERE tblhosting.packageid=tblproducts.id AND tblhosting.id=mod_licensing.serviceid AND tblproducts.servertype='licensing' AND tblhosting.id='.(int)$serviceid)';
$data = mysql_fetch_array($result);
$licensekey = $data['licensekey'];
$validdomain = $data['validdomain'];
$validip = $data['validip'];
$validdirectory = $data['validdirectory'];
$status = $data['status'];
$allowreissues = $data['configoption3'];

$this->_tpl_vars['licensekey'] = $licensekey;
$this->_tpl_vars['validdomain'] = $validdomain;
$this->_tpl_vars['validip'] = $validip;
$this->_tpl_vars['validdirectory'] = $validdirectory;
$this->_tpl_vars['status'] = $status;
$this->_tpl_vars['allowreissues'] = $allowreissues;

{/php}

{if $status == "Reissued"}
<div class="alert-message success">
    {$LANG.licensingreissued}
</div>
{/if}

<p><h4>{$LANG.licensingkey}:</h4> {$licensekey}</p>
<p><h4>{$LANG.licensingvaliddomains}:</h4> <textarea rows=2 style="width:60%;" readonly=true>{$validdomain}</textarea></p>
<p><h4>{$LANG.licensingvalidips}:</h4> <textarea rows=2 style="width:60%;" readonly=true>{$validip}</textarea></p>
<p><h4>{$LANG.licensingvaliddirectory}:</h4> <textarea rows=2 style="width:60%;" readonly=true>{$validdirectory}</textarea></p>
<p><h4>{$LANG.licensingstatus}:</h4> {$status}</p>

{if $allowreissues && $status == "Active"}
<form method="post" action="clientarea.php?action=productdetails">
<input type="hidden" name="id" value="{$id}" />
<input type="hidden" name="serveraction" value="custom" />
<input type="hidden" name="a" value="reissue" />
<p align="center"><br /><input type="submit" value="{$LANG.licensingreissue}" class="btn" /></p>
</form>
{/if}