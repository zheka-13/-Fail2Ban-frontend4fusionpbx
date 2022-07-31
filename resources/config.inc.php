<?php

return [
    "socket" => '/var/run/fail2ban/fail2ban.sock',
    "bin" => "/usr/local/bin/fail2ban-client",
    "conf" => "/etc/fail2ban/jail.local",
    "emails" => [
        //for example
        //'manager1@example.com',
        //'manager2@example.com',
        //....
    ],
    "use_dns" => true,// failt2ban pages' load time will be a bit slower
];
?>