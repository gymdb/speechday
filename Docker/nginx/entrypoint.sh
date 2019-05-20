#!/bin/sh

if [ ! -d "/var/www1" ]; then
   mkdir /var/www1
fi
cd /var/www1
if [ ! -d "sprechtag" ]; then
   echo "CREATING SPREACHTAG"
   wget https://codeload.github.com/gymdb/speechday/zip/master -O /var/www1/master.zip
   unzip /var/www1/master.zip
   mv /var/www1/speechday-master /var/www1/sprechtag
   rm /var/www1/master.zip
   echo -e "[SQL]\nhost = database\nuser = root\npassword = docker\ndbname = speechday"  >/var/www1/sprechtag/code/dao/settings.ini
fi


exec "$@"
