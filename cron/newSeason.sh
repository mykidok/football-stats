#!/bin/bash
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:update:championships
sleep 5
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:import:teams
sleep 5
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:import:historics

