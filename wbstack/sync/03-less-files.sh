#!/usr/bin/env sh
# Delete a bunch of files that we don't really care about tracking (and don't want to deploy?)

echo "Deleting a bunch of not needed files...."

# Core specific
find ./ -mindepth 1 -regex '^./\(tests\|docs\)\(/.*\)?' -delete

# General stuff
find ./ -mindepth 1 -regex '^./\(extensions\|skins\)/\w+/\(tests\|.phan\|.storybook\|.vscode\|.git\|.gitignore\|.eslintrc.json\|.gitreview\|.phpcs.xml\|Gruntfile.js\|Doxyfile\|.stylelintrc.json\|.rubocop\(_todo\)?.yml\)\(/.*\)?' -delete

# Wikibase packaged vendor and composer lock (TODO do this for all skins and extensions?)
find ./ -mindepth 1 -regex '^./extensions/Wikibase/\(vendor\|composer.lock\)\(/.*\)?' -delete

# Extension specific
find ./ -mindepth 1 -regex '^./extensions/Wikibase/\(\(data-access\|repo\|client\|lib\)/\)?\(build\|tests\|.phan\|.storybook\|.vscode\)\(/.*\)?' -delete
find ./ -mindepth 1 -regex '^./extensions/WikibaseManifest/\(infrastructure\)\(/.*\)?' -delete
find ./ -mindepth 1 -regex '^./extensions/VisualEditor/lib/ve/.git\(/.*\)?' -delete
