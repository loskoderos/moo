.PHONY: run test

run:
	php -S 0.0.0.0:9090 public/index.php

test:
	XDEBUG_MODE=coverage phpunit --coverage-html test/results
