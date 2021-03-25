# mediawiki

## 1.35-7.4-TO-BE-TAGGED

- REL1_35 updates for MediaWiki, skins and extensions
- Actually include wmde/php-vuejs-templating for WikibaseLexeme
- Fix exception shown when wikis don't exist or are deleted
- Enable WikibaseManifest by default on all wikis
- Fix protocol in $wgServer MediaWiki variable
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
