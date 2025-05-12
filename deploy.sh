echo "ejecutando checkout..."
git checkout .
echo "ejecutando pull..."
git pull
echo "ejecutando borrando carpetas tmp..."
rm -Rf var/tmp/*
rm -Rf var/view_preprocessed/*
rm -Rf var/page_cache/*
rm -Rf var/cache/*
rm -Rf var/report/*
rm -Rf pub/static/*
rm -Rf magento/generated/*
echo "ejecutando upgrade..."
bin/magento setup:upgrade --verbose
echo "ejecutando compile..."
bin/magento setup:di:compile --verbose
echo "ejecutando deploy frontend: es_AR..."
bin/magento setup:static-content:deploy es_AR --area frontend -f
echo "ejecutando deploy adminhtml: es_AR..."
bin/magento setup:static-content:deploy es_AR --area adminhtml -f
echo "ejecutando deploy frontend: en_US..."
bin/magento setup:static-content:deploy en_US --area frontend -f
echo "ejecutando deploy adminhtml: en_US..."
bin/magento setup:static-content:deploy en_US --area adminhtml -f
echo "ejecutando cache:flush..."
bin/magento cache:flush
echo "ejecutando reindex..."
bin/magento index:reindex
