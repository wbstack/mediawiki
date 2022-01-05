#!/usr/bin/env sh
# Delete a bunch of files that we don't really care about tracking (and don't want to deploy?)

echo "Deleting a bunch of not needed files...."

# Core specific
find ./dist -mindepth 1 -regex '^./dist/\(tests\|docs\)\(/.*\)?' -delete
find ./dist -mindepth 1 -path './dist/extensions/README' -delete -o -path './dist/skins/README' -delete

# General stuff
find ./dist -mindepth 1 -regex '^./dist/\(extensions\|skins\)/\w+/\(tests\|.phan\|.storybook\|.vscode\|.gitignore\|.eslintrc.json\|.gitreview\|.phpcs.xml\|Gruntfile.js\|Doxyfile\|.stylelintrc.json\|.rubocop\(_todo\)?.yml\)\(/.*\)?' -delete
