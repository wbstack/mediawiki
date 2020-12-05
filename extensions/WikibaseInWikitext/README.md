Adds the "sparql" tag for rendering sparql with some helpful things around it such as list of
entities referenced and a link to try it in a sparql UI.

Also hooks in with syntaxhighlight if provided to make it look pretty.

This is currently intended for use of https://wbstack.com and might change without release notes etc.


Settings:

$wgWikibaseInWikitextSparqlDefaultUi


Example:

```
<sparql list="1" tryit="1">
#Cats
SELECT ?item ?itemLabel 
WHERE 
{
  ?item wdt:P31 wd:Q146.
  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en". }
}
</sparql>
```
