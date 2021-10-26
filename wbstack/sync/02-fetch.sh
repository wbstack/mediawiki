#!/usr/bin/env sh
# This script very simply and quickly downloads core, extensions and skins for the deployment.
# Nothing is faster :)

###########
# core
###########
./wbstack/sync/dwnthing.sh core https://codeload.github.com/wikimedia/mediawiki/zip/cb7d66821512772c5b207591ed7e012518221509

###########
# extensions
###########
./wbstack/sync/dwnthing.sh extensions/RevisionSlider https://codeload.github.com/wikimedia/mediawiki-extensions-RevisionSlider/zip/e998cb97e7f01062ff08178cd57fcd8d316e0d44 & \
./wbstack/sync/dwnthing.sh extensions/Mailgun https://codeload.github.com/wikimedia/mediawiki-extensions-Mailgun/zip/d7f015194de8b1673a4429f7303368c34c099050 & \
./wbstack/sync/dwnthing.sh extensions/StopForumSpam https://codeload.github.com/wikimedia/mediawiki-extensions-StopForumSpam/zip/c63878f5fda752f83f563799c957a120a972163c & \
./wbstack/sync/dwnthing.sh extensions/SpamBlacklist https://codeload.github.com/wikimedia/mediawiki-extensions-SpamBlacklist/zip/5b978fb6b258185247b428313be0808e1fc9125c & \
./wbstack/sync/dwnthing.sh extensions/ConfirmEdit https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmEdit/zip/08afeb725ef590ff235732939bc0869e1e37e821 & \
./wbstack/sync/dwnthing.sh extensions/ConfirmAccount https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmAccount/zip/3195e5a4864ae480aacd62828d392dbf7988ba66 & \
./wbstack/sync/dwnthing.sh extensions/Nuke https://codeload.github.com/wikimedia/mediawiki-extensions-Nuke/zip/cbb00717fefadd170e25c0ce35ef084d505265e2 & \
./wbstack/sync/dwnthing.sh extensions/InviteSignup https://codeload.github.com/wikimedia/mediawiki-extensions-InviteSignup/zip/fceeffdbbea6d981e5124ce8480286074bb5a3a2 & \
./wbstack/sync/dwnthing.sh extensions/TorBlock https://codeload.github.com/wikimedia/mediawiki-extensions-TorBlock/zip/58f993a1f18051bbe2b4cdb7fb01e90872085902 & \
./wbstack/sync/dwnthing.sh extensions/Elastica https://codeload.github.com/wikimedia/mediawiki-extensions-Elastica/zip/3832bd67d4f6fd52fc8a84695084c7b30dce5a4e & \
./wbstack/sync/dwnthing.sh extensions/CirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-CirrusSearch/zip/efdd2f08a10973e86f08c617c962d52d4a945a05 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseCirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseCirrusSearch/zip/16de875592873033d85220adb0bcf995c6c20935 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseLexemeCirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseLexemeCirrusSearch/zip/9e12518819ec5ca52fa0289bf12309a07f432502 & \
./wbstack/sync/dwnthing.sh extensions/UniversalLanguageSelector https://codeload.github.com/wikimedia/mediawiki-extensions-UniversalLanguageSelector/zip/411c7ce102f76f368788e3beb068001ae2d01315 & \
./wbstack/sync/dwnthing.sh extensions/cldr https://codeload.github.com/wikimedia/mediawiki-extensions-cldr/zip/107d7a736e77852424af0ef918bf206e883db2d5 & \
./wbstack/sync/dwnthing.sh extensions/Gadgets https://codeload.github.com/wikimedia/mediawiki-extensions-Gadgets/zip/2494f950ab7e7ead0fa207df58099a7198ba90ed & \
./wbstack/sync/dwnthing.sh extensions/Thanks https://codeload.github.com/wikimedia/mediawiki-extensions-Thanks/zip/1c071f4d4eff48edd8c01687c78523994d6a21cc & \
./wbstack/sync/dwnthing.sh extensions/TwoColConflict https://codeload.github.com/wikimedia/mediawiki-extensions-TwoColConflict/zip/9eac005b2185da3e5b8b60d56e850779d8534a5a & \
./wbstack/sync/dwnthing.sh extensions/OAuth https://codeload.github.com/wikimedia/mediawiki-extensions-OAuth/zip/705f696fb21ca86cd7b10df0cf692c954c066f88 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseLexeme https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseLexeme/zip/8f8fb1c18fd2fed038e094e70a61b8fa4b9ee259 & \
./wbstack/sync/dwnthing.sh extensions/SyntaxHighlight_GeSHi https://codeload.github.com/wikimedia/mediawiki-extensions-SyntaxHighlight_GeSHi/zip/64eacfac9fcd0f5d6684588d47a90cdb4b5aa02e & \
./wbstack/sync/dwnthing.sh extensions/JsonConfig https://codeload.github.com/wikimedia/mediawiki-extensions-JsonConfig/zip/10b1422f2e60cb54b672f4daeb06dc41880089c6 & \
./wbstack/sync/dwnthing.sh extensions/Kartographer https://codeload.github.com/wikimedia/mediawiki-extensions-Kartographer/zip/5969328a369c2a637a84db17d4763a4be1a8ba42 & \
./wbstack/sync/dwnthing.sh extensions/Math https://codeload.github.com/wikimedia/mediawiki-extensions-Math/zip/93b1252386dcd8658a285571a76cea0c6852bb0e & \
./wbstack/sync/dwnthing.sh extensions/Score https://codeload.github.com/wikimedia/mediawiki-extensions-Score/zip/34583fc02f500fb1760774aa9a97bb23ec1d8a33 & \
./wbstack/sync/dwnthing.sh extensions/PageImages https://codeload.github.com/wikimedia/mediawiki-extensions-PageImages/zip/7cc0165545cac27aa8b677b0f1b438126ce696b4 & \
./wbstack/sync/dwnthing.sh extensions/Scribunto https://codeload.github.com/wikimedia/mediawiki-extensions-Scribunto/zip/c1fe9e3f20dcbd27e4d1571a82b17fd190fe7fee & \
./wbstack/sync/dwnthing.sh extensions/Cite https://codeload.github.com/wikimedia/mediawiki-extensions-Cite/zip/8b606402da2b91c4b7ed91d16b56ddcee43e6349 & \
./wbstack/sync/dwnthing.sh extensions/TemplateSandbox https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateSandbox/zip/68b9bfbd125e6bf77a06e40d03d434629ae918e1 & \
./wbstack/sync/dwnthing.sh extensions/CodeEditor https://codeload.github.com/wikimedia/mediawiki-extensions-CodeEditor/zip/f0f99f4471fe9184a83c2b6ee122ae24e599d8f6 & \
./wbstack/sync/dwnthing.sh extensions/CodeMirror https://codeload.github.com/wikimedia/mediawiki-extensions-CodeMirror/zip/1a62716cf00ec76a66ea2f04ec42a6ea81ae87e6 & \
./wbstack/sync/dwnthing.sh extensions/WikiEditor https://codeload.github.com/wikimedia/mediawiki-extensions-WikiEditor/zip/2ea1902606b16811b8beb71be4276e573e4e7dfe & \
./wbstack/sync/dwnthing.sh extensions/SecureLinkFixer https://codeload.github.com/wikimedia/mediawiki-extensions-SecureLinkFixer/zip/19998b69fad24db059d5a278557d673ec5206e3a & \
./wbstack/sync/dwnthing.sh extensions/Echo https://codeload.github.com/wikimedia/mediawiki-extensions-Echo/zip/761da0fbeb36d50a80b60de6bb9d2e17c2395d72 & \
./wbstack/sync/dwnthing.sh extensions/Graph https://codeload.github.com/wikimedia/mediawiki-extensions-Graph/zip/87fbbe73208fc8643b87760235068c862c26e72f & \
./wbstack/sync/dwnthing.sh extensions/Poem https://codeload.github.com/wikimedia/mediawiki-extensions-Poem/zip/c9d264a30dbc74fb130bd16b19415b57809e36e8 & \
./wbstack/sync/dwnthing.sh extensions/TemplateData https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateData/zip/0a7d4d6b5b3892d300582e32c0f1aea95fcb7c2e & \
./wbstack/sync/dwnthing.sh extensions/AdvancedSearch https://codeload.github.com/wikimedia/mediawiki-extensions-AdvancedSearch/zip/829a9844277e867bf3da3712fe1db4c741826265 & \
./wbstack/sync/dwnthing.sh extensions/ParserFunctions https://codeload.github.com/wikimedia/mediawiki-extensions-ParserFunctions/zip/91f0a5cad9d5cc7f767a842f28642a4afd7bea14 & \
./wbstack/sync/dwnthing.sh extensions/MobileFrontend https://codeload.github.com/wikimedia/mediawiki-extensions-MobileFrontend/zip/f78273cce77e875d878cb394397d8bf086bbfe92 & \
./wbstack/sync/dwnthing.sh extensions/DeleteBatch https://codeload.github.com/wikimedia/mediawiki-extensions-DeleteBatch/zip/1883694bd102b7f8ed0ce43a3f41db6c5c8d09e3 & \
./wbstack/sync/dwnthing.sh extensions/MultimediaViewer https://codeload.github.com/wikimedia/mediawiki-extensions-MultimediaViewer/zip/8ee75c3e10493944cd64d9f56b726a6d27889d19 & \
./wbstack/sync/dwnthing.sh extensions/Auth_remoteuser https://codeload.github.com/wikimedia/mediawiki-extensions-Auth_remoteuser/zip/c8b6774eb7f3e8ced57c5b2b0ff903d180872d91 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseManifest https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseManifest/zip/0011dbcfbde4ffff16ab475a0ca41eca5f875473 & \
./wbstack/sync/dwnthing.sh extensions/WikiHiero https://codeload.github.com/wikimedia/mediawiki-extensions-WikiHiero/zip/55780da132d505867fe90939d7839b6a0f2ff776 & \
./wbstack/sync/dwnthing.sh extensions/TextExtracts https://codeload.github.com/wikimedia/mediawiki-extensions-TextExtracts/zip/dd386155d3e1291e57b13769ea1e093889260199 & \
./wbstack/sync/dwnthing.sh extensions/Popups https://codeload.github.com/wikimedia/mediawiki-extensions-Popups/zip/9a65e10bcb44bfa8fd4f6002828335647f1755fc & \


# Automatic script skips this one
# https://github.com/wikimedia/mediawiki-extensions-EntitySchema/tree/REL1_36
./wbstack/sync/dwnthing.sh extensions/EntitySchema https://codeload.github.com/wikimedia/mediawiki-extensions-EntitySchema/zip/27f9ac10e49f2291ff665e81778fc70be8ddbcc4 & \

# Extension Distributor
# https://www.mediawiki.org/wiki/Special:ExtensionDistributor/Wikibase
./wbstack/sync/dwnthing.sh extensions/Wikibase https://extdist.wmflabs.org/dist/extensions/Wikibase-REL1_36-ef9ccdc.tar.gz & \

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
./wbstack/sync/dwnthing.sh skins/Vector https://codeload.github.com/wikimedia/Vector/zip/a2721bf9c3772b45bb13520755034edda52a3e4e & \
./wbstack/sync/dwnthing.sh skins/Timeless https://codeload.github.com/wikimedia/mediawiki-skins-Timeless/zip/48390b5ca8891d1f90a5d55cb6d8895ec47b3dad & \
./wbstack/sync/dwnthing.sh skins/Modern https://codeload.github.com/wikimedia/mediawiki-skins-Modern/zip/9b921dafeabd644c0388f5e495e00a530d10dc04 & \
./wbstack/sync/dwnthing.sh skins/MinervaNeue https://codeload.github.com/wikimedia/mediawiki-skins-MinervaNeue/zip/7203827a86fc670df4cd41821257d565020321a0 & \

# And wait for all the background tasks to be done...
wait
