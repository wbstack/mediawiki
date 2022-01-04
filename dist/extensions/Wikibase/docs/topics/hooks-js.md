# Hooks JS

This file describes JavaScript hooks defined by the Wikibase extensions.

[TOC]

#### wikibase.entityPage.entityView.rendered (entityViewInit.js)
  * Called after the Wikibase UI is initialized.

#### wikibase.entityPage.entityLoaded (entityLoaded.js)
  * Called as soon as the JSON representing the entity stored on the current entity page is loaded.
  * Listener callbacks should expect the entity as a native JavaScript object (the parsed JSON serialization) passed as the first argument.

#### wikibase.statement.saved (StatementsChanger.js)
  * Called after a statement has been saved. Entity ID, statement ID, the old statement (null in case of a new statement) and the updated one are passed as arguments.

#### wikibase.statement.removed (StatementsChanger.js)
  * Called after a statement has been removed. Entity ID and statement ID are passed as arguments.

#### wikibase.entityselector.search (entityselector.js)
  * Called when entity selector fetches suggestions.
  * An object containing the following elements is passed as first argument :element, term and options. As second argument a function is passed allowing to add promises that return additional suggestion items. Those items will replace existing items with the same ID and will be placed on top of the list. If an item has a property `rating` then this property will be used for sorting the list by it descending (higher `rating` on top). The range of the `rating` should be between 0-1.

#### wikibase.statement.startEditing (jquery.wikibase.statementview.js)
  * Called when entering the edit mode for an existing statement. Gets the statement's guid passed as parameter.

#### wikibase.statement.stopEditing (jquery.wikibase.statementview.js)
  * Called when leaving the edit mode for an existing statement. Gets the statement's guid passed as parameter.
