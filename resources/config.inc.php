<?php

return [
    "socket" => '/var/run/fail2ban/fail2ban.sock',
    "bin" => "/usr/local/bin/fail2ban-client",
    "conf" => "/etc/fail2ban/jail.local",
    "use_dns" => true,
];
?>