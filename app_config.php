<?php

	//application details
		$apps[$x]['name'] = "Fail2Ban";
		$apps[$x]['uuid'] = "9cda5dab-a584-4ff5-a4e4-6d95d52d92dc";
		$apps[$x]['category'] = "Switch";
		$apps[$x]['subcategory'] = "";
		$apps[$x]['version'] = "1.0";
		$apps[$x]['license'] = "CC-BY-SA 3.0";
		$apps[$x]['url'] = "https://salsa.debian.org/benedikt-guest";
		$apps[$x]['description']['en-us'] = "(Un)ban hosts";
		$apps[$x]['description']['en-gb'] = "(Un)ban hosts";
		$apps[$x]['description']['ar-eg'] = "";
		$apps[$x]['description']['de-at'] = "temporäre Hostsperren einrichten und aufheben";
		$apps[$x]['description']['de-ch'] = "temporäre Hostsperren einrichten und aufheben";
		$apps[$x]['description']['de-de'] = "temporäre Hostsperren einrichten und aufheben";
		$apps[$x]['description']['es-cl'] = "";
		$apps[$x]['description']['es-mx'] = "";
		$apps[$x]['description']['fr-ca'] = "";
		$apps[$x]['description']['fr-fr'] = "";
		$apps[$x]['description']['he-il'] = "";
		$apps[$x]['description']['it-it'] = "";
		$apps[$x]['description']['nl-nl'] = "";
		$apps[$x]['description']['pl-pl'] = "";
		$apps[$x]['description']['pt-br'] = "";
		$apps[$x]['description']['pt-pt'] = "";
		$apps[$x]['description']['ro-ro'] = "";
		$apps[$x]['description']['ru-ru'] = "";
		$apps[$x]['description']['sv-se'] = "";
		$apps[$x]['description']['uk-ua'] = "";


        //permission details
                $y=0;
                $apps[$x]['permissions'][$y]['name'] = "fail2ban_view";
                $apps[$x]['permissions'][$y]['menu']['uuid'] = "9cda5dab-a584-4ff5-a4e4-6d95d52d92dc";
                $apps[$x]['permissions'][$y]['groups'][] = "superadmin";
                $apps[$x]['permissions'][$y]['groups'][] = "admin";
                $y++;
                $apps[$x]['permissions'][$y]['name'] = "fail2ban_ban";
                $apps[$x]['permissions'][$y]['groups'][] = "superadmin";
                $apps[$x]['permissions'][$y]['groups'][] = "admin";
                $y++;
                $apps[$x]['permissions'][$y]['name'] = "fail2ban_unban";
                $apps[$x]['permissions'][$y]['groups'][] = "superadmin";
                $apps[$x]['permissions'][$y]['groups'][] = "admin";
				$y++;
				$apps[$x]['permissions'][$y]['name'] = "fail2ban_whitelist_view";
				$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
				$apps[$x]['permissions'][$y]['groups'][] = "admin";
				$y++;
				$apps[$x]['permissions'][$y]['name'] = "fail2ban_whitelist_add";
				$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
				$apps[$x]['permissions'][$y]['groups'][] = "admin";
				$y++;
				$apps[$x]['permissions'][$y]['name'] = "fail2ban_whitelist_remove";
				$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
				$apps[$x]['permissions'][$y]['groups'][] = "admin";

?>
