<?php

/**
 * Definition of entity types for use with Wikibase.
 * The array returned by the code below is supposed to be merged with the content of
 * lib/WikibaseLib.entitytypes.php.
 * It defines the views used by the repo to display entities of different types.
 *
 * @note: Keep in sync with lib/WikibaseLib.entitytypes.php
 *
 * @note This is bootstrap code, it is executed for EVERY request.
 * Avoid instantiating objects here!
 *
 * @see docs/entitytypes.wiki
 *
 * @license GPL-2.0-or-later
 * @author Bene* < benestar.wikimedia@gmail.com >
 */

use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\SerializerFactory;
use Wikibase\DataModel\Services\EntityId\EntityIdFormatter;
use Wikibase\Lib\EntityTypeDefinitions as Def;
use Wikibase\Lib\Formatters\LabelsProviderEntityIdHtmlLinkFormatter;
use Wikibase\Lib\Store\LanguageFallbackLabelDescriptionLookup;
use Wikibase\Lib\TermLanguageFallbackChain;
use Wikibase\Repo\Api\CombinedEntitySearchHelper;
use Wikibase\Repo\Api\EntityIdSearchHelper;
use Wikibase\Repo\Api\EntityTermSearchHelper;
use Wikibase\Repo\Api\PropertyDataTypeSearchHelper;
use Wikibase\Repo\ChangeOp\Deserialization\ItemChangeOpDeserializer;
use Wikibase\Repo\ChangeOp\Deserialization\PropertyChangeOpDeserializer;
use Wikibase\Repo\Content\ItemContent;
use Wikibase\Repo\Content\PropertyContent;
use Wikibase\Repo\Diff\BasicEntityDiffVisualizer;
use Wikibase\Repo\Diff\ClaimDiffer;
use Wikibase\Repo\Diff\ClaimDifferenceVisualizer;
use Wikibase\Repo\Diff\ItemDiffVisualizer;
use Wikibase\Repo\EntityReferenceExtractors\EntityReferenceExtractorCollection;
use Wikibase\Repo\EntityReferenceExtractors\SiteLinkBadgeItemReferenceExtractor;
use Wikibase\Repo\EntityReferenceExtractors\StatementEntityReferenceExtractor;
use Wikibase\Repo\Hooks\Formatters\DefaultEntityLinkFormatter;
use Wikibase\Repo\ParserOutput\EntityTermsViewFactory;
use Wikibase\Repo\ParserOutput\TermboxFlag;
use Wikibase\Repo\Rdf\NullEntityRdfBuilder;
use Wikibase\Repo\Rdf\PropertyRdfBuilder;
use Wikibase\Repo\Rdf\RdfProducer;
use Wikibase\Repo\Rdf\RdfVocabulary;
use Wikibase\Repo\Rdf\SiteLinksRdfBuilder;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\View\FingerprintableEntityMetaTagsCreator;
use Wikimedia\Purtle\RdfWriter;

return [
	'item' => [
		Def::STORAGE_SERIALIZER_FACTORY_CALLBACK => function( SerializerFactory $serializerFactory ) {
			return $serializerFactory->newItemSerializer();
		},
		Def::VIEW_FACTORY_CALLBACK => function(
			Language $language,
			TermLanguageFallbackChain $fallbackChain,
			EntityDocument $entity
		) {
			$wikibaseRepo = WikibaseRepo::getDefaultInstance();
			$viewFactory = $wikibaseRepo->getViewFactory();
			return $viewFactory->newItemView(
				$language,
				$fallbackChain,
				( new EntityTermsViewFactory() )
					->newEntityTermsView(
						$entity,
						$language,
						$fallbackChain,
						TermboxFlag::getInstance()->shouldRenderTermbox()
					)
			);
		},
		Def::META_TAGS_CREATOR_CALLBACK => function ( $userLanguage ) {
			$languageFallbackChainFactory = WikibaseRepo::getLanguageFallbackChainFactory();
			$languageFallbackChain = $languageFallbackChainFactory->newFromLanguage( $userLanguage );
			return new FingerprintableEntityMetaTagsCreator( $languageFallbackChain );
		},
		Def::CONTENT_MODEL_ID => ItemContent::CONTENT_MODEL_ID,
		Def::CONTENT_HANDLER_FACTORY_CALLBACK => function() {
			$wikibaseRepo = WikibaseRepo::getDefaultInstance();
			return $wikibaseRepo->newItemHandler();
		},
		Def::ENTITY_FACTORY_CALLBACK => function() {
			return new Item();
		},
		Def::CHANGEOP_DESERIALIZER_CALLBACK => function() {
			return new ItemChangeOpDeserializer(
				WikibaseRepo::getDefaultInstance()->getChangeOpDeserializerFactory()
			);
		},
		Def::RDF_BUILDER_FACTORY_CALLBACK => function(
			$flavorFlags,
			RdfVocabulary $vocabulary,
			RdfWriter $writer,
			$mentionedEntityTracker,
			$dedupe
		) {
			if ( $flavorFlags & RdfProducer::PRODUCE_SITELINKS ) {
				$sites = WikibaseRepo::getDefaultInstance()->getSiteLookup()->getSites();
				// Since the only extra mapping needed for Items are site links,
				// we just return the SiteLinksRdfBuilder directly,
				// instead of defining an ItemRdfBuilder
				$builder = new SiteLinksRdfBuilder( $vocabulary, $writer, $sites );
				$builder->setDedupeBag( $dedupe );
				return $builder;
			}
			return new NullEntityRdfBuilder();
		},
		Def::ENTITY_DIFF_VISUALIZER_CALLBACK => function (
			MessageLocalizer $messageLocalizer,
			ClaimDiffer $claimDiffer,
			ClaimDifferenceVisualizer $claimDiffView,
			SiteLookup $siteLookup,
			EntityIdFormatter $entityIdFormatter
		) {
			$basicEntityDiffVisualizer = new BasicEntityDiffVisualizer(
				$messageLocalizer,
				$claimDiffer,
				$claimDiffView,
				$siteLookup,
				$entityIdFormatter
			);

			return new ItemDiffVisualizer(
				$messageLocalizer,
				$claimDiffer,
				$claimDiffView,
				$siteLookup,
				$entityIdFormatter,
				$basicEntityDiffVisualizer
			);
		},
		Def::ENTITY_SEARCH_CALLBACK => function ( WebRequest $request ) {
			$repo = WikibaseRepo::getDefaultInstance();

			return new CombinedEntitySearchHelper(
					[
						new EntityIdSearchHelper(
							WikibaseRepo::getEntityLookup(),
							WikibaseRepo::getEntityIdParser(),
							new LanguageFallbackLabelDescriptionLookup(
								WikibaseRepo::getTermLookup(),
								WikibaseRepo::getLanguageFallbackChainFactory()
									->newFromLanguage( WikibaseRepo::getUserLanguage() )
							),
							$repo->getEntityTypeToRepositoryMapping()
						),
						new EntityTermSearchHelper(
							$repo->newTermSearchInteractor( WikibaseRepo::getUserLanguage()->getCode() )
						)
					]
			);
		},
		Def::LINK_FORMATTER_CALLBACK => function( Language $language ) {
			return new DefaultEntityLinkFormatter(
				$language,
				WikibaseRepo::getEntityTitleTextLookup()
			);
		},
		Def::ENTITY_ID_HTML_LINK_FORMATTER_CALLBACK => function( Language $language ) {
			$repo = WikibaseRepo::getDefaultInstance();
			$languageLabelLookupFactory = WikibaseRepo::getLanguageFallbackLabelDescriptionLookupFactory();
			$languageLabelLookup = $languageLabelLookupFactory->newLabelDescriptionLookup( $language );
			return new LabelsProviderEntityIdHtmlLinkFormatter(
				$languageLabelLookup,
				$repo->getLanguageNameLookup(),
				WikibaseRepo::getEntityExistenceChecker(),
				WikibaseRepo::getEntityTitleTextLookup(),
				WikibaseRepo::getEntityUrlLookup(),
				WikibaseRepo::getEntityRedirectChecker()
			);
		},
		Def::ENTITY_REFERENCE_EXTRACTOR_CALLBACK => function() {
			return new EntityReferenceExtractorCollection( [
				new SiteLinkBadgeItemReferenceExtractor(),
				new StatementEntityReferenceExtractor( WikibaseRepo::getItemUrlParser() )
			] );
		},
	],
	'property' => [
		Def::STORAGE_SERIALIZER_FACTORY_CALLBACK => function( SerializerFactory $serializerFactory ) {
			return $serializerFactory->newPropertySerializer();
		},
		Def::VIEW_FACTORY_CALLBACK => function(
			Language $language,
			TermLanguageFallbackChain $fallbackChain,
			EntityDocument $entity
		) {
			$wikibaseRepo = WikibaseRepo::getDefaultInstance();
			$viewFactory = $wikibaseRepo->getViewFactory();
			return $viewFactory->newPropertyView(
				$language,
				$fallbackChain,
				( new EntityTermsViewFactory() )
					->newEntityTermsView(
						$entity,
						$language,
						$fallbackChain,
						TermboxFlag::getInstance()->shouldRenderTermbox()
					)
			);
		},
		Def::META_TAGS_CREATOR_CALLBACK => function ( Language $userLanguage ) {
			$languageFallbackChainFactory = WikibaseRepo::getLanguageFallbackChainFactory();
			$languageFallbackChain = $languageFallbackChainFactory->newFromLanguage( $userLanguage );
			return new FingerprintableEntityMetaTagsCreator( $languageFallbackChain );
		},
		Def::CONTENT_MODEL_ID => PropertyContent::CONTENT_MODEL_ID,
		Def::CONTENT_HANDLER_FACTORY_CALLBACK => function() {
			$wikibaseRepo = WikibaseRepo::getDefaultInstance();
			return $wikibaseRepo->newPropertyHandler();
		},
		Def::ENTITY_FACTORY_CALLBACK => function() {
			return Property::newFromType( '' );
		},
		Def::CHANGEOP_DESERIALIZER_CALLBACK => function() {
			return new PropertyChangeOpDeserializer(
				WikibaseRepo::getDefaultInstance()->getChangeOpDeserializerFactory()
			);
		},
		Def::RDF_BUILDER_FACTORY_CALLBACK => function(
			$flavorFlags,
			RdfVocabulary $vocabulary,
			RdfWriter $writer,
			$mentionedEntityTracker,
			$dedupe
		) {
			return new PropertyRdfBuilder(
				$vocabulary,
				$writer,
				WikibaseRepo::getDataTypeDefinitions()->getRdfDataTypes()
			);
		},
		Def::ENTITY_SEARCH_CALLBACK => function ( WebRequest $request ) {
			$repo = WikibaseRepo::getDefaultInstance();

			return new PropertyDataTypeSearchHelper(
				new CombinedEntitySearchHelper(
					[
						new EntityIdSearchHelper(
							WikibaseRepo::getEntityLookup(),
							WikibaseRepo::getEntityIdParser(),
							new LanguageFallbackLabelDescriptionLookup(
								$repo->getTermLookup(),
								WikibaseRepo::getLanguageFallbackChainFactory()
									->newFromLanguage( WikibaseRepo::getUserLanguage() )
							),
							$repo->getEntityTypeToRepositoryMapping()
						),
						new EntityTermSearchHelper(
							$repo->newTermSearchInteractor( WikibaseRepo::getUserLanguage()->getCode() )
						)
					]
				),
				$repo->getPropertyDataTypeLookup()
			);
		},
		Def::LINK_FORMATTER_CALLBACK => function( Language $language ) {
			return new DefaultEntityLinkFormatter(
				$language,
				WikibaseRepo::getEntityTitleTextLookup()
			);
		},
		Def::ENTITY_ID_HTML_LINK_FORMATTER_CALLBACK => function( Language $language ) {
			$repo = WikibaseRepo::getDefaultInstance();
			$languageLabelLookupFactory = WikibaseRepo::getLanguageFallbackLabelDescriptionLookupFactory();
			$languageLabelLookup = $languageLabelLookupFactory->newLabelDescriptionLookup( $language );
			return new LabelsProviderEntityIdHtmlLinkFormatter(
				$languageLabelLookup,
				$repo->getLanguageNameLookup(),
				WikibaseRepo::getEntityExistenceChecker(),
				WikibaseRepo::getEntityTitleTextLookup(),
				WikibaseRepo::getEntityUrlLookup(),
				WikibaseRepo::getEntityRedirectChecker()
			);
		},
		Def::ENTITY_REFERENCE_EXTRACTOR_CALLBACK => function() {
			return new StatementEntityReferenceExtractor( WikibaseRepo::getItemUrlParser() );
		},
	]
];
