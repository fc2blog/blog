echo " ===== Start convert mo file ===== "
SELF_DIR=`dirname $0`
for DIR_LIST in `ls -l ${SELF_DIR}/../locale/ | awk '$1 ~/d/ {print $9}'`
do
  if [ -e ${SELF_DIR}/../locale/${DIR_LIST}/LC_MESSAGES/messages.po ]
  then
    echo "...Convert ${DIR_LIST}/LC_MESSAGES/messages.po > ${DIR_LIST}/LC_MESSAGES/messages.mo"
    msgfmt -o ${SELF_DIR}/../locale/${DIR_LIST}/LC_MESSAGES/messages.mo ${SELF_DIR}/../locale/${DIR_LIST}/LC_MESSAGES/messages.po
  fi
done
echo " ===== End convert mo file ===== "
