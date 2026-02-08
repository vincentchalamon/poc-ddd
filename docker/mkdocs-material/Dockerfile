# https://squidfunk.github.io/mkdocs-material/getting-started/#with-docker-material-for-mkdocs
FROM squidfunk/mkdocs-material:9

# Required by healthcheck
# hadolint ignore=DL3013,DL3018
RUN apk add --no-cache curl; \
	rm -rf /var/cache/apk/*; \
    pip install --no-cache-dir \
        mkdocs-open-in-new-tab \
    ;
