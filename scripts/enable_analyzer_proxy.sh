#!/usr/bin/env zsh
sudo a2enmod proxy proxy_balancer proxy_html proxy_http proxy_ajp proxy_connect
sudo systemctl reload apache2