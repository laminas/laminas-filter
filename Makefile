# Run `make` (no arguments) to get a short description of what is available
# within this `Makefile`.

MKDOCS_IMAGE_ID := $(shell docker images -q laminas/mkdocs | xargs)

help: ## shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
.PHONY: help

documentation-theme: ## fetch the documentation theme repo
	git clone git@github.com:laminas/documentation-theme.git

build-mkdocs-image: documentation-theme ## Build the mkdocs image with necessary dependencies for building the docs
	$(if ${MKDOCS_IMAGE_ID}, $(info Image already built), cd documentation-theme/builder && docker build -t laminas/mkdocs .)
.PHONY: build-mkdocs-image

docs: build-mkdocs-image ## build the docs using a Docker container
	docker run -it -w /app -v ${PWD}:/app --rm laminas/mkdocs ./documentation-theme/build.sh -u https://www.example.com
	$(info ${PWD}/docs/html/index.html)
.PHONY: docs

install: install-tools ## Install PHP dependencies
	composer install
.PHONY: install

install-tools: ## Install standalone dev tools
	cd tools/crc && composer install
.PHONY: install-tools

update: ## Update PHP dependencies
	composer update
.PHONY: update

bump: bump-tools ## Bump dev dependencies and update
	composer update && composer bump -D && composer update
.PHONY: bump

bump-tools: ## Bump and update standalone dev tools
	cd tools/crc && composer update && composer bump -D && composer update
.PHONY: bump-tools

clean: ## Clear out caches and documentation assets
	rm -rf documentation-theme
	rm -rf docs/html
	docker image rm laminas/mkdocs
	rm -rf .phpunit.cache
	rm -rf .psalm.cache
	rm -f .phpcs-cache
	vendor/bin/psalm --clear-cache

composer-require-checker: ## Check for symbols from un-declared dependencies
	tools/crc/vendor/bin/composer-require-checker check --config-file=tools/crc/config.json
.PHONY: composer-require-checker

static-analysis: ## Run static analysis checks
	vendor/bin/psalm --no-cache
.PHONY: sa

coding-standards: ## Run coding standards checks
	vendor/bin/phpcs
.PHONY: cs

test: ## Run unit tests
	vendor/bin/phpunit
.PHONY: test

check: coding-standards static-analysis test composer-require-checker ## Run all QA Checks
.PHONY: check
