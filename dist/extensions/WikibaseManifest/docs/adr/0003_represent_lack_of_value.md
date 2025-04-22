# 3) How to represent lack of value in the Manifest

Date: 2020-10-01

## Status

accepted

## Context

[The OpenAPI specification](https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/extensions/WikibaseManifest/+/refs/heads/master/openapi.json) of the WikibaseManifest [lists required fields](https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/extensions/WikibaseManifest/+/refs/heads/master/schemas.json). There're some fields in the spec, which remain optional because some Wikibases won't have certain extensions installed or certain information configured.

How should we represent lack of value in the Manifest response: by **setting the value to null**, or by **omitting the property** from the response?

- There's a semantic difference between omitting a property and setting it to null.
If a Wikibase cannot provide a certain information to the Manifest it means the Wikibase does not have that information due to missing extensions or configuration. Then it would make sense to **omit the property**.
On the other hand, a Wikibase can be using certain things of importance to the Manifest, but not have them configured in `LocalSettings.php`, which makes them unavailable to the Manifest. In that case setting the value to **null** (because it's not properly configured) makes sense.

- It can be "noisy" and annoying to have a long response with most of the values set to null in case all optional fields do not have a value.
That won't be the case for the Manifest, because most of the information we expose in `v1` is listed as required.

- When removing properties from the implementation of the Manifest, forward compatibility can be achieved if we decide to go with omitting properties.
As of the moment of writing this ADR we are not aware of any properties that are potentially not useful and are a candidate for a removal.

- [Google's JSON style guide](https://google.github.io/styleguide/jsoncstyleguide.xml#Empty/Null_Property_Values) suggests omitting optional, empty or null values, unless there's a strong semantic reason for their existence.

- As far as we are aware the client does not have a strong need to make a difference between a missing key, an empty value, or a null value.

- [OpenAPI 3.0](https://swagger.io/docs/specification/data-models/data-types/#null) doesn't have a null type per se, but it is possible to communicate a value is null by using `nullable: true`.

- [The MediaWiki REST API's design principles](https://www.mediawiki.org/wiki/Core_Platform_Team/Initiative/Core_REST_API_in_MediaWiki/Design_principles) state that "Empty properties should have the value null".

## Decision

Represent lack of value of optional spec properties by omitting the property from the response.

## Consequences

- Have a concise response that doesn't include values which don't carry meaning.
- We are consciously breaking the [MediaWiki REST API's design principles](https://www.mediawiki.org/wiki/Core_Platform_Team/Initiative/Core_REST_API_in_MediaWiki/Design_principles) by choosing to omit a property over setting its value to null.
