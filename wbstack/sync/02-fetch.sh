#!/usr/bin/env sh

# Using our own archive repo temporarily for our Federated Properties endeavour 
# discussion: https://github.com/wbstack/mediawiki/pull/155
./wbstack/sync/dwnthing.sh extensions/Wikibase https://github.com/wbstack/mediawiki-tars/raw/main/wikibase-feddy-props-1668.tar.gz & \

# Elsewhere
# EmbedVideo is currently broken in releases due ot deprecated warnings https://gitlab.com/hydrawiki/extensions/EmbedVideo/-/issues/4784
# There was a MR up for fixing that https://gitlab.com/hydrawiki/extensions/EmbedVideo/-/merge_requests/148 so lets directly use that commit?
# We were using v2.9.0 before this
./wbstack/sync/dwnthing.sh extensions/EmbedVideo https://gitlab.com/jmnote/EmbedVideo/-/archive/e1e965527e19a00de34e534f87d6b7cdae8b262f/EmbedVideo-e1e965527e19a00de34e534f87d6b7cdae8b262f.zip & \

# And wait for all the background tasks to be done...
wait
