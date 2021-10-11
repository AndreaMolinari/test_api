#!/usr/bin/env zsh
foreach srv ($(ls ./scripts/services))
# disable all services
    systemctl disable $srv
# delete all service files
    rm /etc/systemd/system/$srv
end
systemctl daemon-reload

# remove env profile
# rm /etc/environment.d/recordapi.conf
# Unsetting env var
# su www-data -s "$(which zsh)" -c "unset RECORD_API_HOME"
# unset RECORD_API_HOME
# reset
