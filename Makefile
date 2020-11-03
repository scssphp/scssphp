test: vendor
	vendor/bin/phpunit --colors tests

sass-spec: vendor
	TEST_SASS_SPEC=1 vendor/bin/phpunit --colors tests 2>&1 | tee /tmp/sass-spec.log | tail -2

standard: vendor
	vendor/bin/phpcs -s --standard=PSR12 --exclude=PSR12.Properties.ConstantVisibility --extensions=php bin src tests *.php

vendor: composer.json
	composer update
	touch $@
