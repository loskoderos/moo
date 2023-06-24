.PHONY: test

test:
	XDEBUG_MODE=coverage phpunit --coverage-html test/results
