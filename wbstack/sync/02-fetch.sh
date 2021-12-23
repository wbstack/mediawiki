#!/usr/bin/env sh

# Using our own archive repo temporarily for our Federated Properties endeavour 
# discussion: https://github.com/wbstack/mediawiki/pull/155
./wbstack/sync/dwnthing.sh extensions/Wikibase https://github.com/wbstack/mediawiki-tars/raw/main/wikibase-feddy-props-1668.tar.gz & \

# Custom wbstack
./wbstack/sync/dwnthing.sh extensions/WikibaseInWikitext https://codeload.github.com/wbstack/mediawiki-extensions-WikibaseInWikitext/zip/445c7efaa145fa7c31b0caca7400ef6a87cac7d9 & \

# Custom wbstack / sandbox
./wbstack/sync/dwnthing.sh extensions/WikibaseExampleData https://codeload.github.com/wmde/WikibaseExampleData/zip/c129f0b759bf4602aa9b09e2bb9b694682784320 & \

# Elsewhere
# EmbedVideo is currently broken in releases due ot deprecated warnings https://gitlab.com/hydrawiki/extensions/EmbedVideo/-/issues/4784
# There was a MR up for fixing that https://gitlab.com/hydrawiki/extensions/EmbedVideo/-/merge_requests/148 so lets directly use that commit?
# We were using v2.9.0 before this
./wbstack/sync/dwnthing.sh extensions/EmbedVideo https://gitlab.com/jmnote/EmbedVideo/-/archive/e1e965527e19a00de34e534f87d6b7cdae8b262f/EmbedVideo-e1e965527e19a00de34e534f87d6b7cdae8b262f.zip & \
# https://github.com/ProfessionalWiki/WikibaseEdtf 1.2.0
./wbstack/sync/dwnthing.sh extensions/WikibaseEdtf https://codeload.github.com/ProfessionalWiki/WikibaseEdtf/zip/38b94853d1ece0e2dd742aa5aa925d51916a0a28 & \
./wbstack/sync/dwnthing.sh extensions/ThatSrc https://codeload.github.com/nyurik/ThatSrc/zip/3e039311504eb82f8c5c488a457b9e376b5cf7e3 & \

# And wait for all the background tasks to be done...
wait
