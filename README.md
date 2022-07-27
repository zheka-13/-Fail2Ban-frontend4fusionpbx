# -Fail2Ban-frontend4fusionpbx
Fail2ban firewall management application for FusionPBX

add the following line to fail2ban.service
The systemd unit file is usually located in /etc/systemd/system
or /lib/systemd/system

Under the [Service] section add line

ExecStartPost=/bin/sh -c "while ! [ -S /var/run/fail2ban/fail2ban.sock ]; do sleep 1; done; chown www-data:www-data /var/run/fail2ban/fail2ban.sock;"

then run systemctl daemon-reload
