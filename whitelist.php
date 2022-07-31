<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2019
	the Initial Developer. All Rights Reserved.

	The code has been adapted to control fail2ban by Benedikt Wildenhain.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
	James Rose <james.o.rose@gmail.com>
	Benedikt Wildenhain <benedikt.wildenhain@hs-bochum.de> (C) 2020
*/

//includes
	require_once "root.php";
        require_once "resources/require.php";
        require_once "resources/check_auth.php";

	require_once "resources/engine.inc.php";

//check permissions
	if (permission_exists('fail2ban_whitelist_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}
	$service = new Fail2BanService();
	$language = new text;
	$text = $language->get();
	if (isset($_POST['action']) && $_POST['action'] == "add"){
		if ($service->addToWhitelist($_POST['ip'])) {
			$message = "<br><span style='font-size:14px;color:green'>".$text['fail2ban-ip']." ".$_POST['ip']." ".$text['fail2ban-whitelisted']."</span>";
		}
		else{
			$message = "<br><span style='font-size:14px;color:red'>".$text['fail2ban-whitelist-error']." ".$_POST['ip']."</span>";
		}
	}
	if (isset($_POST['action']) && $_POST['action'] == "remove"){
		if ($service->removeFromWhitelist($_POST['ip'])) {
			$message = "<br><span style='font-size:14px;color:green'>".$text['fail2ban-ip']." ".$_POST['ip']." ".$text['fail2ban-whitelist-removed']."</span>";
		}
		else{
			$message = "<br><span style='font-size:14px;color:red'>".$text['fail2ban-whitelist-remove-error']." ".$_POST['ip']."</span>";
		}
}

	$document['title'] = $text['title-whitelist'];
	require_once "resources/header.php";

	try {
		$service->check();
		$service->whitelistCheck();
	}
	catch (Exception $e){
		$msg = "";
		if ($e->getMessage() == 'stopped') {
			$msg = "<div align='center'>" . $text['message-service-stopped'] . "<br /></div>";
		}
		if ($e->getMessage() == 'no access') {
			$msg = "<div align='center'>" . $text['message-connection-denied'] . "<br /></div>";
		}
		if ($e->getMessage() == 'no_conf') {
			$msg = "<div align='center'>" . $text['message-no-conf'] . "<br /></div>";
		}
		echo "<div align='center'>\n";
		echo "<table width='40%'>\n";
		echo "<tr>\n";
		echo "<td class='row_style1'><strong>$msg</strong></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td class='row_style1'><strong>Path to socket: ".$service->getSocket()."</strong></td>\n";
		echo "<td class='row_style1'><strong>Path to conf: ".$service->getConf()."</strong></td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</div>\n";
		require_once "resources/footer.php";
		return;
	}
	$version = $service->getVersion();
	//show the content
    echo "<div class='action_bar' id='action_bar'>\n";
    echo "  <div class='heading'><b>".$text['title-whitelist']."</b> Fail2ban ".$version." ".$text['fail2ban-installed']." <i>(".$text['message-version-required'].")</i></div>\n";
	echo "  <div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-blacklist'],'icon'=>$_SESSION['theme']['button_icon_list'],'collapse'=>'hide-xs','style'=>'margin-left: 15px;','link'=>'fail2ban.php']);
	echo button::create(['type'=>'button','label'=>$text['button-reload'],'icon'=>$_SESSION['theme']['button_icon_reload'],'collapse'=>'hide-xs','style'=>'margin-left: 15px;','link'=>'whitelist.php?reload=1']);
    echo "  </div>\n";
    echo "  <div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo "<form id='form_list' action='whitelist.php'  method='post'>\n";
	echo "<input type='hidden' name='action' value='add'>";
	echo "  <div class='heading'><b>".$text['fail2ban-manually-whitelist']."</b></div>\n";
	echo "  <table><tr>";
	echo "<td><input type='text' name='ip' placeholder='".$text['fail2ban-placeholder']."' value=''>";
	echo "<td>";
	if (permission_exists('fail2ban_whitelist_add')) {
		echo button::create(['type'=>'submit','label'=>$text['button-add'],'icon'=>$_SESSION['theme']['button_icon_add'],'collapse'=>'hide-xs','style'=>'margin-right: 15px;', 'name'=>'add']);
	}
	echo "</tr>";
	echo "</table>";
	echo "  <div class='actions'>\n";

	echo "  </div>\n";
	echo "  <div style='clear: both;'></div>\n";
	echo "</form>";
	if (!empty($message)){
		echo $message;
	}
	echo "<hr>";

	echo "<div class='action_bar' id='action_bar'>\n";
	echo "  <div class='heading'><b>".$text['fail2ban-title-whitelisted']."</b></div>\n";
	echo "  <div class='actions'>\n";

	echo "  </div>\n";
	echo "  <div style='clear: both;'></div>\n";
	echo "</div>\n";
	$ips = $service->getWhitelistIps();
	echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "<th nowrap='nowrap'><a href='#'>".$text['fail2ban-ip']."</th>";
	echo "<th nowrap='nowrap'><a href='#'>".$text['fail2ban-remove']."</th>";
	echo "</tr>\n";
	foreach ($ips as $ip) {
		echo "<tr>";
		echo "<td>" . $ip['ip'].(!empty($ip['domain']) && $ip['domain'] != $ip['ip'] ? "(".$ip['domain'].")" : "");
		echo "<td>";
		if (permission_exists('fail2ban_whitelist_remove')) {
			echo "<form id='form_list' action='whitelist.php'  method='post'>\n";
			echo "<input type='hidden' name='action' value='remove'>";
			echo "<input type='hidden' name='ip' value='".$ip['ip']."'>";
			echo button::create(['type'=>'submit','label'=>$text['button-remove'],'icon'=>$_SESSION['theme']['button_icon_remove'],'collapse'=>'hide-xs','style'=>'margin-right: 15px;', 'name'=>'remove']);
			echo "</form>";
		}
		echo "</tr>";
	}

	echo "</table>";
	echo "<div style='color:gray;text-align: center;'><span style='color:gray' class='footer'>".$text['fail2ban-loaded-time'].": ".$service->getDiffTime()." ".$text['fail2ban-loaded-seconds']."</span></div>";



//include the footer
	require_once "resources/footer.php";

?>
