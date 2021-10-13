#!/usr/bin/env sh
# This script very simply and quickly downloads core, extensions and skins for the deployment.
# Nothing is faster :)

###########
# core
###########
./wbstack/sync/dwnthing.sh core https://codeload.github.com/wikimedia/mediawiki/zip/fa3248db2d1c81bf150c025318e6e7bd5438f6da

###########
# extensions
###########
./wbstack/sync/dwnthing.sh extensions/RevisionSlider https://codeload.github.com/wikimedia/mediawiki-extensions-RevisionSlider/zip/219b8784bc4b2bc9c954ad1a6ef0aa556a3aaf0f & \
./wbstack/sync/dwnthing.sh extensions/Mailgun https://codeload.github.com/wikimedia/mediawiki-extensions-Mailgun/zip/d1e1e3022b437374b962d3ee529388ae74ed3777 & \
./wbstack/sync/dwnthing.sh extensions/StopForumSpam https://codeload.github.com/wikimedia/mediawiki-extensions-StopForumSpam/zip/993d41f8b65eb9237f79db7e3559da3c49773911 & \
./wbstack/sync/dwnthing.sh extensions/SpamBlacklist https://codeload.github.com/wikimedia/mediawiki-extensions-SpamBlacklist/zip/51d9e302a4e01e3dbc671d6240422dbac5713930 & \
./wbstack/sync/dwnthing.sh extensions/ConfirmEdit https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmEdit/zip/8638ebdc99384cd2aae42588f34adb0f8c3f9af8 & \
./wbstack/sync/dwnthing.sh extensions/ConfirmAccount https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmAccount/zip/0e8a5607201eafad81d0ec14ef33cdfb5c026f62 & \
./wbstack/sync/dwnthing.sh extensions/Nuke https://codeload.github.com/wikimedia/mediawiki-extensions-Nuke/zip/ac55dc1d669b918bd5cd342519f72cb70dc0e9a8 & \
./wbstack/sync/dwnthing.sh extensions/InviteSignup https://codeload.github.com/wikimedia/mediawiki-extensions-InviteSignup/zip/2f3648d2e40cafdb9f2891f7687aa9daf31399a5 & \
./wbstack/sync/dwnthing.sh extensions/TorBlock https://codeload.github.com/wikimedia/mediawiki-extensions-TorBlock/zip/d99f902666735d32791682dc461fe127c12069e5 & \
./wbstack/sync/dwnthing.sh extensions/Elastica https://codeload.github.com/wikimedia/mediawiki-extensions-Elastica/zip/545651ce7d3329e182a8921bb03415f20d9b6e84 & \
./wbstack/sync/dwnthing.sh extensions/CirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-CirrusSearch/zip/95b958b0e4b2c0b1a5edaffa2926ba3e87c2f6f8 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseCirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseCirrusSearch/zip/51dc2da2abf21f061abd153d58d8a67e2fe4e4de & \
./wbstack/sync/dwnthing.sh extensions/WikibaseLexemeCirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseLexemeCirrusSearch/zip/09e0496469237b1b40ab55d53abb257bebddd240 & \
./wbstack/sync/dwnthing.sh extensions/UniversalLanguageSelector https://codeload.github.com/wikimedia/mediawiki-extensions-UniversalLanguageSelector/zip/852af5d7d4f4b611587906e1ab0394bc06ea47d4 & \
./wbstack/sync/dwnthing.sh extensions/cldr https://codeload.github.com/wikimedia/mediawiki-extensions-cldr/zip/78728ce0857671c593e56bc47e4f063f3a362911 & \
./wbstack/sync/dwnthing.sh extensions/Gadgets https://codeload.github.com/wikimedia/mediawiki-extensions-Gadgets/zip/5751717ae0c91b878fd8b71e425a128cf57c1649 & \
./wbstack/sync/dwnthing.sh extensions/Thanks https://codeload.github.com/wikimedia/mediawiki-extensions-Thanks/zip/ba334c97c6b347fbdeedf7295749ed3b3605f1b8 & \
./wbstack/sync/dwnthing.sh extensions/TwoColConflict https://codeload.github.com/wikimedia/mediawiki-extensions-TwoColConflict/zip/fdcab47d123ea1fbe0fb6ca216c3ffffb31af20a & \
./wbstack/sync/dwnthing.sh extensions/OAuth https://codeload.github.com/wikimedia/mediawiki-extensions-OAuth/zip/f9448b8a32297bff3b894d166581999c40dd57f6 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseLexeme https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseLexeme/zip/b27b2736ce69bac17d7bb20dad32142ad1bf311d & \
./wbstack/sync/dwnthing.sh extensions/SyntaxHighlight_GeSHi https://codeload.github.com/wikimedia/mediawiki-extensions-SyntaxHighlight_GeSHi/zip/672c897d6a804211673d17a86c172c6c9f379b4f & \
./wbstack/sync/dwnthing.sh extensions/JsonConfig https://codeload.github.com/wikimedia/mediawiki-extensions-JsonConfig/zip/6ac57ed56d2161c2498fcbdc10a5115a7b082f68 & \
./wbstack/sync/dwnthing.sh extensions/Kartographer https://codeload.github.com/wikimedia/mediawiki-extensions-Kartographer/zip/79188ba03b3a5c75bf95cbb3b830257952340060 & \
./wbstack/sync/dwnthing.sh extensions/Math https://codeload.github.com/wikimedia/mediawiki-extensions-Math/zip/4b82618e5018ac35609599241ed3e078a2e24a1a & \
./wbstack/sync/dwnthing.sh extensions/Score https://codeload.github.com/wikimedia/mediawiki-extensions-Score/zip/8259dd3bf8d62c3636e5ec387bc748efc5dc0a0b & \
./wbstack/sync/dwnthing.sh extensions/PageImages https://codeload.github.com/wikimedia/mediawiki-extensions-PageImages/zip/6fb30a84d76d00dd9136a5c07b94ec24b0a51c5f & \
./wbstack/sync/dwnthing.sh extensions/Scribunto https://codeload.github.com/wikimedia/mediawiki-extensions-Scribunto/zip/43b5f2e32886a78e7a03532a607487c8cc37887b & \
./wbstack/sync/dwnthing.sh extensions/Cite https://codeload.github.com/wikimedia/mediawiki-extensions-Cite/zip/1704e52943b29c1e1afd71f35dd393fffe7a47a9 & \
./wbstack/sync/dwnthing.sh extensions/TemplateSandbox https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateSandbox/zip/9debcf9c1ca374a5dd31b1d7021306b281d91e70 & \
./wbstack/sync/dwnthing.sh extensions/CodeEditor https://codeload.github.com/wikimedia/mediawiki-extensions-CodeEditor/zip/0f489204a3279847036c8edb9126fc0f83867d19 & \
./wbstack/sync/dwnthing.sh extensions/CodeMirror https://codeload.github.com/wikimedia/mediawiki-extensions-CodeMirror/zip/a63f3c2ab7b91ea4f744b309c057385c800d6f94 & \
./wbstack/sync/dwnthing.sh extensions/WikiEditor https://codeload.github.com/wikimedia/mediawiki-extensions-WikiEditor/zip/5b6fb3a0ee9870ee372f7e47dc992a41a2d2ffcf & \
./wbstack/sync/dwnthing.sh extensions/SecureLinkFixer https://codeload.github.com/wikimedia/mediawiki-extensions-SecureLinkFixer/zip/176b2345661dab3c31dfdef3b6d7943adf235b3b & \
./wbstack/sync/dwnthing.sh extensions/Echo https://codeload.github.com/wikimedia/mediawiki-extensions-Echo/zip/6581159f098c0f5d17de097af23df6fb9974d9dd & \
./wbstack/sync/dwnthing.sh extensions/Graph https://codeload.github.com/wikimedia/mediawiki-extensions-Graph/zip/f385b473a72d037c21937380bdde4e5987c25ec4 & \
./wbstack/sync/dwnthing.sh extensions/Poem https://codeload.github.com/wikimedia/mediawiki-extensions-Poem/zip/24a38dda46a5f7db935d5f54235a0f847297eaec & \
./wbstack/sync/dwnthing.sh extensions/TemplateData https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateData/zip/945a6d477a24126ba40e1727549c1f9ea1ce3379 & \
./wbstack/sync/dwnthing.sh extensions/AdvancedSearch https://codeload.github.com/wikimedia/mediawiki-extensions-AdvancedSearch/zip/15159a9b64884a15a36c4ac13b987fcd7aa3582e & \
./wbstack/sync/dwnthing.sh extensions/ParserFunctions https://codeload.github.com/wikimedia/mediawiki-extensions-ParserFunctions/zip/ee8b9333a097b435256413f50fdb98299ebb782c & \
./wbstack/sync/dwnthing.sh extensions/MobileFrontend https://codeload.github.com/wikimedia/mediawiki-extensions-MobileFrontend/zip/f6d5bccbd1f9b501e987ca1ba35b174d01da8d76 & \
./wbstack/sync/dwnthing.sh extensions/DeleteBatch https://codeload.github.com/wikimedia/mediawiki-extensions-DeleteBatch/zip/032cb95d670d36af2771189ce127585303fb0086 & \
./wbstack/sync/dwnthing.sh extensions/MultimediaViewer https://codeload.github.com/wikimedia/mediawiki-extensions-MultimediaViewer/zip/0cf9a8004298161ce603008053a65d6d186e6f0d & \
./wbstack/sync/dwnthing.sh extensions/Auth_remoteuser https://codeload.github.com/wikimedia/mediawiki-extensions-Auth_remoteuser/zip/3563afc4f639b52278901f0c95d9580d4e3ccda7 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseManifest https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseManifest/zip/f838c61febdda3ee2413ea7797c495a17929ff5b & \
./wbstack/sync/dwnthing.sh extensions/WikiHiero https://codeload.github.com/wikimedia/mediawiki-extensions-WikiHiero/zip/a410f443833f83a7801427e3fd5f4ab35e65cfe2 & \
./wbstack/sync/dwnthing.sh extensions/TextExtracts https://codeload.github.com/wikimedia/mediawiki-extensions-TextExtracts/zip/ddc48c4dcac2fbc2618483a379d13da617cd0986 & \
./wbstack/sync/dwnthing.sh extensions/Popups https://codeload.github.com/wikimedia/mediawiki-extensions-Popups/zip/b7f8dcec31e4254988eb1a2e86d52e6b6d9a96c2 & \


# Automatic script skips this one
# https://github.com/wikimedia/mediawiki-extensions-EntitySchema/tree/REL1_35
./wbstack/sync/dwnthing.sh extensions/EntitySchema https://codeload.github.com/wikimedia/mediawiki-extensions-EntitySchema/zip/e6019ac5b10a70f4cc98adf11b358917a3f6e85d & \

# Extension Distributor
# https://www.mediawiki.org/wiki/Special:ExtensionDistributor/Wikibase
#./wbstack/sync/dwnthing.sh extensions/Wikibase https://extdist.wmflabs.org/dist/extensions/Wikibase-REL1_35-1b0d104.tar.gz & \
./wbstack/sync/dwnthing.sh extensions/Wikibase https://releases.wikimedia.org/wikibase/1.35/wikibase.1.35.2-wmde.1.tar.gz & \

# Custom wbstack
./wbstack/sync/dwnthing.sh extensions/WikibaseInWikitext https://codeload.github.com/wbstack/mediawiki-extensions-WikibaseInWikitext/zip/445c7efaa145fa7c31b0caca7400ef6a87cac7d9 & \

# Custom wbstack / sandbox
./wbstack/sync/dwnthing.sh extensions/WikibaseExampleData https://codeload.github.com/wmde/WikibaseExampleData/zip/c129f0b759bf4602aa9b09e2bb9b694682784320 & \

# Elsewhere
./wbstack/sync/dwnthing.sh extensions/EmbedVideo https://gitlab.com/hydrawiki/extensions/EmbedVideo/-/archive/v2.9.0/EmbedVideo-v2.9.0.zip & \
# https://github.com/ProfessionalWiki/WikibaseEdtf 1.2.0
./wbstack/sync/dwnthing.sh extensions/WikibaseEdtf https://codeload.github.com/ProfessionalWiki/WikibaseEdtf/zip/38b94853d1ece0e2dd742aa5aa925d51916a0a28 & \
./wbstack/sync/dwnthing.sh extensions/ThatSrc https://codeload.github.com/nyurik/ThatSrc/zip/3e039311504eb82f8c5c488a457b9e376b5cf7e3 & \

###########
# skins
###########
./wbstack/sync/dwnthing.sh skins/Vector https://codeload.github.com/wikimedia/Vector/zip/1b03bafb1267f350ee2b0018da53c31ee0674f92 & \
./wbstack/sync/dwnthing.sh skins/Timeless https://codeload.github.com/wikimedia/mediawiki-skins-Timeless/zip/a2cdc93626f3036269cad979183ece6aabeae40a & \
./wbstack/sync/dwnthing.sh skins/Modern https://codeload.github.com/wikimedia/mediawiki-skins-Modern/zip/60565761fdf586681438e235db089106ba9cdaa4 & \
./wbstack/sync/dwnthing.sh skins/MinervaNeue https://codeload.github.com/wikimedia/mediawiki-skins-MinervaNeue/zip/6c99418af845a7761c246ee5a50fbb82715f4003 & \

# And wait for all the background tasks to be done...
wait
