### Graphite

#### Info

##### General

* **{PREFIX}** is a metric prefix defined by MediaWiki ([docs](https://www.mediawiki.org/wiki/Manual:$wgStatsdMetricPrefix)).
* **{AGGREGATION}** is a suffix added by statsd / graphite per aggregation type. ([docs](https://wikitech.wikimedia.org/wiki/Graphite#Extended_properties))
* You can find more docs @ https://wikitech.wikimedia.org/wiki/Graphite

##### AdvancedSearch specific

* **{PREFIX}** is "MediaWiki.AdvancedSearch"

#### Metrics

##### Special Page Loading

* **{PREFIX}.event.expand.{AGGREGATION}** - Number of times the AdvancedSearch bar was expanded.
* **{PREFIX}.event.collapse.{AGGREGATION}** - Number of times the AdvancedSearch bar was collapsed.
