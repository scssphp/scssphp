install-test:
	mkdir -p vendor/sass
	cd vendor/sass && git clone https://github.com/sass/sass-spec.git && cd ../..

test:
	vendor/bin/phpunit --colors tests

compat:
	TEST_SCSS_COMPAT=1 vendor/bin/phpunit --colors tests | tee /tmp/incompatible.log | tail -2

sass-spec:
	TEST_SASS_SPEC=1 vendor/bin/phpunit --colors tests 2>&1 | tee /tmp/sass-spec.log | tail -2

standard:
	vendor/bin/phpcs -s --standard=PSR12 --exclude=PSR12.Properties.ConstantVisibility --extensions=php bin src tests *.php
