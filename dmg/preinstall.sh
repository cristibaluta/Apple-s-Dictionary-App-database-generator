#!/bin/sh
containsUser=`echo $2 | sed 's/^\/Users\/.*$//g'`
if ["${containsUser}" == ""]; then
    parentID=`ls -nld ~/Library | sed 's/^[^0-9]* [0-9]* \([0-9]*\) .*/\1/g'`
    mkdir ~/Library/Dictionaries
    chown ${parentID} ~/Library/Dictionaries
fi
exit 0