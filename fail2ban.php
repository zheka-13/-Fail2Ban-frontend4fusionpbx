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
	if (permission_exists('fail2ban_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get session data
	if ($_SESSION['fail2ban-jails']) {
		$jails = $_SESSION['fail2ban-jails'];
			if (permission_exists('fail2ban_unban') && $_POST['action'] == 'delete' && $_POST['target'] != '') {
			$target = explode(':', $_POST['target']);
			if (count($target) == 2) {
				$target_jail = $target[0];
				$target_entry = $target[1];
				if (array_key_exists($target_jail, $jails)) {
					unban_ip($target_jail, $jails[$target_jail][$target_entry]);
				}
			}
			} elseif (permission_exists('fail2ban_ban')&& $_POST['action'] == 'add' && $_POST['ip'] != '') {
				$target_jail = $_POST['jail'];
				$ip = $_POST['ip'];
				if (array_key_exists($target_jail, $jails)) {
					ban_ip($target_jail, $ip);
					sleep (1); # sometimes won't appear in list_banned without this
				}
			}
	}

//includes and title
	$document['title'] = $text['title-fail2ban'];
	require_once "resources/header.php";


//show the content
        echo "<div class='action_bar' id='action_bar'>\n";
        echo "  <div class='heading'><b>".$text['title-fail2ban']."</b></div>\n";
	echo "  <div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-reload'],'icon'=>$_SESSION['theme']['button_icon_reload'],'collapse'=>'hide-xs','style'=>'margin-left: 15px;','link'=>'fail2ban.php']);
        echo "  </div>\n";
        echo "  <div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo "Dieses Modul ist aktuell noch in Entwicklung<br />";

	$socket_check = check_socket();
	if ($socket_check != 'OK') {
                $msg = "<div align='center'>".$text['message-connection']."<br />$socket_check</div>";
                echo "<div align='center'>\n";
                echo "<table width='40%'>\n";
                echo "<tr>\n";
                echo "<th align='left'>".$text['label-message']."</th>\n";
                echo "</tr>\n";
                echo "<tr>\n";
                echo "<td class='row_style1'><strong>$msg</strong></td>\n";
                echo "</tr>\n";
                echo "</table>\n";
                echo "</div>\n";
	} else {

		echo "<form id='form_list' method='post'>\n";
		echo "<input type='hidden' id='action' name='action' value='delete'>\n";
		echo "<table class='list'>\n";
		echo "<tr class='list-header'>\n";
		echo "<th>".$text['label-jail']."</th>\n";
		echo "<th>".$text['label-ip-address']."</th>\n";
		if (permission_exists('fail2ban_unban'))
			echo "<th>".$text['label-action']."</th>\n";
		echo "</tr>\n";

		$jails=list_jails();
		$_SESSION["fail2ban-jails"]=array();
		foreach($jails as $j=>$i){ $banned=list_banned($j); $jails[$j]=$banned; }
		foreach ($jails as $key => $value) {
			$_SESSION["fail2ban-jails"][$key]=array();
			$i=0;
			foreach ($value as $banned) {
				$_SESSION["fail2ban-jails"][$key][$i]=strval(explode(' ', $banned)[0]);
				echo "<tr>\n";
				echo "<td>$key</td>\n";
				echo "<td>$banned</td>\n";
				if (permission_exists('fail2ban_unban')) {
					echo "<td>";
					echo button::create(['type'=>'submit','label'=>$text['button-unban'],'icon'=>$_SESSION['theme']['button_icon_delete'],'collapse'=>'hide-xs','style'=>'margin-right: 15px;', 'name'=>'target', 'value'=>"$key:$i"]);
					echo "</td>\n";
				}
				echo "</tr>\n";
				$i++;
			}
		}
		echo "</form>\n";

		if (permission_exists('fail2ban_ban')) {
			echo "<tr>\n";
			echo "<form id='form_ban' method='post'>\n";
			echo "<input type='hidden' id='action' name='action' value='add'>\n";
			echo "<td>\n";
			#echo "<input type='hidden' id='action' name='action' value='ban'>\n";
			echo "  <select class='formfld' name='jail'>\n";
			foreach ($jails as $key => $value) {
				echo "          <option value='$key'>".$key."</option>\n";
			}
			echo "  </select>\n";
			echo "</td>\n";

			echo "<td>\n";
			echo "  <input class='formfld' name='ip'>\n";
			echo "</td>\n";

			echo "<td>\n";
			echo button::create(['type'=>'submit','label'=>$text['button-ban'],'icon'=>$_SESSION['theme']['button_icon_add'],'collapse'=>'hide-xs','style'=>'margin-right: 15px;', 'name'=>'ban']);
			echo "</td>\n";
			echo "</form>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
	}

//include the footer
	require_once "resources/footer.php";

?>
