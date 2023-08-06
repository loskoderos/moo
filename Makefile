.PHONY: test

test:
	XDEBUG_MODE=coverage phpunit --display-warnings --display-notices --display-errors --coverage-html test/results
