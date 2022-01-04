### EventLogging

* https://meta.wikimedia.org/wiki/Schema:TwoColConflictConflict

### Graphite

#### Info

##### General

* **{PREFIX}** is a metric prefix defined by MediaWiki ([docs](https://www.mediawiki.org/wiki/Manual:$wgStatsdMetricPrefix)).
* **{AGGREGATION}** is a suffix added by statsd / graphite per aggregation type. ([docs](https://wikitech.wikimedia.org/wiki/Graphite#Extended_properties))
* You can find more docs @ https://wikitech.wikimedia.org/wiki/Graphite

##### TwoColConflict specific

* **{PREFIX}** is "MediaWiki.TwoColConflict"
* **{NSID}** is an id between NS_MAIN(0) and NS_CATEGORY_TALK(15) - [Manual:Namespace#Built-in_namespaces](https://www.mediawiki.org/wiki/Manual:Namespace#Built-in_namespaces)

#### Metrics

* **{PREFIX}.conflict.{AGGREGATION}** - Total number of edit conflict page loads (All namespaces)
* **{PREFIX}.conflict.byNamespaceId.{NSID}.{AGGREGATION}** - Number of edit conflict page loads in the given namespace
  * A user refreshing a conflict page would result in 2 counts here
* **{PREFIX}.conflict.byUserEdits.anon.{AGGREGATION}** - Number of edit conflict page loads shown to anonymous users
* **{PREFIX}.conflict.byUserEdits.over200.{AGGREGATION}** - Number of edit conflict pages shown to users with over 200 edits
* **{PREFIX}.conflict.byUserEdits.over100.{AGGREGATION}** - Number of edit conflict pages shown to users with 101-200 edits
* **{PREFIX}.conflict.byUserEdits.over10.{AGGREGATION}** - Number of edit conflict pages shown to users with 11-100 edits
* **{PREFIX}.conflict.byUserEdits.under11.{AGGREGATION}** - Number of edit conflict pages shown to users with 0-10 edits


* **{PREFIX}.conflict.resolved.{AGGREGATION}** - Total number of conflicts resolved (All namespaces)
* **{PREFIX}.conflict.resolved.byNamespaceId.{NSID}.{AGGREGATION}** - Number of conflicts resolved in the given namespace
* **{PREFIX}.conflict.resolved.byUserEdits.anon.{AGGREGATION}** - Number of edit conflicts resolved by anonymous users
* **{PREFIX}.conflict.resolved.byUserEdits.over200.{AGGREGATION}** - Number of edit conflicts resolved by users with over 200 edits
* **{PREFIX}.conflict.resolved.byUserEdits.over100.{AGGREGATION}** - Number of edit conflicts resolved by users with 101-200 edits
* **{PREFIX}.conflict.resolved.byUserEdits.over10.{AGGREGATION}** - Number of edit conflicts resolved by users with 11-100 edits
* **{PREFIX}.conflict.resolved.byUserEdits.under11.{AGGREGATION}** - Number of edit conflicts resolved by users with 0-10 edits


* **{PREFIX}.copy.jsclick.{AGGREGATION}** - Total number of clicks of the link to copy the originally submitted text in-place ( multiple clicks from the same conflict will only be counted once )
* **{PREFIX}.copy.special.load.{AGGREGATION}** - Total number of loads of the special page to show the originally submitted text
* **{PREFIX}.copy.special.retrieved.{AGGREGATION}** - Total number of successfully retrieved texts from the cache on the special page to show the originally submitted text
