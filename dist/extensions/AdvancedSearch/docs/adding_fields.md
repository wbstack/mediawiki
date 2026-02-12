# Adding Fields to AdvancedSearch

This document explains how to add your own fields to a new section in AdvancedSearch, using the `advancedSearch.configureFields` JavaScript hook.

## Example Code: Prefix search

[The search keyword `prefix:`](https://www.mediawiki.org/wiki/Help:CirrusSearch#Prefix_and_namespace) allows to search for pages where the page title starts with the search string. To add a search field for the `prefix:` keyword to AdvancedSearch, you need to add a hook handler for the `advancedSearch.configureFields` hook. The hook gets an instance of [FieldCollection](../modules/ext.advancedSearch.FieldCollection.js) and you can use its `add` method to add a field configuration for your own field.

The `fieldCollection.add` method takes your field definition and the section name where the new field should appear (in our case we're using the section name `extra`). The field definition object needs three methods:

* `formatter` converts the user input to the search keyword string,
* `init` creates the field widget
* `layout` defines the label, help text and placement of the field widget


```javascript
const { createSearchFieldFromObject, TextInput } = require( 'ext.advancedSearch.elements' );
mw.hook( 'advancedSearch.configureFields' ).add( function ( fieldCollection ) {
  var fieldDefinition = {
    id: 'prefix',
    defaultValue: '',
    formatter: function ( val ) {
      return 'prefix:' + val.trim();
    },
    init: function ( state, config ) {
      return new TextInput( state, config );
    },
    layout: function ( widget, field ) {
      return new OO.ui.FieldLayout(
        widget,
        {
          label: mw.msg( 'advancedsearch-field-' + field.id ),
          align: 'right',
          help: mw.msg( 'advancedsearch-field-help-' + field.id ),
          $overlay: true
        }
      );
    }
  };
  fieldCollection.add( createSearchFieldFromObject( fieldDefinition , 'extra' ) );
} );
```

`createSearchFieldFromObject` is a helper function that returns a subclassed instance of [`SearchField`](../modules/ext.advancedSearch.SearchField.js). `SearchField` is an abstract class that defines the properties and methods that all search field configuration entries must implement. All the object properties and methods you pass into the field definition in `createSearchFieldFromObject` will become properties and methods of the new `SearchField` subclass.

You can place the code for adding new fields in the [`common.js` page](https://www.mediawiki.org/wiki/Manual:Interface/JavaScript) or in a [gadget definition](https://www.mediawiki.org/wiki/Extension:Gadgets).

## In-depth explanation of object properties for field definitions

### Mandatory properties

#### id

A unique name for the field.

#### defaultValue

The default value for the field. The correct value (empty string, empty array, etc) depends on the widget you use in the `init` function (see below). For example, for `TextInput`, the default value needs to be an empty string. For `ArbitraryWordInput` (the pill-style input), the default value needs to be an empty array.

#### formatter
The formatter function takes the field value and converts it to MediaWiki search keywords.

The type of the input parameter depends on the field widget you chose - `TextInput` values are strings, `ArbitraryWordInput` values are an array of strings, etc.

Here is an example for the formatter for the *pages without this word* field that uses `ArbitraryWordInput`:

```javascript
function ( val ) {
  if ( Array.isArray( val ) ) {
    return val.map( function ( el ) {
      return '-' + el;
    } ).join( ' ' );
  }
  return '-' + val;
}
```

If you're getting strings as input, remember to trim the whitespace at the beginning and end of the value. Please think of all possible values that might come from your field. You might need to quote and/or escape the values coming from your field.

#### init

A function that returns an AdvancedSearch widget instance. AdvancedSearch widgets are subclasses of OOUI widgets with additional state handling capabilities.

If you want to have a plain text input field or a "pill box" text input field like most of the other AdvancedSearch fields, use the `TextInput` and `ArbitraryWordInput` widgets.

For more complex custom widgets, have a look at the in-depth explanation the section "Implementing your own widgets" to see what state-handling methods you need.

#### layout
A function that returns an `OO.ui.FieldLayout` instance. The function gets passed the widget that the `init` function created and the `field` instance from your call to `createSearchFieldFromObject`.

If you want to make the `help` option translatable and if it contains complex HTML (with examples and lists), you can't use `mw.msg` until issue https://phabricator.wikimedia.org/T27349 is resolved. A possible workaround for that is to assign the translated strings to `mw.config`.

If you want to make a dependent field, where you show or hide the field depending on the value of another field (like the image dimensions), you can use the third parameter for the layout function, `state`, and subscribe to its `update` method to check for specific values in the current form state. See the function `createImageDimensionLayout` in the [default field definitions](../modules/ext.advancedSearch.defaultFields.js) as an example.

### Optional properties

#### customEventHandling
Boolean, default `false`. Usually, the `FieldElementBuilder` takes care of connecting the `change` event of AdvancedSearch widgets to the store. If your widget has no `change` event or you want to handle it yourself, set this option to `true`.

#### enabled
A function that returns a boolean. The function does runtime checks to determine if the field should be shown (e.g. if certain other extensions are enabled).

## Providing labels

You provide the i18n keys for the translated field label and help text in the `layout` function, just like the example does.

The i18n label for the section name has the prefix `advancedsearch-optgroup-`. So if you're adding the group `extra`, you must provide translations for the message key `advancedsearch-optgroup-extra`.

## Implementing your own widgets

### The data model of AdvancedSearch
AdvancedSearch has a unidirectional data flow: User input events in AdvancedSearch widgets collect the user input and send it to its `store` property, a [`SearchModel`](../modules/dm/ext.advancedSearch.SearchModel.js) instance. All calls to `SearchModel.storeField` trigger the `update` event of `SearchModel`. All AdvancedSearch widgets listen to the `update` event from `SearchModel` to update their value.

```
+--------+  User event (e.g. "change")  +---------------+ field data  +----------+
|        +----------------------------->+ Event Handler +------------>+          |
|        |                              +---------------+             |          |
| Widget |                                            "update" event  |  Store   |
|        +<-----------------------------------------------------------+          |
+--------+                                                            +----------+

```  

This event-based, one-directional data flow enables

* showing the field field values in different places, e.g. the preview "pills" and the fields themselves
* serializing and deserializing the form state when the user sends the form
* to programmatically triggering changing the contents of dependent fields.

Your widgets don't have to implement the event handler themselves, AdvancedSearch initialization code in the [`FieldElementBuilder`](../modules/ext.advancedSearch.FieldElementBuilder.js) class takes care of setting up the event handler. 

Here is some minimal widget example code that shows how to handle the "update" event from the store:

```javascript
const MyWidget = function ( store, config ) {
	this.fieldId = config.fieldId;
	this.store = store;
	store.connect( this, { update: 'onStoreUpdate' } );

	MyWidget.parent.call( this, config );
	this.setValuesFromStore();
};

OO.inheritClass( MyWidget, OO.ui.Widget );

MyWidget.prototype.onStoreUpdate = function () {
	this.setValuesFromStore();
};

MyWidget.prototype.setValuesFromStore = function () {
	if ( this.store.hasFieldChanged( this.fieldId, this.data ) ) {
		this.setValue( this.store.getField( this.fieldId ) );
	}
};
```

**Note:** To avoid infinite loops, the event handler in the widget must check if the value coming from the `update` event is the same as the one already in the widget. If the values are the same, don't set it again, otherwise the widget will trigger another change event, sending the value to the store. As you can see, there is a utility method in the store for that.
 
