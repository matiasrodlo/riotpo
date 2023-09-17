<?php

function zopim_instant_messaging() { ?>

<div class="wrap">
	 <div id="icon-users" class="icon32"></div>
<h2>Relay your messages</h2>
Use your favourite Instant Messaging (IM) client to chat with your website visitors!<p>


<?php

$salt = array('salt' => get_option('zopimSalt'));

if (isset($_GET["remove"]) && $_GET["remove"] == 1) {
	echo "Removed IM Set Up. <br><br>";

	json_to_array(zopim_post_request(ZOPIM_IMREMOVE_URL, $salt));
}
$iminfo = json_to_array(zopim_post_request(ZOPIM_IMINFO_URL, $salt));

if (isset($iminfo->bots)) { // Can set up IM ?>

 <style>
	 td {}
	 .clients td.first {border:none;background:#888;color:#fff;}
	 .steps {width:100%}
	 .steps td {background:#f9f9f9;padding:15px;}
	 .clients td {padding:8px;border-top:1px solid #dfdfdf;background:#fff;}
	 .clients {border:1px solid #dfdfdf;background:#fff}
	 .explain {
background:#FAFAFA;
color:#667788;
font-size:8pt;
line-height:13px;
margin:4px 0 0 0;
padding:8px 3px;
display: inline-block;
}
 </style>

<div class="metabox-holder">
	 <div class="postbox">
		  <h3 class="hndle"><span>Setting up your Chat Bots</span></h3>
		  <div style="padding:10px 0px;line-height:17px;">

	 <table class="steps" cellspacing="0" cellpadding="0">
		  <tr valign="top">
				<td style="border-right:5px solid #fff;width:394px;">
					 1. Add the Control Bot to the IM Client of your choice.<br/><br/>

	 <table class="clients" cellpadding="0" cellspacing="0">
	 <tr><td align="center" width="160" class="first"><b>IM Client</b></td><td class="first" width="200"><b>Chat Bot's Name</b></td></tr>
	 <tr><td valign="center" align="center">Gtalk</td><td><?php echo $iminfo->bots->gtalk; ?></td></tr>
	 <tr><td valign="center" align="center">Yahoo</td><td><?php echo $iminfo->bots->yahoo; ?></td></tr>
	 <tr><td valign="center" align="center">AIM</td><td><?php echo $iminfo->bots->aim; ?></td></tr>
	 <tr><td valign="center" align="center">Microsoft</td><td><?php echo $iminfo->bots->msn; ?></td></tr>
	 </table>
	 <div class="explain">For example, to use <b>Gtalk</b> to chat,<br/>add <b><?php echo $iminfo->bots->gtalk; ?></b> to your Gtalk contact list.</div>
		  </td>
		  <td>
		  2. Send the Control Bot this message:<br/><br/><input style="font-size:31px;color:#555;margin:0 0 5px;width:380px;" type="text" value="#setup <?php echo $iminfo->auth_key; ?>" id="box-content" readonly></input><br/>

	<br/><br/>
	3. Accept the invitations to add the Chat Bots.<br>
	<div class="explain">Depending on the number of Chat Bots available in your Package,<br/>you may need to accept up to 8 invitations</div>
	 <br/><br/>That's all!<br/><br/>
	 The Chat Bot will now relay all messages sent from<br/>your website to your IM Client.
	 <br/><br/>
	 Chat away!
	 </td>
	 </tr>
	 </table>

		  </div>
	 </div>
</div>
<?php } else if (isset($iminfo->status)) { // integration already set up ?>

	<h3><img src="<?php echo ZOPIM_IM_LOGOS.$iminfo->protocol; ?>.png"> Your <?php echo strtoupper($iminfo->protocol); ?> account is now linked with Zopim.</h3>

You are connected using the account: <?php echo $iminfo->username; ?>. <br>
Your status is now <b><?php echo $iminfo->status; ?></b>.<br><br>

<!--
<H3>Disable IM Integration</h3>
You can <a href="admin.php?page=zopim_instant_messaging&remove=1">disable IM integration by clicking here</a>.
-->

<?php } else { // could not contact zopim to get the IM status

	if (get_option('zopimCode') != "zopim") {

?>

<div class="metabox-holder">
	 <div class="postbox">
		  <h3 class="hndle"><span>Account not linked</span></h3>
		  <div style="padding:10px;line-height:17px;">
		Please <a href="admin.php?page=zopim_account_config">link your account / check your password</a> before setting up Chat Bots.

		  </div>
	 </div>
</div>

<?php } else { ?>

<div class="metabox-holder">
	 <div class="postbox">
		  <h3 class="hndle"><span>Account not activated</span></h3>
		  <div style="padding:10px;line-height:17px;">
		Please <a href="admin.php?page=zopim_account_config">activate your account</a> before setting up Chat Bots.

		  </div>
	 </div>
</div>

<?php } }

}
?>
