# mediawiki

Tags have the format: `<MediaWiki core version>-<PHP Version>-<date>-<build number>`

## 1.39-7.4-20241112-0
- Fix lexeme data transclusion returns 'nil' (#456)

## 1.39-7.4-20241107-0
- Remove Graph extension (#453)

## 1.39-7.4-20241101-0
- Include rewrite rules present in the WMF config for wikidata

## 1.39-7.4-20240722-0
- Add `ownerOnly` parameter to OAuth setup (#447)

## 1.39-7.4-20240624-0
- Enable InstantCommons (#444)

## 1.39-7.4-20240618-0
- Add link pointing to status.wikibase.cloud to error pages

## 1.39-7.4-20240426-0
- update mediawiki to 1.39.7
  - actually a bit beyond that: wikiman.yaml refers to the latest REL1_39 branch state now
- switch Wikibase source to wmf extdist

## 1.39-7.4-20240424-0
- Specify a cluster when creating CirrusSearch indices (#437)

## 1.39-7.4-20240411-0
- Add default Main page content to new wikis

## 1.39-7.4-20240320-0
- Add flag to enable CirrusSearch prefixing for ids (#434)
- Add ability to override CirrusSearch's base index name (#433)
- Move Elasticsearch aliasing out of CirrusSearch (#432)

## 1.39-7.4-20240315-0
- Support sharing Elasticsearch indices across multiple wikis (#430)

## 1.39-7.4-20240226-0
- Configure Q1 KELOD research banner #423
- Add extension DismissableSiteNotice #427
- Sync resources via pacman #426

## 1.39-7.4-20240207-0
- Update MediaWiki to 1.39.6 (#417)

## 1.39-7.4-20240202-0
- Structure logs for Google Cloud Error Reporting (#416)
- Drop migration patch for Echo (#373)

## 1.39-7.4-20240125-0
- Load conflicting extensions localisation at cache build time (T354953)

## 1.39-7.4-20240116-0
- On failure, propagate status code from Platform API to clients (T343744)

## 1.39-7.4-20240103-0
- Add QuestyCaptcha config option

## 1.39-7.4-20231213-0
- Set $wgConfirmAccountCaptchas true for ConfirmAccount Wikis

## 1.39-7.4-20231113-0
- Do not allow more than 1 replica per Elasticsearch index (#402)

## 1.39-7.4-20231031-0
- Update Wikibase and EntitySchema to 1.39.5 (#399)

## 1.39-7.4-20231004-0
- Security update for mediawiki to v1.39.5 (#397)
- Tiny refactoring of the pacman script (#396)

## 1.39-7.4-20230825-0
- Configure Xdebug to be compatible with VSCode
- Find entities using term search when Elasticsearch is down
- Use a PHP 7.4 compatible version of Xdebug
- Switch to ParamValidator::PARAM_TYPE instead of deprecated ApiBase::PARAM_TYPE

## 1.39-7.4-20230801-0
- CirrusSearch: run init maint script against all clusters

## 1.39-7.4-20230731-0
- Show a reduced Wikibase copyright notice
- Reuse Wikibase's copyright message builder in EntitySchema

## 1.39-7.4-20230725-0
- Update links in Wikibase item and property creation summaries

## 1.39-7.4-20230719-0
- Add configuration for two ElasticSearch clusters

## 1.39-7.4-20230717-0
- Remove `Popups` extension

## 1.39-7.4-20230524-0
- Add condition for skipping Db replica config in case replica hostname is not set (empty string)

## 1.39-7.4-20230522-0
- Allow email-confirmed users to add external urls (T327752)

## 1.39-7.4-20230426-0
- Re-introduce the fallback to default Search when ElasticSearch is broken (T334191)
- Replace usage of PersonalUrlsHook with SkinTemplateNavigation (T334846)

## 1.39-7.4-20230411-1
- Update to MediaWiki 1.39.3

## 1.39-7.4-20230411-0
- Fix OAuth for QuickStatements

## 1.39-7.4-20230405-0
- Temporary fix to resolve Echo's migration errors

## 1.39-7.4-20230328-0
- Update to MediaWiki 1.39

## 1.38-7.4-20230323-0
- Set the timeout for MediaWiki updates to 1hr

## 1.38-7.4-20230321-0
- Add missing Wikibase submodules

## 1.38-7.4-20230316-0
- Update to MediaWiki 1.38

## 1.37-7.4-20230112-fp-beta-0
- Restrict createaccount permission to bureaucrats

## 1.37-7.4-20220930-fp-beta-0
- Add option to add mediawiki patches with pacman
- Add maintenance script for rebuilding quantity units

## 1.37-7.4-20220818-fp-beta-0

- Update extensions and skins to include latest REL1_37 changes
## 1.37-7.4-20220812-fp-beta-0
- Add ApiWbStackSiteStatsUpdate

## 1.37-7.4-20220621-fp-beta-0
- MW_ALLOWED_PROXY_CIDR added and read into $wgCdnServersNoPurge.

## 1.37-7.4-20220603-fp-beta-0
- Disables creation of CirrusSearch archive indices.

## 1.37-7.4-20220512-fp-beta-0

- Update CirrusSearch sharding configuration. This action requires re-indexing of ElasticSearch.
## 1.37-7.4-20220429-fp-beta-0

- Catch fatal error output from ForceSearchIndex job
## 1.37-7.4-20220304-fp-beta-0

- Hardcode $localConceptBaseUri to HTTPS
- Use escapeshellarg in WbStackForceSearchIndex
- Some changes to sync.sh
- dependabot updates to github actions
## 1.37-7.4-20220131-fp-beta-0

- Don't set $wgReadOnly in CLIs

## 1.37-7.4-20220126-fp-beta-0

- Add internal api modules for updating ElasticSearch

## 1.37-7.4-20220118-fp-beta-0
- Set $wgReadOnly from the api

## 1.37-7.4-20220105-fp-beta-0

- Update extensions and skins to include latest REL1_37 changes

## 1.37-7.4-20211203-fp-beta-1

- Conditionally set lexeme in entity source

## 1.37-7.4-20211203-fp-beta-0

- First 1.37 fed props build

## 1.36-7.4-20211109-0

- Fix (conditional) SMTP configuration auth boolean setting

## 1.36-7.4-20211108-1

## 1.36-7.4-20211108-0

- Update deprecated usages of `UserFactory` in wbstack code
- Update deprecated usages of `AuthManager` in wbstack code
- Add (conditional) SMTP configuration

## 1.36-7.4-20211104-0

- Move internal update API outside of MediaWiki

## 1.36-7.4-20211103-0

- Update StopForumSpam extension (bug fix)
- Don't ignore composer platform reqs

## 1.36-7.4-20211029-0

- Enable replication locally
- Fix deprecration warnings from EmbedVideo
- Bump memory_limit to 256M in Dockerfile
- Update deprecated hook PageContentSaveComplete

## 1.36-7.4-20211028-0

- First 1.36 code release (at this point untested)

## 1.35-7.4-20211022-0-fix1

- Enable replication locally

## 1.35-7.4-20211022-0

- Add `MW_LOG_TO_STDERR` which can be set for local development
- Remove port from localhost sites (gets in the way of mwde-wbaas-deploy dev env)

## 1.35-7.4-20211015-0

- Mailgun code updates
- LocalSettings: `$wgMailgunEndpoint = getenv('MW_MAILGUN_ENDPOINT');`

## 1.35-7.4-20211221-0-bp1

- Set $wgReadOnly from the api

## 1.35-7.4-20211221-0

- MediaWiki 1.35.5

## 1.35-7.4-20211013-0

- Updates for 1.35 releases

## 1.35-7.4-20210902-0

- [Use dbname in elasticsearch indexes](https://github.com/wbstack/mediawiki/pull/128)

## 1.35-7.4-20210824-0

- [Configure elasticsearch for WikibaseLexeme](https://github.com/wbstack/mediawiki/pull/121)

## 1.35-7.4-20210818-1

- Stop using `uceprotect` in DNSBL
- Add `.` to end of DNSBL entries missing it

## 1.35-7.4-20210818-0

- Log useful `BlockManager` logs

## 1.35-7.4-20210817-0

- Remove `dnsbl.spfbl.net` as it is causing issues

## 1.35-7.4-20210803-2

- `$wgEnableDnsBlacklist = true;`
- Configure `wgDnsBlacklistUrls`
- Add extra backend user create login logging

## 1.35-7.4-20210802-1

- Add `StopForumSpam` MediaWiki extension

## 1.35-7.4-20210802-0

- Add `SpamBlacklist` MediaWiki extension
- Use wbstack/mediawiki-spam-lists

## 1.35-7.4-20210715-0

- Load WikibaseCirrusSearch and WikibaseLexemeCirrusSearch code
- Add `MW_ELASTICSEARCH_HOST` and `MW_ELASTICSEARCH_PORT` environment variables for when elastic search is used
- Add `wwExtEnableElasticSearch` setting for when to enable elastic search
- Update composer dependencies
- Lots of dev environment changes (including touching production files)
- [Add Special:ListProperties to sidebar](https://github.com/wbstack/mediawiki/commit/2b21416c30c6820e645e33478d796f36493bdb66)

## 1.35-7.4-20210624-0

- [Re add some development environment stuff](https://github.com/wbstack/mediawiki/commit/97482213fb4d04b1b4c9215348ccba420fbcde85)
- [Maybe fix OAuth consumer creation etc](https://github.com/wbstack/mediawiki/commit/903182b91c8410d695404e83efe5f168ac7332c8)

## 1.35-7.4-20210623-0

- [Update MediaWiki & Main Skins / Extensions 1.35 branches](https://github.com/wbstack/mediawiki/pull/90)
- [Update WikibaseEdtf to 1.2.0](https://github.com/wbstack/mediawiki/pull/91)

## 1.35-7.4-20210506-0

- Use Wikibase 1.35 wmde.1 release

## 1.35-7.4-20210426-0

- REL1_35 updates
- Add wbstack shims to missing MediaWiki entry points
- Load & Enable `WikibaseEdtf`, `TextExtracts`, `Popups` for all sites
- Conditionally load `nyurik/ThatSrc`

## 1.35-7.4-20210409-0

- Multiple 1.35 branch updates

## 1.35-7.4-20210402-0

- [Set wgLexemeLanguageCodePropertyId from equiv entities](https://github.com/wbstack/mediawiki/pull/55)
- [$wgLexemeEnableDataTransclusion = true](https://github.com/wbstack/mediawiki/pull/56)

## 1.35-7.4-20210326-1

- `$wgContentNamespaces[] = 120;` - Requires running `updateArticleCount.php` on all wikis to complete.
- Enable MediaWiki Extension `WikiHiero`
- Enable MediaWiki Extension `CodeMirror`

## 1.35-7.4-20210326-0

- Big internal setting & build refactorings

## 1.35-7.4-20210325-4

- Fix `conceptBaseUri` to be http again... again...

## 1.35-7.4-20210325-1

- Fix for `$wgWBRepoSettings['formatterUrlProperty']` and `$wgWBRepoSettings['canonicalUriProperty']` (use `properties` key)

## 1.35-7.4-20210325-0

- REL1_35 updates for MediaWiki, skins and extensions
- Actually include wmde/php-vuejs-templating for WikibaseLexeme
- Fix exception shown when wikis don't exist or are deleted
- Enable WikibaseManifest by default on all wikis
- Fix protocol in $wgServer MediaWiki variable
- Add `wikibaseFedPropsEnable` setting for toggling federated properties
- Add `wikibaseManifestEquivEntities` setting for WikibaseManifest
- Set `$wgWBRepoSettings['formatterUrlProperty']` and `$wgWBRepoSettings['canonicalUriProperty']` based on `wikibaseManifestEquivEntities`

## 1.35-7.4-20210322-2

- Load WikibaseManifest in maint scripts
- Change version for one with fix :) (not master)

## 1.35-7.4-20210322-1

- Add WikibaseManifest

## 1.35-7.4-20210322-0

- Update Wikibase to have more backports related to Federated Properties

## 1.35-7.4-20210210-0

- [Wikibase Federated properties](https://github.com/wbstack/mediawiki/pull/4)
  - Code briefly tested in production before merging this PR
  - Working from the branch at https://github.com/addshore/Wikibase/pull/1 which has a backport for 1.35
  - Includes a config setting to enable the functionality (not tested)

## 1.35-7.4-20210131-0

- [1.35 Updates (part 2)](https://github.com/wbstack/mediawiki/commit/0c4ba980622208fd5f1509b07ff47d01f4b6576a)

## 1.35-7.4-20210130-0

- [PHP 7.4](https://github.com/wbstack/mediawiki/commit/1f377cd59cc25fac162dc23c1532a63df8bf492c)
  - [And needed libonig-dev](https://github.com/wbstack/mediawiki/commit/75632cba8af8b6438f66d9d403bb2e598e80522a)
- [1.35 Updates](https://github.com/wbstack/mediawiki/commit/cdf00150b4cebdbffa29828f3193110b3da6aa75)

## 1.35-7.3-20201211-0

- [Enable sandboxes to be loaded with some preset data](https://github.com/wbstack/mediawiki/commit/fe67991c8036dd41023baf0226407c8f33c446a2)

## 1.35-7.3-20201210-0

- Allow sandbox mode users to actually edit their wikis
  - [pt1](https://github.com/wbstack/mediawiki/commit/7d053f76de25a1f0dd591cd41fb53a8a5e74b0b3)
  - [pt2](https://github.com/wbstack/mediawiki/commit/88c626b66a4a50916a49759df5da4fa361864886)

## 1.35-7.3-20201209-0

- Move the InternalSettings.php files around [commit](https://github.com/wbstack/mediawiki/commit/6ac5ead75ddf63ed52a58153a734915d46aa254d)
- Add [Auth_remoteuser extension](https://www.mediawiki.org/wiki/Extension:Auth_remoteuser)
  - [Introduction](https://github.com/wbstack/mediawiki/commit/0a2f6f102d6e0ac2b73eb613e32dc42c95916525)
  - Settings [pt1](https://github.com/wbstack/mediawiki/commit/36d4f121abfe12e14020cbf96881a7078808d33b) [pt2](https://github.com/wbstack/mediawiki/commit/f394a42d4454c9d398751e0c636df24754e9f76e)
- Alter ApiWbStackInit to allow passwords, and make emails optional
  - [pt1](https://github.com/wbstack/mediawiki/commit/1cb230a37b79610a441e28196c11aace7b062f59)
  - [pt2](https://github.com/wbstack/mediawiki/commit/43870909b762c09dfb4a72eff8d1b17c48ecf00f)
  - [pt3](https://github.com/wbstack/mediawiki/commit/c257deb582c431e2bdcf28517a3c6dcfe8f8660f) (Though the deadlock still happened? but the module continued? and worked..)

## 1.35-7.3-20201208-0

- Update some extensions and skins to latest REL1_35 commits [commit](https://github.com/wbstack/mediawiki/commit/926b4a3dcfcfa993a3159bccf5dc09f222081032)
  - OAuth
  - Score
  - MinervaNeue
- Update WikibaseInWikitext [commit](https://github.com/wbstack/mediawiki/commit/773bdc85dc8c813afaab37211a783a95f322ce28)
- Move a bunch of the WBStack custom files around [commit](https://github.com/wbstack/mediawiki/commit/666f55b0ef56ea184313065d04652530858afbd8)

## 1.35-7.3-2.0

- Build moved to Github, but other than that everything remains the same.

## 1.35 (Build on GCE)

### November 2020

- 1.35-7.3-b0.2-c0.1-e0.9 - 135 with hack for term update fix... https://phabricator.wikimedia.org/T268944
- 1.35-7.3-b0.2-c0.1-e0.8 - Make sure 1.35 has https://gerrit.wikimedia.org/r/c/mediawiki/core/+/641776 applied (Thanks Lucas!) and $wgDisableOutputCompression  = true;
- 1.35-7.3-b0.2-c0.1-e0.7 - Make sure 1.35 has https://gerrit.wikimedia.org/r/c/mediawiki/core/+/641776 applied (Thanks Lucas!)
- 1.35-7.3-b0.2-c0.3-e0.6 - $wgDisableOutputCompression  = false; # again
- 1.35-7.3-b0.2-c0.3-e0.5 - 1.35 (with hack https://gerrit.wikimedia.org/r/c/mediawiki/core/+/643550)
- 1.35-7.3-b0.2-c0.1-e0.5 - $wgDisableOutputCompression  = true; https://phabricator.wikimedia.org/T235554 (and other LS.php fixes)
- 1.35-7.3-b0.2-c0.1-e0.4 - Base bump:  with zlib
- 1.35-7.3-b0.1-c0.1-e0.4 - Going for mw 1.35 - Changes for the new version...
- 1.35-7.3-b0.1-c0.1-e0.1 - Going for mw 1.35

## 1.34

### November 2020 (1.34)

- 1.34-7.3-b0.1-c0.1-e0.5 - Going for mw 1.34 - Fix password reset that is needed for mw account creation
- 1.34-7.3-b0.1-c0.1-e0.4 - Going for mw 1.34 - wbstackUpdate internal api module
- 1.34-7.3-b0.1-c0.1-e0.2 - Going for mw 1.34 - Don't add replica config when running offline "maint" scripts

### October 2020

- 1.34-7.3-b0.1-c0.1-e0.1 - Going for mw 1.34

## 1.33

### September 2020

- 1.33-b0.10-c0.13-e0.90 - PHP and MW updater (still 1.33)

### June 2020

- 1.33-b0.9-c0.12-e0.90 - Multilang (term) length options
- 1.33-b0.9-c0.12-e0.89 - PHP Security fixes June 3 2020

### May 2020

- 1.33-b0.8-c0.12-e0.89 - Tweak wikwiki setting cache
- 1.33-b0.8-c0.12-e0.88 - wikwiki settings for wikibase string lengths
- 1.33-b0.8-c0.12-e0.86 - Extensions: InviteSignup and ConfirmAccount
- 1.33-b0.8-c0.12-e0.85 - Use private ips from forward for header
- 1.33-b0.8-c0.12-e0.82 - Use ObjectCache::newFromParams so that redis keyspace is correctly split...
- 1.33-b0.8-c0.12-e0.81 - Use DB cache for parser cache (TODO check other caches (redis) are split per wiki...)
- 1.33-b0.8-c0.12-e0.80 - Use event/pageUpdateBatch for page change events, and do 1 call per request max...
- 1.33-b0.8-c0.12-e0.79 - Permissions: crats edit global css, crats no longer interact with platform group
- 1.33-b0.8-c0.12-e0.77 - PHP health check file
- 1.33-b0.8-c0.12-e0.74 - Custom default mediawiki skin
- 1.33-b0.8-c0.12-e0.73 - redis, read and write connection
- 1.33-b0.8-c0.12-e0.72 - sql, replica 100 load, master 1 (should allow things to keep working when replica is reloading)?
- 1.33-b0.8-c0.12-e0.71 - Link to cradle in sidebar
- 1.33-b0.8-c0.12-e0.70 - remove internal apis for quickstatements
- 1.33-b0.8-c0.12-e0.69 - generic oauth internal api for widar setup

### April 2020

- 1.33-b0.8-c0.12-e0.67 - Move mw assets to gce bucket
- 1.33-b0.8-c0.12-e0.66 - $wgCompressRevisions = true;
- 1.33-b0.8-c0.12-e0.65 - Harsher SQL object cache purging
- 1.33-b0.8-c0.12-e0.64 - Better user agent
- 1.33-b0.8-c0.12-e0.63 - Load custom favicon if it is set
- 1.33-b0.8-c0.12-e0.62 - Load custom logo if it is set
- 1.33-b0.8-c0.12-e0.61 - Add extensions: ParserFunctions, MobileFrontend, DeleteBatch, MultimediaViewer, EmbedVideo & Skin: MinerveNeue
- 1.33-b0.8-c0.11-e0.60 - Poke rewrite rules again & fix powered by image locations after .59?
- 1.33-b0.8-c0.11-e0.59 - less layers
- 1.33-b0.8-c0.11-e0.58 - robots.txt
- 1.33-b0.8-c0.11-e0.56 - Deployed and working /entity redirect.. hufffff...
- 1.33-b0.7-c0.11-e0.49-55 - Various stages of broken
- 1.33-b0.7-c0.11-e0.48 - /entity/ redirect for Wikibase
- 1.33-b0.7-c0.11-e0.47 - ExtraSettings stuff after localization cache build in Dockerfile
- 1.33-b0.7-c0.11-e0.46 - Powered by icons!
- 1.33-b0.7-c0.11-e0.45 - Echo patch for https://github.com/addshore/wbstack/issues/76
- 1.33-b0.7-c0.10-e0.45 - Add and load extensions: Echo, Graph, Poem, TemplateData, AdvancedSearch, Thanks (but actually enable thanks)
- 1.33-b0.7-c0.10-e0.44 - Add and load extensions: Echo, Graph, Poem, TemplateData, AdvancedSearch, Thanks
- 1.33-b0.7-c0.9-e0.43 - Add and load extensions: PageImages, Scribunto, Cite, TemplateSandbox, WikiEditor, CodeEditor, SecureLinkFixer
- 1.33-b0.6-c0.8-e0.42 - $wgUseImageMagick = true;
- 1.33-b0.6-c0.8-e0.41 - $wgShellLocale = "C.UTF-8";
- 1.33-b0.6-c0.8-e0.40 - Enable Score Wikibase data type
- 1.33-b0.6-c0.8-e0.39 - Load JsonConfig, Kartographer, Math, Score
- 1.33-b0.5-c0.8-e0.38 - Add code for various extensions Add JsonConfig, Kartographer, Math, Score extensions (but don't enable) (should have been b0.6)
- 1.33-b0.5-c0.7-e0.37 - PHP 7.2 base
- 1.33-b0.4-c0.7-e0.37 - Rebuild of code after March/April security release (5 April 2020)

### February 2020

- 1.33-b0.4-c0.6-e0.37 - job rate of 2 in requests
- 1.33-b0.4-c0.6-e0.36 - python 3 for syntaxhighlighter...
- 1.33-b0.2-c0.6-e0.36 - WikibaseInWikitext (custom ext)

### January 2020

- 1.33-b0.2-c0.4-e0.35 - Special:NewLexeme in sidebar
- 1.33-b0.2-c0.4-e0.34 - $wgLocalisationCacheConf['manualRecache'] = true;
- 1.33-b0.2-c0.4-e0.33 - Don't enable WikibaseLexeme by default (look for a specific setting)
- 1.33-b0.2-c0.4-e0.32 - Load Wikibase Lexeme Code and enable
- 1.33-b0.2-c0.3-e0.31 - Fix user agents for hooks to platform API
- 1.33-b0.2-c0.3-e0.30 - APCu cache for platform settings.

### December 2019

- 1.33-b0.2-c0.3-e0.28 - A little bit less logging
- 1.33-b0.2-c0.3-e0.27 - Deletions, restores & moves as updated for wdqs updater.. - https://github.com/addshore/wbstack/issues/33
- 1.33-b0.2-c0.3-e0.25 - Turn of redis wikibase entity cache - https://github.com/addshore/wbstack/issues/37
- 1.33-b0.2-c0.3-e0.24 - base includes calendar php extension... - https://github.com/addshore/wbstack/issues/36

### November 2019

- 1.33-c0.3-e0.24 - SyntaxHighlight
- 1.33-c0.2-e0.23 - Project namespace is default meta namespace
- 1.33-c0.2-e0.22 - Special:NewEntitySchema sidebar link
- 1.33-c0.2-e0.21 - Switch to chown of mw files inside of COPY
- 1.33-c0.2-e0.19 - EntitySchema loaded (and other minor ext updates) (19 possibly should have become 20...)?
- 1.33-c0.1-e0.19 - Add Wikibase 1 more settings that spam logs, and tweak logs, and sidebar
- 1.33-c0.1-e0.18 - Add Wikibase settings that spam logs
- 1.33-c0.1-e0.17 - Remove Gadgets extension, but do do localization caching...

### October 2019

- 1.33-0.16 - Evil debugging for https://github.com/addshore/wbstack/issues/4
- 1.33-0.15 - Logging changes
- 1.33-0.14 - Sidebar links for qs and qs
- 1.33-0.13 - MW_DB_SERVER_MASTER and MW_DB_SERVER_REPLICA
- 1.33-0.12 - MW_EMAIL_DOMAIN added
- 1.33-0.11 - Include logging files i missed
- 1.33-0.10 - Wednesday before Wikidatacon
- 1.33-0.8 - With extension OAuth
- 1.33-0.8 - With InternalSettings.php
- 1.33-0.7 - Remove EventBus, Write our own thing....
- 1.33-0.6 - Fix for https://phabricator.wikimedia.org/T235943 (added composer.extra.json)
- 1.33-0.4 - Logging stuff for stderr
