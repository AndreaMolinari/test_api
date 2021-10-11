#!/usr/bin/env zsh
# install env profile
# echo "RECORD_API_HOME=\"$PWD\"" > /etc/environment.d/recordapi.conf
# Setting env var
# su www-data -s "$(which zsh)" -c "export RECORD_API_HOME=\"$PWD\""
# export RECORD_API_HOME="$PWD"
# reset

# copy all service files
cp ./scripts/services/* /etc/systemd/system/
systemctl daemon-reload

#enable all services
systemctl enable apiqueues.service
