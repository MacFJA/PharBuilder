.PHONY: phpqa coverage mutation-testing
.DEFAULT_GOAL := help

##
## 8888888b.   888                          888888b.              d8b  888       888
## 888   Y88b  888                          888  "88b             Y8P  888       888
## 888    888  888                          888  .88P                  888       888
## 888   d88P  88888b.    8888b.   888d888  8888888K.   888  888  888  888   .d88888   .d88b.   888d888
## 8888888P"   888 "88b      "88b  888P"    888  "Y88b  888  888  888  888  d88" 888  d8P  Y8b  888P"
## 888         888  888  .d888888  888      888    888  888  888  888  888  888  888  88888888  888
## 888         888  888  888  888  888      888   d88P  Y88b 888  888  888  Y88b 888  Y8b.      888
## 888         888  888  "Y888888  888      8888888P"    "Y88888  888  888   "Y88888   "Y8888   888
## ____________________________________________________________________________________________________
##     _____
##     /    )                                  /
##    /    /   ___          _/_   ___   ___   /   __
##   /    /   /___) | /     /    /   ) /   ) /   (_ `
## _/____/   (___   |/     (_   (___/ (___/ /   (__)
##
# Generate on http://patorjk.com/software/taag/


##
## Application quality tools
##===========================

COMPOSER=composer -v exec
NPM=npm run

qa:                ## Run All QA tools
qa: phpqa coverage mutation-testing
	xdg-open build/phpqa.html
	xdg-open build/coverage/index.html

##   ------------------------------

phpqa:             ## Run PHPQA
phpqa: vendor
	$(COMPOSER) phpqa -- \
	  --analyzedDirs=lib \
	  --buildDir=build \
	  --output=file \
	  --report \
	  --ansi \
	  --tools=phpmetrics,pdepend,phploc,parallel-lint:0,security-checker:0,phpunit:0,phpcs:0,phpmd:0,phpcpd:0,phpstan:0,psalm:0

coverage:          ## Show test coverage
coverage: vendor
	@$(COMPOSER) phpunit -- --coverage-text=build/coverage.txt --coverage-html=build/coverage --color=always >/dev/null
	@cat build/coverage.txt
	@rm build/coverage.txt

mutation-testing:  ## Run mutation testing
mutation-testing: vendor
	$(COMPOSER) infection -- --ansi --only-covered | tee build/mutation.txt

#
# Files generation targets
#---------------------------

vendor: composer.lock composer.json

composer.lock: composer.json
	composer install

composer.json:
	composer update


##
# Utilities
#----------------------------

help:
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'
