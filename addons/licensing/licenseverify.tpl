{include file="$template/pageheader.tpl" title="License Verification Tool" desc="Check if a website is authorized to be using our software"}

<div class="alert-message block-message info">

    Our License Verification Tool allows you to check if a website is authorized to be running our software.<br />
    If you find a website that is not showing as being licensed, please report it to us for us to check.

</div>

<br />

<form method="post" action="index.php?m=licensing">
    <div align="center">
        Enter Domain/IP: <input type="text" name="domain" size="30" value="{$domain}" style="font-size:18px;" /> <input type="submit" value="Check" class="btn danger" />
    </div>
</form>

<br />

{if !$check}

<h3>How to use this tool:</h3>

<ul>
<li>Enter <strong>only</strong> the domain name where you see our software in use</li>
<li>For example if there was an installation @ http://support.domain.com/, enter just support.domain.com above</li>
<li>You should not include the www. prefix in the case of website urls</li>
</ul>

{else}

<h3>Search Results</h3>

{if $results}

<div class="alert-message block-message success">

    <strong>License Match Found</strong><br />We can confirm that this domain/IP is authorized to be running our software.

</div>

{else}

<div class="alert-message block-message error">

    <strong>No License Matches Found</strong><br />We were unable to find any licenses assigned to the Domain/IP you entered. This doesn't necessarily mean it isn't licensed, but please report the domain to us so we can check it.

</div>

{/if}

{/if}

<br />

<div align="center">
<h2>We thank you for helping us in the fight against piracy</h2>
</div>

<br />
<br />
<br />