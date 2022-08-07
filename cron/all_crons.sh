#!/bin/bash
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:check:bet
sleep 6
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:update:teams
sleep 6
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:import:games
sleep 6
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:import:odds
sleep 6
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:check:form
sleep 6
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:check:combination
sleep 6
/usr/local/php7.3/bin/php /home/myfootbamx/bin/console api:create:combination
