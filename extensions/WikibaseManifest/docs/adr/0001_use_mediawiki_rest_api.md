# 1) Use MediaWiki REST API

Date: 2020-08-27

## Status

accepted

## Context

Wikibase Manifest needs to provide essential metadata and configuration options about a Wikibase instance in an automated way.

We can achieve this by using either the [Wikibase Action API](https://www.mediawiki.org/wiki/Wikibase/API) or the [MediaWiki REST API](https://www.mediawiki.org/wiki/API:REST_API).

### Wikibase Action API

`+` The developers on the team have experience working with Wikibase's API

### MediaWiki REST API

`+` Built more recently
`+` Has good test coverage thanks to [mediawiki-tools-api-testing](https://github.com/wikimedia/mediawiki-tools-api-testing)
`+` Developers want to try out this new REST API
`+` Broadly accepted as a standard and probably easier to use for tool builders than the Action API

We don't see any significant downsides of using one or the other.

## Decision

Use MediaWiki REST API.

## Consequences

Gain more knowledge about the new MediaWiki REST API.
