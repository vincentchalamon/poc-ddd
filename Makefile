.DEFAULT_GOAL := help
##help: List available tasks on this project
help:
	@echo ""
	@echo "These are the available commands"
	@echo ""
	@grep -E '\#\#[a-zA-Z\.\-]+:.*$$' $(MAKEFILE_LIST) \
        | tr -d '##' \
        | awk 'BEGIN {FS = ": "}; {printf "  \033[36m%-30s\033[0m %s\n", $$1, $$2}' \

##lint: Run all linters
lint: markdown.lint rector.lint cs.lint dockerfile.lint
.PHONY: lint

##lint.fix: Fix all linters
lint.fix: markdown.fix rector.fix cs.fix
.PHONY: lint.fix

##tests: Run all tests
tests: lint openapi.lint phpunit security.check phpstan
.PHONY: tests

##security.check: check for known vulnerabilities in project dependencies
security.check:
	@symfony check:security
.PHONY: security.check

##cs.fix: run php-cs-fixer FIX tool
cs.fix:
	@vendor/bin/php-cs-fixer fix
.PHONY: cs.fix

##cs.fix.path: run php-cs-fixer FIX tool [OPT append file or directory path]
cs.fix.path:
	@vendor/bin/php-cs-fixer fix $(filter-out $@,$(MAKECMDGOALS))
.PHONY: cs.fix.path

##cs.lint: run php-cs-fixer DETECTION tool
cs.lint:
	@vendor/bin/php-cs-fixer fix --dry-run --diff
.PHONY: cs.lint

##cs.lint.path: run php-cs-fixer DETECTION tool [OPT append file or directory path]
cs.lint.path:
	@vendor/bin/php-cs-fixer fix --dry-run --diff $(filter-out $@,$(MAKECMDGOALS))
.PHONY: cs.lint.path

##phpstan: run phpstan
phpstan:
	@vendor/bin/phpstan clear-result-cache
	@vendor/bin/phpstan analyse
.PHONY: phpstan

##phpstan.path: run phpstan [OPT append file or directory path]
phpstan.path:
	@vendor/bin/phpstan clear-result-cache
	@vendor/bin/phpstan analyse $(filter-out $@,$(MAKECMDGOALS))
.PHONY: phpstan.path

##rector.fix: run rector
rector.fix:
	@vendor/bin/rector process
.PHONY: rector.fix

##rector.fix.path: run rector [OPT append file or directory path]
rector.fix.path:
	@vendor/bin/rector process $(filter-out $@,$(MAKECMDGOALS))
.PHONY: rector.fix.path

##rector.lint: run rector with dry-run
rector.lint:
	@vendor/bin/rector process --dry-run
.PHONY: rector.lint

##rector.lint.path: run rector with dry-run [OPT append file or directory path]
rector.lint.path:
	@vendor/bin/rector process --dry-run $(filter-out $@,$(MAKECMDGOALS))
.PHONY: rector.lint.path

##phpunit: Run phpunit tests
phpunit:
	@vendor/bin/phpunit --display-deprecations
.PHONY: phpunit

##phpunit.path: Run phpunit tests [OPT append file or directory path]
phpunit.path:
	@vendor/bin/phpunit --display-deprecations $(filter-out $@,$(MAKECMDGOALS))
.PHONY: phpunit.path

##phpunit.unit: Run the phpunit Unit tests
phpunit.unit:
	@vendor/bin/phpunit --testsuite=Unit
.PHONY: phpunit.unit

##phpunit.utils: Run the phpunit Utils tests
phpunit.utils:
	@vendor/bin/phpunit --testsuite=Utils
.PHONY: phpunit.utils

##phpunit.functional: Run the phpunit Functional tests
phpunit.functional:
	@vendor/bin/phpunit --testsuite=Functional
.PHONY: phpunit.functional

##markdown.fix: Run markdown-lint with --fix
markdown.fix:
	@markdownlint --fix "docs/**/*.md" "*.md"
.PHONY: markdown.fix

##markdown.fix.path: Run markdown-lint with --fix [OPT append file or directory path]
markdown.fix.path:
	@markdownlint --fix $(filter-out $@,$(MAKECMDGOALS))
.PHONY: markdown.fix.path

##markdown.lint: Run markdown-lint
markdown.lint:
	@markdownlint "docs/**/*.md" "*.md"
.PHONY: markdown.lint

##markdown.lint.path: Run markdown-lint [OPT append file or directory path]
markdown.lint.path:
	@markdownlint $(filter-out $@,$(MAKECMDGOALS))
.PHONY: markdown.lint.path

##openapi.lint: run swagger-cli commands
openapi.lint:
	@bin/console api:openapi:export --yaml 1> openapi.yaml && $(DOCKER_RUN) --pull=always --rm -t -v $$(pwd):/spec redocly/cli lint openapi.yaml
.PHONY: openapi.lint

##dockerfile.lint: run hadolint commands
dockerfile.lint:
	@find . -name "Dockerfile" -type f | while read -r file; do \
		echo "Linting $$file..."; \
		docker run --rm -i hadolint/hadolint < "$$file" || exit 1; \
	done
.PHONY: dockerfile.lint

##doc: run documentation locally (PHPDoc with UML, PHPMetrics, and MkDocs)
doc:
	@$(DOCKER_COMPOSE) --profile docs up --wait
.PHONY: doc
