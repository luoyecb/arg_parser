# 
.PHONY: default env test

default: test

env:
	@echo === php version ===
	@php -v
	@echo
	@echo === phpunit version ===
	@phpunit --version

test: env
	phpunit --bootstrap vendor/autoload.php tests
