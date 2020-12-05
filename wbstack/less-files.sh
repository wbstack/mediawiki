# Delete a bunch of files that we don't really care about tracking (and don't want to deploy?)

# Core specific
find ./ -mindepth 1 -regex '^./\(tests\|docs\)\(/.*\)?' -delete

# General stuff
find ./ -mindepth 1 -regex '^./\(extensions\|skins\)/\w+/\(tests\|.phan\|.storybook\|.vscode\|.gitignore\|.eslintrc.json\|.gitreview\|.phpcs.xml\|.stylelintrc.json\|.rubocop\(_todo\)?.yml\)\(/.*\)?' -delete

# Things from extension distributor
find ./ -mindepth 1 -regex '^./extensions/Wikibase/\(vendor\|composer.lock\)\(/.*\)?' -delete

# Wikibase specific
find ./ -mindepth 1 -regex '^./extensions/Wikibase/\(\(data-access\|repo\|client\|lib\)/\)?\(build\|tests\|.phan\|.storybook\|.vscode\)\(/.*\)?' -delete