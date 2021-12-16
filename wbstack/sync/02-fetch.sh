#!/usr/bin/env sh
# This script very simply and quickly downloads core, extensions and skins for the deployment.
# Nothing is faster :)

###########
# core
###########
./wbstack/sync/dwnthing.sh core https://codeload.github.com/wikimedia/mediawiki/zip/e1239b16705b192252f795698629d62ed9b2b93a

###########
# extensions
###########
./wbstack/sync/dwnthing.sh extensions/RevisionSlider https://codeload.github.com/wikimedia/mediawiki-extensions-RevisionSlider/zip/b5b3a68a8c69ce261b70c177e4983806c1173eff & \
./wbstack/sync/dwnthing.sh extensions/Mailgun https://codeload.github.com/wikimedia/mediawiki-extensions-Mailgun/zip/37108dab96582a730df9e3a2fafaeb5e69120bd4 & \
./wbstack/sync/dwnthing.sh extensions/StopForumSpam https://codeload.github.com/wikimedia/mediawiki-extensions-StopForumSpam/zip/3dff0ffc72ae784d9e3521be2347fb4f4af827b4 & \
./wbstack/sync/dwnthing.sh extensions/SpamBlacklist https://codeload.github.com/wikimedia/mediawiki-extensions-SpamBlacklist/zip/bed58ae25964a02089406db10939c83dda5cdb02 & \
./wbstack/sync/dwnthing.sh extensions/ConfirmEdit https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmEdit/zip/e9c0201b4924398c1fc4ac9ed2a28a1cc51da746 & \
./wbstack/sync/dwnthing.sh extensions/ConfirmAccount https://codeload.github.com/wikimedia/mediawiki-extensions-ConfirmAccount/zip/f40450c269ab628223ca705bf94711032cb5252b & \
./wbstack/sync/dwnthing.sh extensions/Nuke https://codeload.github.com/wikimedia/mediawiki-extensions-Nuke/zip/942c5a35bc8aba319b801ace71746245d228ab4d & \
./wbstack/sync/dwnthing.sh extensions/InviteSignup https://codeload.github.com/wikimedia/mediawiki-extensions-InviteSignup/zip/6218d64c4cfb94e372310a9bd6365b9120088435 & \
./wbstack/sync/dwnthing.sh extensions/TorBlock https://codeload.github.com/wikimedia/mediawiki-extensions-TorBlock/zip/97629e4fbbd5036c1f9e82a1c8f52b0c2724c621 & \
./wbstack/sync/dwnthing.sh extensions/Elastica https://codeload.github.com/wikimedia/mediawiki-extensions-Elastica/zip/acbf559ff28d144d34b318473c2b42da1cf9e86b & \
./wbstack/sync/dwnthing.sh extensions/CirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-CirrusSearch/zip/69c9be81a7784a822c6284a3599944b3c5d89eaf & \
./wbstack/sync/dwnthing.sh extensions/WikibaseCirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseCirrusSearch/zip/40d889fdf96f879c1f25e397e3463a1cb9986fc1 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseLexemeCirrusSearch https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseLexemeCirrusSearch/zip/56c1880acf1fc33e249c9a2f582626455d70156f & \
./wbstack/sync/dwnthing.sh extensions/UniversalLanguageSelector https://codeload.github.com/wikimedia/mediawiki-extensions-UniversalLanguageSelector/zip/f3813dab69f5f2d8cfe0313bde108e029979c670 & \
./wbstack/sync/dwnthing.sh extensions/cldr https://codeload.github.com/wikimedia/mediawiki-extensions-cldr/zip/d79493d9cb80977f3cfca6a5960dcd13f79b643b & \
./wbstack/sync/dwnthing.sh extensions/Gadgets https://codeload.github.com/wikimedia/mediawiki-extensions-Gadgets/zip/6df6b800fae8c161fed0ed04d3aeaf6fb999317a & \
./wbstack/sync/dwnthing.sh extensions/Thanks https://codeload.github.com/wikimedia/mediawiki-extensions-Thanks/zip/f15f4305760750b141f2591e5a9a17e973a4cd1c & \
./wbstack/sync/dwnthing.sh extensions/TwoColConflict https://codeload.github.com/wikimedia/mediawiki-extensions-TwoColConflict/zip/9b4e43fb526a8ce368c94346b957475916ed4514 & \
./wbstack/sync/dwnthing.sh extensions/OAuth https://codeload.github.com/wikimedia/mediawiki-extensions-OAuth/zip/0031940800688566ff23c91c4e041733975dbac6 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseLexeme https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseLexeme/zip/db4b024d3fb19a7c29219cc013396b4aca7fd7f5 & \
./wbstack/sync/dwnthing.sh extensions/SyntaxHighlight_GeSHi https://codeload.github.com/wikimedia/mediawiki-extensions-SyntaxHighlight_GeSHi/zip/8181186f932bf1a93d57b663cfd7a6bd9db3059a & \
./wbstack/sync/dwnthing.sh extensions/JsonConfig https://codeload.github.com/wikimedia/mediawiki-extensions-JsonConfig/zip/fd0a3c368849318813ebc44d91275503c3ccbc08 & \
./wbstack/sync/dwnthing.sh extensions/Kartographer https://codeload.github.com/wikimedia/mediawiki-extensions-Kartographer/zip/915734d7775bd765f7e08cefc5c6ee4777fa6e0a & \
./wbstack/sync/dwnthing.sh extensions/Math https://codeload.github.com/wikimedia/mediawiki-extensions-Math/zip/39bf030832e70b5dbb8e1e3ac8f7bc0736a28318 & \
./wbstack/sync/dwnthing.sh extensions/Score https://codeload.github.com/wikimedia/mediawiki-extensions-Score/zip/cd4f72339b54ebca82fd3a33890fbe50c271944b & \
./wbstack/sync/dwnthing.sh extensions/PageImages https://codeload.github.com/wikimedia/mediawiki-extensions-PageImages/zip/130e0ae9eba8d33a3fcaade4fa87f24939528166 & \
./wbstack/sync/dwnthing.sh extensions/Scribunto https://codeload.github.com/wikimedia/mediawiki-extensions-Scribunto/zip/cbfceb5d19f1ff13362545db186fb1bafb36a7aa & \
./wbstack/sync/dwnthing.sh extensions/Cite https://codeload.github.com/wikimedia/mediawiki-extensions-Cite/zip/838e4fda79dbf707c8538045c86250b66965a08d & \
./wbstack/sync/dwnthing.sh extensions/TemplateSandbox https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateSandbox/zip/c6f81c6175c43a17cd5f6e64b3a50e425f1e807f & \
./wbstack/sync/dwnthing.sh extensions/CodeEditor https://codeload.github.com/wikimedia/mediawiki-extensions-CodeEditor/zip/719f578d787be1fce76db8bf0ee104920be0b97e & \
./wbstack/sync/dwnthing.sh extensions/CodeMirror https://codeload.github.com/wikimedia/mediawiki-extensions-CodeMirror/zip/9680a9e2ecd92ae2651985a1e4d06f56c346f4b3 & \
./wbstack/sync/dwnthing.sh extensions/WikiEditor https://codeload.github.com/wikimedia/mediawiki-extensions-WikiEditor/zip/59dceed2ecb70df97be904e56a52b801cb6033cd & \
./wbstack/sync/dwnthing.sh extensions/SecureLinkFixer https://codeload.github.com/wikimedia/mediawiki-extensions-SecureLinkFixer/zip/c351e14c3aa49610998f500855c60b506502b0bb & \
./wbstack/sync/dwnthing.sh extensions/Echo https://codeload.github.com/wikimedia/mediawiki-extensions-Echo/zip/97cc760dd5f1ad9b6547cdcfc537fc778a7b2621 & \
./wbstack/sync/dwnthing.sh extensions/Graph https://codeload.github.com/wikimedia/mediawiki-extensions-Graph/zip/dfca6181babd9200169b36fa6dddaf8bd3197fb4 & \
./wbstack/sync/dwnthing.sh extensions/Poem https://codeload.github.com/wikimedia/mediawiki-extensions-Poem/zip/5cd20a362d227ac2448e9c0395444c272ee70736 & \
./wbstack/sync/dwnthing.sh extensions/TemplateData https://codeload.github.com/wikimedia/mediawiki-extensions-TemplateData/zip/97edad799900f37286be5c2ec3d230699d4011e5 & \
./wbstack/sync/dwnthing.sh extensions/AdvancedSearch https://codeload.github.com/wikimedia/mediawiki-extensions-AdvancedSearch/zip/8c8c37b092d431b91e1ea24f08d948c446c95cb7 & \
./wbstack/sync/dwnthing.sh extensions/ParserFunctions https://codeload.github.com/wikimedia/mediawiki-extensions-ParserFunctions/zip/8a6c06537b0e238681f031621b54e2d64202447a & \
./wbstack/sync/dwnthing.sh extensions/MobileFrontend https://codeload.github.com/wikimedia/mediawiki-extensions-MobileFrontend/zip/ac7c29c55c251a03ffe85de8fb442fe3bf65f839 & \
./wbstack/sync/dwnthing.sh extensions/DeleteBatch https://codeload.github.com/wikimedia/mediawiki-extensions-DeleteBatch/zip/ce8b6df87e96c7100565c63c8056dfeadb33f5c5 & \
./wbstack/sync/dwnthing.sh extensions/MultimediaViewer https://codeload.github.com/wikimedia/mediawiki-extensions-MultimediaViewer/zip/11b7afdfa673b53fc42625a556c7976c3f58b3f6 & \
./wbstack/sync/dwnthing.sh extensions/Auth_remoteuser https://codeload.github.com/wikimedia/mediawiki-extensions-Auth_remoteuser/zip/267291a73535032710ebfd4504f0cb6e0538b135 & \
./wbstack/sync/dwnthing.sh extensions/WikibaseManifest https://codeload.github.com/wikimedia/mediawiki-extensions-WikibaseManifest/zip/e47f11b91597a9f4599cb2dfcc9bdef94a9f0a13 & \
./wbstack/sync/dwnthing.sh extensions/WikiHiero https://codeload.github.com/wikimedia/mediawiki-extensions-WikiHiero/zip/4297f947cd8564637948b955f284dc7b63c1d75d & \
./wbstack/sync/dwnthing.sh extensions/TextExtracts https://codeload.github.com/wikimedia/mediawiki-extensions-TextExtracts/zip/4bf31cf343542ed827237babd5c0709c14d5cb11 & \
./wbstack/sync/dwnthing.sh extensions/Popups https://codeload.github.com/wikimedia/mediawiki-extensions-Popups/zip/7cfb5a5e13f94e669616ce704bb0ca9b3bcb0936 & \
./wbstack/sync/dwnthing.sh extensions/EntitySchema https://codeload.github.com/wikimedia/mediawiki-extensions-EntitySchema/zip/74c4040f1903c2bbc5e059367e806a134d172e75 & \

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

###########
# skins
###########
./wbstack/sync/dwnthing.sh skins/Vector https://codeload.github.com/wikimedia/Vector/zip/a2721bf9c3772b45bb13520755034edda52a3e4e & \
./wbstack/sync/dwnthing.sh skins/Timeless https://codeload.github.com/wikimedia/mediawiki-skins-Timeless/zip/90e079694efbd34bd5b7725823583238fb546eea & \
./wbstack/sync/dwnthing.sh skins/Modern https://codeload.github.com/wikimedia/mediawiki-skins-Modern/zip/57c685aa4d6ebd786ce85fdb97aba5850961931e & \
./wbstack/sync/dwnthing.sh skins/MinervaNeue https://codeload.github.com/wikimedia/mediawiki-skins-MinervaNeue/zip/92533535bfaba4e0a3f06eee39f375e2259c6f90 & \

# And wait for all the background tasks to be done...
wait
