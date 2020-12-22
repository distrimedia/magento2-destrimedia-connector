# set default target which is executed when no explicit target is provided on the cli
.DEFAULT_GOAL := default

.PHONY: default
default:
	# do nothing

.PHONY: check
check: checkstyle checkquality test

.PHONY: checkstyle
checkstyle:
	vendor/bin/php-cs-fixer fix --dry-run --diff --stop-on-violation --allow-risky=yes
	#vendor/bin/phpcs --standard=Magento2 --ignore=./vendor/ .
	~/bin/composer.phar normalize --dry-run

.PHONY: checkquality
checkquality:
	xmllint --noout --schema vendor/magento/module-backend/etc/menu.xsd            etc/adminhtml/menu.xml
	xmllint --noout --schema vendor/magento/framework/App/etc/routes.xsd           etc/adminhtml/routes.xml
	xmllint --noout --schema vendor/magento/framework/Acl/etc/acl.xsd              etc/acl.xml
	xmllint --noout --schema vendor/magento/module-cron/etc/cron_groups.xsd        etc/cron_groups.xml
	xmllint --noout --schema vendor/magento/module-cron/etc/crontab.xsd            etc/crontab.xml
	xmllint --noout                                                                etc/di.xml # schema validation doesn't work here since the xsd includes another xsd ..
	xmllint --noout --schema vendor/magento/framework/Module/etc/module.xsd        etc/module.xml

	xmllint --noout                                                                view/adminhtml/layout/default.xml # schema validation doesn't work here since the xsd includes another xsd ..
	xmllint --noout                                                                view/adminhtml/ui_component/sales_order_grid.xml # schema validation doesn't work here since the xsd includes another xsd ..

	vendor/bin/phpstan analyse
