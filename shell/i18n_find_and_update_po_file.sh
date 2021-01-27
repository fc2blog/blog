#!/bin/bash
# Auto correct translate targets and generate po file, BUT DO NOT TRUST.
echo " ===== Start create po file ===== "
SELF_DIR=`dirname $0`
# find from php and twig code. find in twig is not perfect.
xgettext --keyword=_ --keyword=__ --language php --from-code=UTF-8 --output messages.pot `find ${SELF_DIR}/../app/twig_templates ${SELF_DIR}/../app/src  ${SELF_DIR}/../app/config -name \*.php -o -name \*.twig -type f `
cat messages.pot | sed "s/plain; charset=CHARSET/plain; charset=UTF-8/" > messages.po

for DIR_LIST in `ls -l ${SELF_DIR}/../app/locale/ | awk '$1 ~/d/ {print $9}'`
do
  if [ ! -e ${SELF_DIR}/../app/locale/${DIR_LIST}/LC_MESSAGES/messages.po ]
  then
    echo "...Create ${DIR_LIST}/LC_MESSAGES/messages.po"
    cp messages.po ${SELF_DIR}/../app/locale/${DIR_LIST}/LC_MESSAGES/messages.po
  else
    echo "...Merge ${DIR_LIST}/LC_MESSAGES/messages.po"
    msgmerge --previous --compendium ${SELF_DIR}/../app/locale/${DIR_LIST}/LC_MESSAGES/messages.po /dev/null messages.po -o ${SELF_DIR}/../app/locale/${DIR_LIST}/LC_MESSAGES/messages.po
  fi
done
rm -f messages.pot
rm -f messages.po
echo " ===== End create po file ===== "
