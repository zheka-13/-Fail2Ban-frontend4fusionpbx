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
	if (!permission_exists('fail2ban_view')) {
		echo "access denied";
		exit;
	}

	$service = new Fail2BanService();
	$language = new text;
	$text = $language->get();

	if (isset($_POST['action']) && $_POST['action'] == "ban"){
		if ($service->ban($_POST['jail'], $_POST['ip'])) {
			$message = "<br><span style='font-size:14px;color:green'>".$text['fail2ban-ip']." ".$_POST['ip']." ".$text['fail2ban-added']." ".$_POST['jail']."</span>";
		}
		else{
			$message = "<br><span style='font-size:14px;color:red'>".$text['fail2ban-add-error']." ".$_POST['ip']."</span>";
		}
	}
	if (isset($_POST['action']) && $_POST['action'] == "unban"){
		if ($service->unban($_POST['jail'], $_POST['ip'])) {
			$message = "<br><span style='font-size:14px;color:green'>".$text['fail2ban-ip']." ".$_POST['ip']." ".$text['fail2ban-removed']." ".$_POST['jail']."</span>";
		}
		else{
			$message = "<br><span style='font-size:14px;color:red'>".$text['fail2ban-remove-error']." ".$_POST['ip']."</span>";
		}
	}

	$document['title'] = $text['title-fail2ban'];
	require_once "resources/header.php";

	try {
		$service->check();
	}
	catch (Exception $e){
		$msg = "";
		if ($e->getMessage() == 'stopped') {
			$msg = "<div align='center'>" . $text['message-service-stopped'] . "<br /></div>";
		}
		if ($e->getMessage() == 'no access') {
			$msg = "<div align='center'>" . $text['message-connection-denied'] . "<br /></div>";
		}
		echo "<div align='center'>\n";
		echo "<table width='40%'>\n";
		echo "<tr>\n";
		echo "<td class='row_style1'><strong>$msg</strong></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td class='row_style1'><strong>Path to socket: ".$service->getSocket()."</strong></td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</div>\n";
		require_once "resources/footer.php";
		return;
	}
	$version = $service->getVersion();
	$jails = $service->getJails();
//show the content
    echo "<div class='action_bar' id='action_bar'>\n";
    echo "  <div class='heading'><b>".$text['title-fail2ban']."</b> Fail2ban ".$version." ".$text['fail2ban-installed']." <i>(".$text['message-version-required'].")</i></div>\n";
	echo "  <div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-whitelist'],'icon'=>$_SESSION['theme']['button_icon_list'],'collapse'=>'hide-xs','style'=>'margin-left: 15px;','link'=>'whitelist.php']);
	echo button::create(['type'=>'button','label'=>$text['button-reload'],'icon'=>$_SESSION['theme']['button_icon_reload'],'collapse'=>'hide-xs','style'=>'margin-left: 15px;','link'=>'fail2ban.php?reload=1']);
    echo "  </div>\n";
    echo "  <div style='clear: both;'></div>\n";
	echo "</div>\n";
	echo "<form id='form_list' action='fail2ban.php'  method='post'>\n";
	echo "<input type='hidden' name='action' value='ban'>";
	echo "  <div class='heading'><b>".$text['fail2ban-manually-ban']."</b></div>\n";
	echo "  <table><tr><td>".$text['fail2ban-jail'].": <td>";
	echo "  <select style='width: 200px' class='formfld' name='jail'>";
	echo "<option value=''>" . $text['fail2ban-select-jail'] . "</option>";

	foreach ($jails as $jail_name => $ips) {
		echo "<option ".(isset($_POST['jail']) && $_POST['jail'] == $jail_name ? "selected" : "")." value='".$jail_name."'>" . $jail_name . "</option>";
	}
	echo "  </select>\n";
	echo "<td><input type='text' name='ip' placeholder='".$text['fail2ban-placeholder']."' value=''>";
	echo "<td>";
	if (permission_exists('fail2ban_ban')) {
		echo button::create(['type'=>'submit','label'=>$text['button-ban'],'icon'=>$_SESSION['theme']['button_icon_add'],'collapse'=>'hide-xs','style'=>'margin-right: 15px;', 'name'=>'ban']);
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
	echo "  <div class='heading'><b>".$text['fail2ban-title-banned']."</b></div>\n";
	echo "  <div class='actions'>\n";

	echo "  </div>\n";
	echo "  <div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "<th nowrap='nowrap'><a href='#'>".$text['fail2ban-jail-name']."</th>";
	echo "<th nowrap='nowrap'><a href='#'>".$text['fail2ban-ip']."</th>";
	echo "<th nowrap='nowrap'><a href='#'>".$text['fail2ban-unban']."</th>";
	echo "</tr>\n";
	foreach ($jails as $jail_name => $ips) {
		if (empty($ips)){
			continue;
		}
		foreach ($ips as $ip) {
			echo "<tr>";
			echo "<td>" . $jail_name;
			echo "<td>" . $ip['ip'].(!empty($ip['domain']) && $ip['domain'] != $ip['ip'] ? "(".$ip['domain'].")" : "");
			echo "<td>";
			if (permission_exists('fail2ban_unban')) {
				echo "<form id='form_list' action='fail2ban.php'  method='post'>\n";
				echo "<input type='hidden' name='action' value='unban'>";
				echo "<input type='hidden' name='ip' value='".$ip['ip']."'>";
				echo "<input type='hidden' name='jail' value='".$jail_name."'>";
				echo button::create(['type'=>'submit','label'=>$text['button-unban'],'icon'=>$_SESSION['theme']['button_icon_remove'],'collapse'=>'hide-xs','style'=>'margin-right: 15px;', 'name'=>'unban']);
				echo "</form>";
			}
			echo "</tr>";
		}
	}

	echo "</table>";
	echo "<div style='color:gray;text-align: center;'><span style='color:gray' class='footer'>".$text['fail2ban-loaded-time'].": ".$service->getDiffTime()." ".$text['fail2ban-loaded-seconds']."</span></div>";



//include the footer
	require_once "resources/footer.php";

?>
