# 2) Use OpenAPI Specification to define the WikibaseManifest format

Date: 2020-08-27

## Status

accepted

## Context

Wikibase Manifest needs to provide essential metadata and configuration options about a Wikibase instance.  
We need to decide on the format of the information the manifest will provide.

We took into consideration the following projects:
- the recent [REST API Prototype](https://github.com/wmde/wikibase-rest-fantasies) (by WMDE) and its [OpenAPI spec](https://raw.githubusercontent.com/wmde/wikibase-rest-fantasies/gh-pages/openapi.json)
- [OpenRefine's initiative](https://github.com/OpenRefine/wikibase-manifests) to collect manifests from different wikibases and their [json-schema spec](https://github.com/OpenRefine/wikibase-manifests/blob/master/wikibase-manifest-schema-v1.json)

The MediaWiki REST API which we are using (please refer to ADR 1 for more info) implements neither json schema nor openAPI.

### OpenAPI (swagger)

The OpenAPI Specification (formerly Swagger Specification) is an API description format for REST APIs. An OpenAPI file allows you to describe your entire API. [Swagger](swagger.io) is a set of open-source tools built around the OpenAPI Specification, like the [api editor](https://editor.swagger.io/).

- It’s popularly used for mocking services and generating SDKs. It's not commonly used for run-time functionality.
- Useful when you want to describe your entire API.
- **OpenAPI is both a subset of JSON Schema Draft 5 and a superset**

### Json Schema

JSON Schema is a vocabulary that allows you to validate, annotate, and manipulate JSON documents.
The specification is split into three parts, [Core](https://json-schema.org/draft/2019-09/json-schema-core.html), [Validation](https://json-schema.org/draft/2019-09/json-schema-validation.html), and [Hyper-Schema](https://json-schema.org/draft/2019-09/json-schema-hypermedia.html).

- JSON Schema is a good option when there are data models whose schema needs to be defined

## Decision

Use OpenAPI spec.  
We acknowledge that both are good options. We chose OpenAPI because the Wikidata team has created several products (e.g. [termbox](https://gerrit.wikimedia.org/r/plugins/gitiles/wikibase/termbox/+/refs/heads/master/openapi.json)) using the OpenAPI spec and plan on continue to do so when we have the opportunity.  
There're tools (e.g. [OpenAPI Schema to JSON Schema](https://github.com/openapi-contrib/openapi-schema-to-json-schema)) for converting from OpenAPI Schema Object or Parameter Object to JSON Schema in case the need arises to use json schema.

## Consequences

Specify the WikibaseManifest for tool builders in OpenAPI format.
Use the OpenAPI format to "discuss" the manifest internally.