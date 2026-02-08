import re

# https://www.mkdocs.org/dev-guide/plugins/#on_page_markdown
def on_page_markdown(markdown, page, config, **kwargs):
    # Replace all relative links to source code by GitLab links
    markdown = re.sub(r"(?:\.\./)+(src\/|fixtures\/|tests\/|\.env(?:\..+)?|\.coderabbit\.yaml)", r"{}\1/".format(config.edit_uri), markdown)

    # Convert fenced block to indented block for Admonitions compatibility
    # https://squidfunk.github.io/mkdocs-material/reference/admonitions/
    markdown = re.sub(r"^> (Note|Abstract|Info|Tip|Success|Question|Warning|Failure|Danger|Bug|Example|Quote): (.+)", r"!!! \1\n\n    \2", markdown, flags=re.IGNORECASE | re.MULTILINE)

    return markdown
