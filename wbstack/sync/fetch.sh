#!/usr/bin/env sh
# This script very simply and quickly downloads core, extensions and skins for the deployment.
# Nothing is faster :)

###########
# core
###########
./wbstack/sync/dwnthing.sh core https://codeload.github.com/wikimedia/mediawiki/zip/1a3083861f0f3a5beeb256594ae8865701665479

###########
# extensions
###########
./wbstack/sync/dwnthing.sh extensions/RevisionSlider https://codeload.github.com/wikimedia/mediawiki-extensions-RevisionSlider/zip/8bdb906c8aaf7b5735873f6702b76b4210eb78ad & \
./wbstack/sync/dwnthing.sh extensions/Mailgun https://codeload.github.com/wikimedia/mediawiki-extensions-Mailgun/zip/7b0fe10c1639ec90eb056eb50e6d015ed5607fb7 & \
./wbstack/sync/dwnthing.sh extensions/ConfirmEdit https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmEdit/zip/eceeda60d620499a6f6990b08b0d5a264606c2de & \
./wbstack/sync/dwnthing.sh extensions/ConfirmAccount https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmAccount/zip/9b071fdf6dd90858c4a4419b39ec0650693632fc & \
./wbstack/sync/dwnthing.sh extensions/Nuke https://codeload.github.com/wikimedia/mediawiki-extensions-Nuke/zip/3de3b0f8889a10a824c205e6258c63da3afd2268 & \
./wbstack/sync/dwnthing.sh extensions/InviteSignup https://codeload.github.com/wikimedia/mediawiki-extensions-InviteSignup/zip/d84690985bb15ef01b0b61d9e32a2ea79d63d15e & \
./wbstack/sync/dwnthing.sh extensions/TorBlock https://codeload.github.com/wikimedia/mediawiki-extensions-TorBlock/zip/ac05aa0294b2bd5a37580c600517a62d60e28a05 & \
./wbstack/sync/dwnthing.sh extensions/Elastica https://codeload.github.com/wikimedia/mediawiki-extensions-Elastica/zip/3e3b76f3b7208167342fee843c401f2587dacde3 & \
./wbstack/sync/dwnthing.sh extensions/CirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-CirrusSearch/zip/ca9d1b7ad2139989dd6c9bbe46f931bad6a3f517 & \
./wbstack/sync/dwnthing.sh extensions/UniversalLanguageSelector https://codeload.github.com/wikimedia/mediawiki-extensions-UniversalLanguageSelector/zip/a3c19a98c3d1b90405af12f0680defee9efac42d & \
./wbstack/sync/dwnthing.sh extensions/cldr https://codeload.github.com/wikimedia/mediawiki-extensions-cldr/zip/fec3e2945455099866f79b314e58f9854dde30e6 & \
./wbstack/sync/dwnthing.sh extensions/Gadgets https://codeload.github.com/wikimedia/mediawiki-extensions-Gadgets/zip/1e54c8014828168d297795d515c12ef060f4f849 & \
./wbstack/sync/dwnthing.sh extensions/Thanks https://codeload.github.com/wikimedia/mediawiki-extensions-Thanks/zip/50e0715de112b1b353265a12e4f17ac804a3b181 & \
./wbstack/sync/dwnthing.sh extensions/TwoColConflict https://codeload.github.com/wikimedia/mediawiki-extensions-TwoColConflict/zip/0f00dc06b31eed9413e800add282ffe1437fb1ae & \
./wbstack/sync/dwnthing.sh extensions/OAuth https://codeload.github.com/wikimedia/mediawiki-extensions-OAuth/zip/c66d88205019919dd596b894df90d7abb612cecc & \
./wbstack/sync/dwnthing.sh extensions/WikibaseLexeme https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseLexeme/zip/f26a9bb34b1e30893035cb262f52c502e0616d2d & \
./wbstack/sync/dwnthing.sh extensions/SyntaxHighlight_GeSHi https://codeload.github.com/wikimedia/mediawiki-extensions-SyntaxHighlight_GeSHi/zip/5fecf46ef78302a87addc65678b674f9733f0b01 & \
./wbstack/sync/dwnthing.sh extensions/JsonConfig https://codeload.github.com/wikimedia/mediawiki-extensions-JsonConfig/zip/1b798794ae4ab32ae41adc2013d26e5e0be5262e & \
./wbstack/sync/dwnthing.sh extensions/Kartographer https://codeload.github.com/wikimedia/mediawiki-extensions-Kartographer/zip/7272b2b93d9fc397e74844be86ab576a308819d0 & \
./wbstack/sync/dwnthing.sh extensions/Math https://codeload.github.com/wikimedia/mediawiki-extensions-Math/zip/a412f37c6d2ad966edbc7fca923b2de51067399d & \
./wbstack/sync/dwnthing.sh extensions/Score https://codeload.github.com/wikimedia/mediawiki-extensions-Score/zip/13d0ffc9a94c7bac0d445cd3b59dbecec2f69108 & \
./wbstack/sync/dwnthing.sh extensions/PageImages https://codeload.github.com/wikimedia/mediawiki-extensions-PageImages/zip/9a2f95e712ccfbe255b7cb192ca6a597cd08d117 & \
./wbstack/sync/dwnthing.sh extensions/Scribunto https://codeload.github.com/wikimedia/mediawiki-extensions-Scribunto/zip/d21b655d99b60ee678538125c3f323f30b748bf6 & \
./wbstack/sync/dwnthing.sh extensions/Cite https://codeload.github.com/wikimedia/mediawiki-extensions-Cite/zip/52f3cf3aab68a3610fa82d02f1dd17e06f15c703 & \
./wbstack/sync/dwnthing.sh extensions/TemplateSandbox https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateSandbox/zip/291e326fa3e071b84b9c81841c88894ae8a5dca4 & \
./wbstack/sync/dwnthing.sh extensions/CodeEditor https://codeload.github.com/wikimedia/mediawiki-extensions-CodeEditor/zip/1fafe27dc395ba1bc636f69fca15e1bb5a59caae & \
./wbstack/sync/dwnthing.sh extensions/WikiEditor https://codeload.github.com/wikimedia/mediawiki-extensions-WikiEditor/zip/9bc2d0b806a33777fd66afb5e272203f735567e8 & \
./wbstack/sync/dwnthing.sh extensions/SecureLinkFixer https://codeload.github.com/wikimedia/mediawiki-extensions-SecureLinkFixer/zip/47f12670b23b5a59e7fdd1a2bd9b5a49693940c8 & \
./wbstack/sync/dwnthing.sh extensions/Echo https://codeload.github.com/wikimedia/mediawiki-extensions-Echo/zip/fd6a33e7b8f2945777a9182a13dbdb74a61b8159 & \
./wbstack/sync/dwnthing.sh extensions/Graph https://codeload.github.com/wikimedia/mediawiki-extensions-Graph/zip/69ec90ac966fdaea923ca6268fb42afe3f9fdbf8 & \
./wbstack/sync/dwnthing.sh extensions/Poem https://codeload.github.com/wikimedia/mediawiki-extensions-Poem/zip/447d8b7a3a16dbb4ad820d743dc685a4ec70bf16 & \
./wbstack/sync/dwnthing.sh extensions/TemplateData https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateData/zip/ba41c7d0b1b803167d0f33a615733310132133a0 & \
./wbstack/sync/dwnthing.sh extensions/AdvancedSearch https://codeload.github.com/wikimedia/mediawiki-extensions-AdvancedSearch/zip/e58572a4b80295af45f7e4b704118fa3b42ea436 & \
./wbstack/sync/dwnthing.sh extensions/ParserFunctions https://codeload.github.com/wikimedia/mediawiki-extensions-ParserFunctions/zip/59a91354754ac78b1f49de1cf7e5dfca204e6ce7 & \
./wbstack/sync/dwnthing.sh extensions/MobileFrontend https://codeload.github.com/wikimedia/mediawiki-extensions-MobileFrontend/zip/1421405c1fe4f3dbafe0cd18f43aa9054b974c4e & \
./wbstack/sync/dwnthing.sh extensions/DeleteBatch https://codeload.github.com/wikimedia/mediawiki-extensions-DeleteBatch/zip/3650f3c94f42b77e099907102fb7e0f456af5ff2 & \
./wbstack/sync/dwnthing.sh extensions/MultimediaViewer https://codeload.github.com/wikimedia/mediawiki-extensions-MultimediaViewer/zip/4b3219b60eb6c3b9718ff18887610e29cb88cc69 & \
./wbstack/sync/dwnthing.sh extensions/Auth_remoteuser https://codeload.github.com/wikimedia/mediawiki-extensions-Auth_remoteuser/zip/6f570b83ff3fe9502a43f7894533273ff37d41f2 & \

# Automatic script skips this one
# https://github.com/wikimedia/mediawiki-extensions-EntitySchema/tree/REL1_35
./wbstack/sync/dwnthing.sh extensions/EntitySchema https://codeload.github.com/wikimedia/mediawiki-extensions-EntitySchema/zip/e6019ac5b10a70f4cc98adf11b358917a3f6e85d & \

# Extension Distributor
# https://www.mediawiki.org/wiki/Special:ExtensionDistributor/Wikibase
./wbstack/sync/dwnthing.sh extensions/Wikibase-tmp https://extdist.wmflabs.org/dist/extensions/Wikibase-REL1_35-ea86f45.tar.gz & \
# XXX: fetch normal 1_35 into a tmp dir, and the fed props code into a new dir.
# These are the merged evily further down this file...
# because submodules...
./wbstack/sync/dwnthing.sh extensions/Wikibase https://codeload.github.com/addshore/Wikibase/zip/c61b0865d487b0f93f9a195e0cfcb449b86994d7 & \

# Custom wbstack
./wbstack/sync/dwnthing.sh extensions/WikibaseInWikitext https://codeload.github.com/wbstack/mediawiki-extensions-WikibaseInWikitext/zip/445c7efaa145fa7c31b0caca7400ef6a87cac7d9 & \

# Custom wbstack / sandbox
./wbstack/sync/dwnthing.sh extensions/WikibaseExampleData https://codeload.github.com/wmde/WikibaseExampleData/zip/c129f0b759bf4602aa9b09e2bb9b694682784320 & \

# Elsewhere
./wbstack/sync/dwnthing.sh extensions/EmbedVideo https://gitlab.com/hydrawiki/extensions/EmbedVideo/-/archive/v2.9.0/EmbedVideo-v2.9.0.zip & \

###########
# skins
###########
./wbstack/sync/dwnthing.sh skins/Vector https://codeload.github.com/wikimedia/Vector/zip/771c8764c856a2b9ed2a8ff84ad5cfd38cdfaf5e & \
./wbstack/sync/dwnthing.sh skins/Timeless https://codeload.github.com/wikimedia/mediawiki-skins-Timeless/zip/c2b39e7b08ffa210c6cda09ee4da89a0b3e64345 & \
./wbstack/sync/dwnthing.sh skins/Modern https://codeload.github.com/wikimedia/mediawiki-skins-Modern/zip/d84e84e7b08cc03963099081a269e26b3030b586 & \
./wbstack/sync/dwnthing.sh skins/MinervaNeue https://codeload.github.com/wikimedia/mediawiki-skins-MinervaNeue/zip/aa4236267cac13ab52f2ecaf47396ae3302821a6 & \

# And wait for all the background tasks to be done...
wait

cp -r ./extensions/Wikibase-tmp/view/lib/wikibase-data-values-value-view ./extensions/Wikibase/view/lib
cp -r ./extensions/Wikibase-tmp/view/lib/wikibase-serialization ./extensions/Wikibase/view/lib
cp -r ./extensions/Wikibase-tmp/view/lib/wikibase-data-values ./extensions/Wikibase/view/lib
cp -r ./extensions/Wikibase-tmp/view/lib/wikibase-data-model ./extensions/Wikibase/view/lib
cp -r ./extensions/Wikibase-tmp/view/lib/wikibase-termbox ./extensions/Wikibase/view/lib
cp -r ./extensions/Wikibase-tmp/lib/resources/wikibase-api ./extensions/Wikibase/lib/resources
rm -rf ./extensions/Wikibase-tmp