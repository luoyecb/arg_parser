# 
.PHONY: default env test

default: test

env:
	@echo === php version ===
	@php -v
	@echo

test: env
	phpunit --bootstrap vendor/autoload.php tests
