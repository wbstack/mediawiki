#!/usr/bin/env sh
# This script very simply and quickly downloads core, extensions and skins for the deployment.
# Nothing is faster :)

###########
# core
###########
./wbstack/sync/dwnthing.sh core https://codeload.github.com/wikimedia/mediawiki/zip/50af9adc3c8db3b5321c06322cf67a28f5224bbe

###########
# extensions
###########
./wbstack/sync/dwnthing.sh extensions/RevisionSlider https://codeload.github.com/wikimedia/mediawiki-extensions-RevisionSlider/zip/d1a6af207e26e220d93d16381a58055259575d3b & \
./wbstack/sync/dwnthing.sh extensions/Mailgun https://codeload.github.com/wikimedia/mediawiki-extensions-Mailgun/zip/7b0fe10c1639ec90eb056eb50e6d015ed5607fb7 & \
./wbstack/sync/dwnthing.sh extensions/ConfirmEdit https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmEdit/zip/eceeda60d620499a6f6990b08b0d5a264606c2de & \
./wbstack/sync/dwnthing.sh extensions/ConfirmAccount https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmAccount/zip/341181fc7acdc85ee2b507b76286787fbf2fda1f & \
./wbstack/sync/dwnthing.sh extensions/Nuke https://codeload.github.com/wikimedia/mediawiki-extensions-Nuke/zip/3de3b0f8889a10a824c205e6258c63da3afd2268 & \
./wbstack/sync/dwnthing.sh extensions/InviteSignup https://codeload.github.com/wikimedia/mediawiki-extensions-InviteSignup/zip/d84690985bb15ef01b0b61d9e32a2ea79d63d15e & \
./wbstack/sync/dwnthing.sh extensions/TorBlock https://codeload.github.com/wikimedia/mediawiki-extensions-TorBlock/zip/ac05aa0294b2bd5a37580c600517a62d60e28a05 & \
./wbstack/sync/dwnthing.sh extensions/Elastica https://codeload.github.com/wikimedia/mediawiki-extensions-Elastica/zip/c101a4c17fff7e8711b0199cc9f7c342699e1221 & \
./wbstack/sync/dwnthing.sh extensions/CirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-CirrusSearch/zip/6379aa8cda5a43e6852213dba9f0196d8d412d8b & \
./wbstack/sync/dwnthing.sh extensions/UniversalLanguageSelector https://codeload.github.com/wikimedia/mediawiki-extensions-UniversalLanguageSelector/zip/a3c19a98c3d1b90405af12f0680defee9efac42d & \
./wbstack/sync/dwnthing.sh extensions/cldr https://codeload.github.com/wikimedia/mediawiki-extensions-cldr/zip/fec3e2945455099866f79b314e58f9854dde30e6 & \
./wbstack/sync/dwnthing.sh extensions/Gadgets https://codeload.github.com/wikimedia/mediawiki-extensions-Gadgets/zip/1e54c8014828168d297795d515c12ef060f4f849 & \
./wbstack/sync/dwnthing.sh extensions/Thanks https://codeload.github.com/wikimedia/mediawiki-extensions-Thanks/zip/50e0715de112b1b353265a12e4f17ac804a3b181 & \
./wbstack/sync/dwnthing.sh extensions/TwoColConflict https://codeload.github.com/wikimedia/mediawiki-extensions-TwoColConflict/zip/29639ad668e300a8e9786e2bd7a87b9c0a5c94a9 & \
./wbstack/sync/dwnthing.sh extensions/OAuth https://codeload.github.com/wikimedia/mediawiki-extensions-OAuth/zip/b697cebae32783477921226e683114ec4cc8a757 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseLexeme https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseLexeme/zip/f26a9bb34b1e30893035cb262f52c502e0616d2d & \
./wbstack/sync/dwnthing.sh extensions/SyntaxHighlight_GeSHi https://codeload.github.com/wikimedia/mediawiki-extensions-SyntaxHighlight_GeSHi/zip/0e4cce61224b64b2eced7b6ba6d3830e88ad1c2b & \
./wbstack/sync/dwnthing.sh extensions/JsonConfig https://codeload.github.com/wikimedia/mediawiki-extensions-JsonConfig/zip/1b798794ae4ab32ae41adc2013d26e5e0be5262e & \
./wbstack/sync/dwnthing.sh extensions/Kartographer https://codeload.github.com/wikimedia/mediawiki-extensions-Kartographer/zip/ce6de47d56e317885aaa9900a94a98a266018d71 & \
./wbstack/sync/dwnthing.sh extensions/Math https://codeload.github.com/wikimedia/mediawiki-extensions-Math/zip/f849125ef5f20591452bb55457d9fda6048c6810 & \
./wbstack/sync/dwnthing.sh extensions/Score https://codeload.github.com/wikimedia/mediawiki-extensions-Score/zip/13d0ffc9a94c7bac0d445cd3b59dbecec2f69108 & \
./wbstack/sync/dwnthing.sh extensions/PageImages https://codeload.github.com/wikimedia/mediawiki-extensions-PageImages/zip/9a2f95e712ccfbe255b7cb192ca6a597cd08d117 & \
./wbstack/sync/dwnthing.sh extensions/Scribunto https://codeload.github.com/wikimedia/mediawiki-extensions-Scribunto/zip/d21b655d99b60ee678538125c3f323f30b748bf6 & \
./wbstack/sync/dwnthing.sh extensions/Cite https://codeload.github.com/wikimedia/mediawiki-extensions-Cite/zip/bc16b0527f34f002d327154092cc05a5d2dcb0b9 & \
./wbstack/sync/dwnthing.sh extensions/TemplateSandbox https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateSandbox/zip/291e326fa3e071b84b9c81841c88894ae8a5dca4 & \
./wbstack/sync/dwnthing.sh extensions/CodeEditor https://codeload.github.com/wikimedia/mediawiki-extensions-CodeEditor/zip/1fafe27dc395ba1bc636f69fca15e1bb5a59caae & \
./wbstack/sync/dwnthing.sh extensions/CodeMirror https://codeload.github.com/wikimedia/mediawiki-extensions-CodeMirror/zip/b5c9a1b6d225875ad531d8ced8f5d81f996880ca & \
./wbstack/sync/dwnthing.sh extensions/WikiEditor https://codeload.github.com/wikimedia/mediawiki-extensions-WikiEditor/zip/9bc2d0b806a33777fd66afb5e272203f735567e8 & \
./wbstack/sync/dwnthing.sh extensions/SecureLinkFixer https://codeload.github.com/wikimedia/mediawiki-extensions-SecureLinkFixer/zip/47f12670b23b5a59e7fdd1a2bd9b5a49693940c8 & \
./wbstack/sync/dwnthing.sh extensions/Echo https://codeload.github.com/wikimedia/mediawiki-extensions-Echo/zip/17c90bcf7abf384aac28674b70cf4078aa8995eb & \
./wbstack/sync/dwnthing.sh extensions/Graph https://codeload.github.com/wikimedia/mediawiki-extensions-Graph/zip/09f0338473212d4829f73b4571b55775b59c3c80 & \
./wbstack/sync/dwnthing.sh extensions/Poem https://codeload.github.com/wikimedia/mediawiki-extensions-Poem/zip/447d8b7a3a16dbb4ad820d743dc685a4ec70bf16 & \
./wbstack/sync/dwnthing.sh extensions/TemplateData https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateData/zip/ba41c7d0b1b803167d0f33a615733310132133a0 & \
./wbstack/sync/dwnthing.sh extensions/AdvancedSearch https://codeload.github.com/wikimedia/mediawiki-extensions-AdvancedSearch/zip/e58572a4b80295af45f7e4b704118fa3b42ea436 & \
./wbstack/sync/dwnthing.sh extensions/ParserFunctions https://codeload.github.com/wikimedia/mediawiki-extensions-ParserFunctions/zip/59a91354754ac78b1f49de1cf7e5dfca204e6ce7 & \
./wbstack/sync/dwnthing.sh extensions/MobileFrontend https://codeload.github.com/wikimedia/mediawiki-extensions-MobileFrontend/zip/1421405c1fe4f3dbafe0cd18f43aa9054b974c4e & \
./wbstack/sync/dwnthing.sh extensions/DeleteBatch https://codeload.github.com/wikimedia/mediawiki-extensions-DeleteBatch/zip/3650f3c94f42b77e099907102fb7e0f456af5ff2 & \
./wbstack/sync/dwnthing.sh extensions/MultimediaViewer https://codeload.github.com/wikimedia/mediawiki-extensions-MultimediaViewer/zip/4b3219b60eb6c3b9718ff18887610e29cb88cc69 & \
./wbstack/sync/dwnthing.sh extensions/Auth_remoteuser https://codeload.github.com/wikimedia/mediawiki-extensions-Auth_remoteuser/zip/6f570b83ff3fe9502a43f7894533273ff37d41f2 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseManifest https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseManifest/zip/ebabf21b37f801f25d4802024cdc2dcda21a1f0f & \
./wbstack/sync/dwnthing.sh extensions/WikiHiero https://codeload.github.com/wikimedia/mediawiki-extensions-WikiHiero/zip/c32ec80791eca5a031963d1c3029e0bc5b71808c & \


# Automatic script skips this one
# https://github.com/wikimedia/mediawiki-extensions-EntitySchema/tree/REL1_35
./wbstack/sync/dwnthing.sh extensions/EntitySchema https://codeload.github.com/wikimedia/mediawiki-extensions-EntitySchema/zip/e6019ac5b10a70f4cc98adf11b358917a3f6e85d & \

# Extension Distributor
# https://www.mediawiki.org/wiki/Special:ExtensionDistributor/Wikibase
./wbstack/sync/dwnthing.sh extensions/Wikibase https://extdist.wmflabs.org/dist/extensions/Wikibase-REL1_35-1b0d104.tar.gz & \

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
./wbstack/sync/dwnthing.sh skins/MinervaNeue https://codeload.github.com/wikimedia/mediawiki-skins-MinervaNeue/zip/4f165666d0f69fa4d70a29d3221f2147fc6255ae & \

# And wait for all the background tasks to be done...
wait
