#!/usr/bin/env sh
# This script very simply and quickly downloads core, extensions and skins for the deployment.
# Nothing is faster :)

###########
# core
###########
./wbstack/sync/dwnthing.sh core https://codeload.github.com/wikimedia/mediawiki/zip/923676858a9521c96b7051641010d920796bb408

###########
# extensions
###########
./wbstack/sync/dwnthing.sh extensions/RevisionSlider https://codeload.github.com/wikimedia/mediawiki-extensions-RevisionSlider/zip/d1a6af207e26e220d93d16381a58055259575d3b & \
./wbstack/sync/dwnthing.sh extensions/Mailgun https://codeload.github.com/wikimedia/mediawiki-extensions-Mailgun/zip/a6606b1cfdeee506819bea8a220c3f32e4504894 & \
./wbstack/sync/dwnthing.sh extensions/SpamBlacklist https://codeload.github.com/wikimedia/mediawiki-extensions-SpamBlacklist/zip/b4579e6fd6c7b60581e08b35fe55c2c86f4b4b0c & \
./wbstack/sync/dwnthing.sh extensions/ConfirmEdit https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmEdit/zip/a9b47a6b2bb81a2f863a1a9d05851dbd419019b2 & \
./wbstack/sync/dwnthing.sh extensions/ConfirmAccount https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmAccount/zip/cde8cece830eaeebf66d0d96dc09a206683435c7 & \
./wbstack/sync/dwnthing.sh extensions/Nuke https://codeload.github.com/wikimedia/mediawiki-extensions-Nuke/zip/41d816e4661aa6d4f49b4604fcfb2c111995df30 & \
./wbstack/sync/dwnthing.sh extensions/InviteSignup https://codeload.github.com/wikimedia/mediawiki-extensions-InviteSignup/zip/b36a3aa5e0660d3ba4f2c893e666f100de145924 & \
./wbstack/sync/dwnthing.sh extensions/TorBlock https://codeload.github.com/wikimedia/mediawiki-extensions-TorBlock/zip/9344f53d83475120010c4da35a8dff55246e8df2 & \
./wbstack/sync/dwnthing.sh extensions/Elastica https://codeload.github.com/wikimedia/mediawiki-extensions-Elastica/zip/8af6b458adf628a98af4ba8e407f9c676bf4a4fb & \
./wbstack/sync/dwnthing.sh extensions/CirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-CirrusSearch/zip/203237ef2828c46094c5f6ba26baaeff2ab3596b & \
./wbstack/sync/dwnthing.sh extensions/WikibaseCirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseCirrusSearch/zip/4bbdfdc55cd19c7e171445cf0bb80ed744975490 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseLexemeCirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseLexemeCirrusSearch/zip/d5a8b8636ba5f5841491248852c9d3d0f0d88319 & \
./wbstack/sync/dwnthing.sh extensions/UniversalLanguageSelector https://codeload.github.com/wikimedia/mediawiki-extensions-UniversalLanguageSelector/zip/e7ab607dd91b55f15a733bcba793619cf48d3604 & \
./wbstack/sync/dwnthing.sh extensions/cldr https://codeload.github.com/wikimedia/mediawiki-extensions-cldr/zip/69363ef473877eb454c9b1186e53264b48806143 & \
./wbstack/sync/dwnthing.sh extensions/Gadgets https://codeload.github.com/wikimedia/mediawiki-extensions-Gadgets/zip/8f6007d7a52871cf09a5161fc5062fdaa69d58c9 & \
./wbstack/sync/dwnthing.sh extensions/Thanks https://codeload.github.com/wikimedia/mediawiki-extensions-Thanks/zip/e28a16d38b5a4c0d32f2388aa4fcc93ec48e7b02 & \
./wbstack/sync/dwnthing.sh extensions/TwoColConflict https://codeload.github.com/wikimedia/mediawiki-extensions-TwoColConflict/zip/83158c42ddf84cc9a6b0d4727610d01105ba5903 & \
./wbstack/sync/dwnthing.sh extensions/OAuth https://codeload.github.com/wikimedia/mediawiki-extensions-OAuth/zip/671c495b33f70d99b69794ea13ca83da3e24080c & \
./wbstack/sync/dwnthing.sh extensions/WikibaseLexeme https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseLexeme/zip/8b486241790f32a8e3d4f04abd354b54617d5464 & \
./wbstack/sync/dwnthing.sh extensions/SyntaxHighlight_GeSHi https://codeload.github.com/wikimedia/mediawiki-extensions-SyntaxHighlight_GeSHi/zip/33d7b70f1a7e1ba37b24312b2daeec9e16c9d0b6 & \
./wbstack/sync/dwnthing.sh extensions/JsonConfig https://codeload.github.com/wikimedia/mediawiki-extensions-JsonConfig/zip/06805de4295ea1cc1c86c6f1f177ca4fa6c81ecb & \
./wbstack/sync/dwnthing.sh extensions/Kartographer https://codeload.github.com/wikimedia/mediawiki-extensions-Kartographer/zip/f6669d293d4c12dfec5d194f7c4d504e6bf0617b & \
./wbstack/sync/dwnthing.sh extensions/Math https://codeload.github.com/wikimedia/mediawiki-extensions-Math/zip/ce438004cb7366860d3bff1f60839ef3c304aa1e & \
./wbstack/sync/dwnthing.sh extensions/Score https://codeload.github.com/wikimedia/mediawiki-extensions-Score/zip/52eac92b27f5bd8f3874f7230333b77098a7b711 & \
./wbstack/sync/dwnthing.sh extensions/PageImages https://codeload.github.com/wikimedia/mediawiki-extensions-PageImages/zip/ee6dbda8ce6ec32f9f1a346153281232ee04b3c4 & \
./wbstack/sync/dwnthing.sh extensions/Scribunto https://codeload.github.com/wikimedia/mediawiki-extensions-Scribunto/zip/8119592874a1cecc7a06e4abb10b2e02d5702530 & \
./wbstack/sync/dwnthing.sh extensions/Cite https://codeload.github.com/wikimedia/mediawiki-extensions-Cite/zip/edba6489df7a389ce2660c524684ccfdb04ca303 & \
./wbstack/sync/dwnthing.sh extensions/TemplateSandbox https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateSandbox/zip/cc5c02f70aa90478e2dad432d10250e5370bd007 & \
./wbstack/sync/dwnthing.sh extensions/CodeEditor https://codeload.github.com/wikimedia/mediawiki-extensions-CodeEditor/zip/e5e6e9219e0ca05012b8fba874f984df819a88f6 & \
./wbstack/sync/dwnthing.sh extensions/CodeMirror https://codeload.github.com/wikimedia/mediawiki-extensions-CodeMirror/zip/84846ec71fb3be844771025ddd9c039da3cc1616 & \
./wbstack/sync/dwnthing.sh extensions/WikiEditor https://codeload.github.com/wikimedia/mediawiki-extensions-WikiEditor/zip/aeab19d82c0abf815180ba2b801ed71ab26c35a6 & \
./wbstack/sync/dwnthing.sh extensions/SecureLinkFixer https://codeload.github.com/wikimedia/mediawiki-extensions-SecureLinkFixer/zip/e03db2af1ab3a05cc4d22c554819f19c174eb773 & \
./wbstack/sync/dwnthing.sh extensions/Echo https://codeload.github.com/wikimedia/mediawiki-extensions-Echo/zip/a3dedc0d64380d74d2e153aad9a8d54cee1b85bd & \
./wbstack/sync/dwnthing.sh extensions/Graph https://codeload.github.com/wikimedia/mediawiki-extensions-Graph/zip/ae2cc41b751a9763792ae861fa3699b9217c5ef9 & \
./wbstack/sync/dwnthing.sh extensions/Poem https://codeload.github.com/wikimedia/mediawiki-extensions-Poem/zip/6d76cddd67585f8bc0002945700c1d7e0b2a55e0 & \
./wbstack/sync/dwnthing.sh extensions/TemplateData https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateData/zip/6e64a367c1df94d623de99f11a449fcaed0bb6d7 & \
./wbstack/sync/dwnthing.sh extensions/AdvancedSearch https://codeload.github.com/wikimedia/mediawiki-extensions-AdvancedSearch/zip/d1895707f3750a6d4a486b425ac9a727707f27f9 & \
./wbstack/sync/dwnthing.sh extensions/ParserFunctions https://codeload.github.com/wikimedia/mediawiki-extensions-ParserFunctions/zip/bb4c9d24de95d0020ace77576c08b55599f7598d & \
./wbstack/sync/dwnthing.sh extensions/MobileFrontend https://codeload.github.com/wikimedia/mediawiki-extensions-MobileFrontend/zip/db7c7843189a9009dde59503e3e3d4cbcab8eaef & \
./wbstack/sync/dwnthing.sh extensions/DeleteBatch https://codeload.github.com/wikimedia/mediawiki-extensions-DeleteBatch/zip/cd12275a4de3e26a26d50425765af2baf474c287 & \
./wbstack/sync/dwnthing.sh extensions/MultimediaViewer https://codeload.github.com/wikimedia/mediawiki-extensions-MultimediaViewer/zip/f0de2f8a4177142eaf79b57672971b74cdce49df & \
./wbstack/sync/dwnthing.sh extensions/Auth_remoteuser https://codeload.github.com/wikimedia/mediawiki-extensions-Auth_remoteuser/zip/a448e281bf94cbb712f8ed45db9f99372959bae2 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseManifest https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseManifest/zip/9cdd036084355aaef993610006bb61985a601b84 & \
./wbstack/sync/dwnthing.sh extensions/WikiHiero https://codeload.github.com/wikimedia/mediawiki-extensions-WikiHiero/zip/9f5d7b42cf56fa6e3abbcfb1aa695504b4d7564e & \
./wbstack/sync/dwnthing.sh extensions/TextExtracts https://codeload.github.com/wikimedia/mediawiki-extensions-TextExtracts/zip/b97d85901027a9861ce614ac5864db2c9acae028 & \
./wbstack/sync/dwnthing.sh extensions/Popups https://codeload.github.com/wikimedia/mediawiki-extensions-Popups/zip/dccd60752353eac1063a79f81a8059b3b06b9353 & \


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
./wbstack/sync/dwnthing.sh skins/Timeless https://codeload.github.com/wikimedia/mediawiki-skins-Timeless/zip/fe0cdbda90039907d4cf12272969873e1d1b4794 & \
./wbstack/sync/dwnthing.sh skins/Modern https://codeload.github.com/wikimedia/mediawiki-skins-Modern/zip/d0a04c91132105f712df4de44a99d3643e7afbba & \
./wbstack/sync/dwnthing.sh skins/MinervaNeue https://codeload.github.com/wikimedia/mediawiki-skins-MinervaNeue/zip/6c99418af845a7761c246ee5a50fbb82715f4003 & \

# And wait for all the background tasks to be done...
wait
