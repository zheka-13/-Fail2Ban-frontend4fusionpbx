# -Fail2Ban-frontend4fusionpbx

Forked and completely rewritten for using with the latest 
versions of fail2ban.
---
### **App works with fail2ban version 0.11.2 or above**

#### To work properly with fail2ban you need to do a few things:
1. Set the owner of the fail2ban.sock file. **For example: chown www-data:www-data /var/run/fail2ban/fail2ban.sock** 
2. Add option to systemd fail2ban unit file in order to keep proper ownership on .sock file after reboot or sevice restart. Add below line to failt2ban.service file and run **systemctl daemon-reload**.

```bash
Under the [Service] section add line

ExecStartPost=/bin/sh -c "while ! [ -S /var/run/fail2ban/fail2ban.sock ]; do sleep 1; done; chown www-data:www-data /var/run/fail2ban/fail2ban.sock;"

```
3. Copy file /etc/fail2ban/jail.cof to /etc/fail2ban/jail.local. Or just make empty /etc/fail2ban/jail.local file if you don't have one. **Change its ownership to www-data**.  
4. **_Don't forget to configure fail2ban app. Edit file config.inc.php._**

---

## App functionality
- Add ip address to jail.
- Remove ip address from jail
- Add ip address to white list
- Remove ip address from white list
- Send mails to configured emails on above actions
