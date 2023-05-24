test: vendor
	vendor/bin/phpunit --colors tests

sass-spec: vendor
	TEST_SASS_SPEC=1 vendor/bin/phpunit --colors tests 2>&1 | tee /tmp/sass-spec.log | tail -2

rebuild-sass-spec: vendor
	BUILD=1 vendor/bin/phpunit tests/SassSpecTest.php

rebuild-outputs: vendor
	BUILD=1 vendor/bin/phpunit tests/InputTest.php

standard: vendor
	vendor/bin/phpcs -s --standard=PSR12 --exclude=PSR12.Properties.ConstantVisibility --extensions=php bin src tests *.php

vendor: composer.json
	composer update
	touch $@

phpstan: vendor-bin/phpstan/vendor
	vendor-bin/phpstan/vendor/bin/phpstan analyse

phpstan-baseline: vendor-bin/phpstan/vendor
	vendor-bin/phpstan/vendor/bin/phpstan analyse --generate-baseline

vendor-bin/phpstan/vendor: vendor vendor-bin/phpstan/composer.json
	composer bin phpstan update
	touch $@

.PHONY: test sass-spec rebuild-sass-spec rebuild-outputs standard phpstan phpstan-baseline
