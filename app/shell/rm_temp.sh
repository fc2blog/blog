echo " ===== Start Temp Directory File ===== "
SELF_DIR=`dirname $0`
echo "rm debug_html"
rm -f ${SELF_DIR}/../temp/debug_html/*.html
echo "rm log"
rm -f ${SELF_DIR}/../temp/log/*_log
echo "rm blog_template"
rm -fr ${SELF_DIR}/../temp/blog_template/*
echo " ===== End Temp Directory File ===== "
