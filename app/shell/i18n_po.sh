#!/bin/bash
echo " ===== Start create po file ===== "
SELF_DIR=`dirname $0`
xgettext --keyword=__ --language php --from-code=UTF-8 --output messages.pot `find ${SELF_DIR}/../ -name \*.php -o -name \*.html -type f | grep -v /temp/`
cat messages.pot | sed "s/plain; charset=CHARSET/plain; charset=UTF-8/" > messages.po

for DIR_LIST in `ls -l ${SELF_DIR}/../locale/ | awk '$1 ~/d/ {print $9}'`
do
  if [ ! -e ${SELF_DIR}/../locale/${DIR_LIST}/LC_MESSAGES/messages.po ]
  then
    echo "...Create ${DIR_LIST}/LC_MESSAGES/messages.po"
    cp messages.po ${SELF_DIR}/../locale/${DIR_LIST}/LC_MESSAGES/messages.po
  else
    echo "...Merge ${DIR_LIST}/LC_MESSAGES/messages.po"
    msgmerge ${SELF_DIR}/../locale/${DIR_LIST}/LC_MESSAGES/messages.po messages.po -o ${SELF_DIR}/../locale/${DIR_LIST}/LC_MESSAGES/messages.po
  fi
done
rm -f messages.pot
rm -f messages.po
echo " ===== End create po file ===== "
