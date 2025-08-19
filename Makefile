test: vendor
	vendor/bin/phpunit --colors tests

sass-spec: vendor
	TEST_SASS_SPEC=1 vendor/bin/phpunit --colors tests 2>&1 | tee /tmp/sass-spec.log | tail -2

rebuild-sass-spec: vendor
	BUILD=1 vendor/bin/phpunit tests/SassSpecTest.php

rebuild-outputs: vendor
	BUILD=1 vendor/bin/phpunit tests/InputTest.php

standard: vendor
	vendor/bin/phpcs -s --extensions=php bin src tests

fix-cs: vendor
	vendor/bin/phpcbf -s --extensions=php bin src tests

vendor: composer.json
	composer update
	touch $@

phpstan: vendor
	vendor/bin/phpstan analyse

phpstan-verbose: vendor
	vendor/bin/phpstan analyse -v

phpstan-baseline: vendor
	vendor/bin/phpstan analyse --generate-baseline

.PHONY: test sass-spec rebuild-sass-spec rebuild-outputs standard fix-cs phpstan phpstan-verbose phpstan-baseline
