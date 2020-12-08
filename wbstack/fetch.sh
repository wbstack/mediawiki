#!/usr/bin/env sh
# This script very simply and quickly downloads core, extensions and skins for the deployment.
# Nothing is faster :)

###########
# core
###########
./wbstack/dwnthing.sh core https://codeload.github.com/wikimedia/mediawiki/zip/227ed74bdb7c364e42194dcdba2bd7c20bd69c9a

###########
# extensions
###########
./wbstack/dwnthing.sh extensions/RevisionSlider https://codeload.github.com/wikimedia/mediawiki-extensions-RevisionSlider/zip/218ce8fb59f75d14790f68a0e2b4b4195a12e98c & \
./wbstack/dwnthing.sh extensions/Mailgun https://codeload.github.com/wikimedia/mediawiki-extensions-Mailgun/zip/7b0fe10c1639ec90eb056eb50e6d015ed5607fb7 & \
./wbstack/dwnthing.sh extensions/ConfirmEdit https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmEdit/zip/809bc690658989210568028495a79c9b242a8636 & \
./wbstack/dwnthing.sh extensions/ConfirmAccount https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmAccount/zip/9b071fdf6dd90858c4a4419b39ec0650693632fc & \
./wbstack/dwnthing.sh extensions/Nuke https://codeload.github.com/wikimedia/mediawiki-extensions-Nuke/zip/3de3b0f8889a10a824c205e6258c63da3afd2268 & \
./wbstack/dwnthing.sh extensions/InviteSignup https://codeload.github.com/wikimedia/mediawiki-extensions-InviteSignup/zip/d84690985bb15ef01b0b61d9e32a2ea79d63d15e & \
./wbstack/dwnthing.sh extensions/TorBlock https://codeload.github.com/wikimedia/mediawiki-extensions-TorBlock/zip/5adf92b21e33cb7f15f05e6b21e24ae3a4910d4f & \
./wbstack/dwnthing.sh extensions/Elastica https://codeload.github.com/wikimedia/mediawiki-extensions-Elastica/zip/3e3b76f3b7208167342fee843c401f2587dacde3 & \
./wbstack/dwnthing.sh extensions/CirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-CirrusSearch/zip/f8358500d2136bd03819b48e55dd7da346850eca & \
./wbstack/dwnthing.sh extensions/UniversalLanguageSelector https://codeload.github.com/wikimedia/mediawiki-extensions-UniversalLanguageSelector/zip/d52008b96107b72ee52da9cbc44dd5ba2e43b4aa & \
./wbstack/dwnthing.sh extensions/cldr https://codeload.github.com/wikimedia/mediawiki-extensions-cldr/zip/fec3e2945455099866f79b314e58f9854dde30e6 & \
./wbstack/dwnthing.sh extensions/Gadgets https://codeload.github.com/wikimedia/mediawiki-extensions-Gadgets/zip/1e54c8014828168d297795d515c12ef060f4f849 & \
./wbstack/dwnthing.sh extensions/Thanks https://codeload.github.com/wikimedia/mediawiki-extensions-Thanks/zip/d307eba7201b9a03121585f38664973a8e7c96d2 & \
./wbstack/dwnthing.sh extensions/TwoColConflict https://codeload.github.com/wikimedia/mediawiki-extensions-TwoColConflict/zip/d21e1adea79c9f0ac24a1bd02d165d3ec3dd57d8 & \
./wbstack/dwnthing.sh extensions/OAuth https://codeload.github.com/wikimedia/mediawiki-extensions-OAuth/zip/5d974d0b1b19228f3fc902f2aa48c1ec35a74b98 & \
./wbstack/dwnthing.sh extensions/WikibaseLexeme https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseLexeme/zip/db5f4b11502c3149d37c399712c3467cf240648b & \
./wbstack/dwnthing.sh extensions/SyntaxHighlight_GeSHi https://codeload.github.com/wikimedia/mediawiki-extensions-SyntaxHighlight_GeSHi/zip/6bd7a93f3f0ebc64de1a475bf97aff4397e4d05b & \
./wbstack/dwnthing.sh extensions/JsonConfig https://codeload.github.com/wikimedia/mediawiki-extensions-JsonConfig/zip/a0bdbfbf7bccba99834a384224b91ed52eb459d6 & \
./wbstack/dwnthing.sh extensions/Kartographer https://codeload.github.com/wikimedia/mediawiki-extensions-Kartographer/zip/1ed9c132d80d8f6340e2cf21b102b659aaeb5c90 & \
./wbstack/dwnthing.sh extensions/Math https://codeload.github.com/wikimedia/mediawiki-extensions-Math/zip/b5a8f57b98cf2eecdf9cf241050d1acb6af8749e & \
./wbstack/dwnthing.sh extensions/Score https://codeload.github.com/wikimedia/mediawiki-extensions-Score/zip/00a1762249c2c5073d34407fac1f7fe48eb32648 & \
./wbstack/dwnthing.sh extensions/PageImages https://codeload.github.com/wikimedia/mediawiki-extensions-PageImages/zip/9a2f95e712ccfbe255b7cb192ca6a597cd08d117 & \
./wbstack/dwnthing.sh extensions/Scribunto https://codeload.github.com/wikimedia/mediawiki-extensions-Scribunto/zip/e44ec268924acd06d352e3dc5593f12c3314c4fd & \
./wbstack/dwnthing.sh extensions/Cite https://codeload.github.com/wikimedia/mediawiki-extensions-Cite/zip/98efd80db7542d33130fc938a5cadfa4f6726f29 & \
./wbstack/dwnthing.sh extensions/TemplateSandbox https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateSandbox/zip/d574cbb38a5d56ed18ecf7f0f79d19a53e5b610d & \
./wbstack/dwnthing.sh extensions/CodeEditor https://codeload.github.com/wikimedia/mediawiki-extensions-CodeEditor/zip/46773ebef34370988cff3acab5729397d2e4b63c & \
./wbstack/dwnthing.sh extensions/WikiEditor https://codeload.github.com/wikimedia/mediawiki-extensions-WikiEditor/zip/a46cebd83784abf45c00b41fa9d4155a2c7c06d1 & \
./wbstack/dwnthing.sh extensions/SecureLinkFixer https://codeload.github.com/wikimedia/mediawiki-extensions-SecureLinkFixer/zip/47f12670b23b5a59e7fdd1a2bd9b5a49693940c8 & \
./wbstack/dwnthing.sh extensions/Echo https://codeload.github.com/wikimedia/mediawiki-extensions-Echo/zip/3c62654c2f0a088cd3b6366aedd746875ee33662 & \
./wbstack/dwnthing.sh extensions/Graph https://codeload.github.com/wikimedia/mediawiki-extensions-Graph/zip/fa2b9af55252c60ce5f2aa8f027e539fdcabc9a5 & \
./wbstack/dwnthing.sh extensions/Poem https://codeload.github.com/wikimedia/mediawiki-extensions-Poem/zip/447d8b7a3a16dbb4ad820d743dc685a4ec70bf16 & \
./wbstack/dwnthing.sh extensions/TemplateData https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateData/zip/5225cf4da7812e45865ae387e758250e91c5e483 & \
./wbstack/dwnthing.sh extensions/AdvancedSearch https://codeload.github.com/wikimedia/mediawiki-extensions-AdvancedSearch/zip/0a0e386b5ca9cc962218299e791158340965aae6 & \
./wbstack/dwnthing.sh extensions/ParserFunctions https://codeload.github.com/wikimedia/mediawiki-extensions-ParserFunctions/zip/59a91354754ac78b1f49de1cf7e5dfca204e6ce7 & \
./wbstack/dwnthing.sh extensions/MobileFrontend https://codeload.github.com/wikimedia/mediawiki-extensions-MobileFrontend/zip/1421405c1fe4f3dbafe0cd18f43aa9054b974c4e & \
./wbstack/dwnthing.sh extensions/DeleteBatch https://codeload.github.com/wikimedia/mediawiki-extensions-DeleteBatch/zip/3650f3c94f42b77e099907102fb7e0f456af5ff2 & \
./wbstack/dwnthing.sh extensions/MultimediaViewer https://codeload.github.com/wikimedia/mediawiki-extensions-MultimediaViewer/zip/bad41902b05f475d7b17a20d2dc18224a633f509 & \

# Automatic script skips this one
./wbstack/dwnthing.sh extensions/EntitySchema https://codeload.github.com/wikimedia/mediawiki-extensions-EntitySchema/zip/64831e3f9a4e8d33af017cbf22876ac3dd89466a & \

# Extension Distributor
./wbstack/dwnthing.sh extensions/Wikibase https://extdist.wmflabs.org/dist/extensions/Wikibase-REL1_35-ea86f45.tar.gz & \

# Custom wbstack
./wbstack/dwnthing.sh extensions/WikibaseInWikitext https://codeload.github.com/wbstack/mediawiki-extensions-WikibaseInWikitext/zip/445c7efaa145fa7c31b0caca7400ef6a87cac7d9 & \

# Elsewhere
./wbstack/dwnthing.sh extensions/EmbedVideo https://gitlab.com/hydrawiki/extensions/EmbedVideo/-/archive/v2.8.0/EmbedVideo-v2.8.0.zip & \

###########
# skins
###########
./wbstack/dwnthing.sh skins/Vector https://codeload.github.com/wikimedia/mediawiki-skins-Vector/zip/771c8764c856a2b9ed2a8ff84ad5cfd38cdfaf5e & \
./wbstack/dwnthing.sh skins/Timeless https://codeload.github.com/wikimedia/mediawiki-skins-Timeless/zip/e8f1e5aba16e8f7c922aaaf3a263c53474703b20 & \
./wbstack/dwnthing.sh skins/Modern https://codeload.github.com/wikimedia/mediawiki-skins-Modern/zip/ca6e5009ea8ffe5db39a642fdddfbc4fe6d6c7d5 & \
./wbstack/dwnthing.sh skins/MinervaNeue https://codeload.github.com/wikimedia/mediawiki-skins-MinervaNeue/zip/2e2378bf8ff0ecb977f4e7d200f6840982c40f74 & \

# And wait for all the background tasks to be done...
wait