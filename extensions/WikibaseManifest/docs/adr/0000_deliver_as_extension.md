# 0) Deliver Wikibase Manifest as an extension

Date: 2020-08-27

## Status

accepted

## Context

We considered two options for delivering Wikibase Manifest: 
- Built as MediaWiki Extension
- Include in Wikibase Core

|   Options	|  Consistency across 3rd party Wikibases 	|   Installation/Setup Burden	|  User Adoption 	|
|---	|---	|---	|---	|
|  MW Extension 	|   Third-party Wikibase admins will have to provide the Manifest via extension.	|   Additional effort of setting up an extension. It could be part of the docker bundle	|   Assuming lower as it involves installing a separate extension	|
|  Wikibase core	|   Tool builders could rely on the Manifest always being there in any third-party Wikibase. This would be an advantage over the extension. But the user still needs to configure it. |   None	|  Assuming higher	|

|   Options	|  Maintenance Burden 	|   Backwards Compatibility	|  Testing Infrastructure	|
|---	|---	|---	|---	|
|  MW Extension 	|   Need to set up our own CI	|   compatible|   Less straightforward to test in Beta if we make this an extension, will have to set up our own cloudvps test instance.	|
|  Wikibase core	|   Same as Wikibase	|   compatible	|   Could use Beta or a cloudvps test instance for testing infrastructure. Probably an advantage    | 

|   Options	|  Documentation	|   Speed of release to Wikibase users	|  Feedback loop with tool builders 	|
|---	|---	|---	|---	|
|  MW Extension 	|   Need to document installation of the extension, as well as setup. Probably minimal additional effort 	|   Potentially faster	|   Probably shorter, for the same reasons stated on speed of release to wb users	|
|  Wikibase core	|   Document how to work with the file	|   Waiting until a new release is made (e.g. 1.36) or we have to do some backporting	|   Not sure	|


## Decision

Build as a MediaWiki Extension.  
Plan to move the functionality to Wikibase core in the future.

## Consequences

Ability to release the functionality to Wikibase users in a timely manner.  
Ability to have shorter feedback loops with tool builders.  
Creating additional work in the future to move this to Wikibase core.
