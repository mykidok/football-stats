#!/bin/bash
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:check:bet
sleep 60
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:update:teams
sleep 60
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:import:games
sleep 5
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:import:odds
sleep 5
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:check:form
sleep 5
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:check:combination
sleep 5
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:create:combination
