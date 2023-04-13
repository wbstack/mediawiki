const
	View = require( '../mobile.startup/View' ),
	util = require( '../mobile.startup/util' ),
	langUtil = require( './util' ),
	mfExtend = require( '../mobile.startup/mfExtend' );

/**
 * Overlay displaying a structured list of languages for a page
 *
 * @class LanguageSearcher
 * @extends View
 *
 * @param {Object} props Configuration options
 * @param {Object[]} props.languages list of language objects as returned by the API
 * @param {Array|boolean} props.variants language variant objects
 *  or false if no variants exist
 * @param {boolean} props.showSuggestedLanguages If the suggested languages
 *  section should be rendered.
 * @param {string} [props.deviceLanguage] the device's primary language
 * @param {Function} [props.onOpen] callback that fires on opening the searcher
 * @param {Function} [props.onBannerClick] callback that fires when banner is clicked
 */
function LanguageSearcher( props ) {
	/**
	 * @prop {StructuredLanguages} languages` JSDoc.
	 */
	const languages = langUtil.getStructuredLanguages(
		props.languages,
		props.variants,
		langUtil.getFrequentlyUsedLanguages(),
		props.showSuggestedLanguages,
		props.deviceLanguage
	);

	View.call(
		this,
		util.extend(
			{
				className: 'language-searcher',
				events: {
					'click .language-search-banner': props.onBannerClick,
					'click a': 'onLinkClick',
					'input .search': 'onSearchInput'
				},
				// the rest are template properties
				inputPlaceholder: mw.msg( 'mobile-frontend-languages-structured-overlay-search-input-placeholder' ),
				// we can't rely on CSS only to uppercase the headings. See https://stackoverflow.com/questions/3777443/css-text-transform-not-working-properly-for-turkish-characters
				allLanguagesHeader: mw.msg( 'mobile-frontend-languages-structured-overlay-all-languages-header' ).toLocaleUpperCase(),
				suggestedLanguagesHeader: mw.msg( 'mobile-frontend-languages-structured-overlay-suggested-languages-header' ).toLocaleUpperCase(),
				noResultsFoundHeader: mw.msg( 'mobile-frontend-languages-structured-overlay-no-results' ),
				noResultsFoundMessage: mw.msg( 'mobile-frontend-languages-structured-overlay-no-results-body' ),
				allLanguages: languages.all,
				allLanguagesCount: languages.all.length,
				suggestedLanguages: languages.suggested,
				suggestedLanguagesCount: languages.suggested.length,
				showSuggestedLanguagesHeader: languages.suggested.length > 0
			},
			props
		)
	);

	// defer event to be emitted after event handler has been registered
	const onOpen = props.onOpen;
	if ( !onOpen ) {
		return;
	}
	const searcher = this;
	setTimeout( () => {
		onOpen( searcher );
	}, 0 );
}

mfExtend( LanguageSearcher, View, {
	/**
	 * @inheritdoc
	 * @memberof LanguageSearcher
	 * @instance
	 */
	template: util.template( `
<div class="panel">
	<div class="panel-body search-box">
		<input type="search" class="search mw-ui-background-icon-search" placeholder="{{inputPlaceholder}}">
	</div>
</div>

<div class="overlay-content-body">
	{{#showSuggestedLanguagesHeader}}
	<h3 class="list-header">{{suggestedLanguagesHeader}}</h3>
	{{/showSuggestedLanguagesHeader}}
	{{#suggestedLanguagesCount}}
	<ol class="site-link-list suggested-languages">
		{{#suggestedLanguages}}
			<li>
				<a href="{{url}}" class="{{lang}}" hreflang="{{lang}}" lang="{{lang}}" dir="{{dir}}">
					<span class="autonym">{{autonym}}</span>
					{{#title}}
						<span class="title">{{title}}</span>
					{{/title}}
				</a>
			</li>
		{{/suggestedLanguages}}
	</ol>
	{{/suggestedLanguagesCount}}
	{{#bannerHTML}}
	<div class="language-search-banner">
		{{{.}}}
	</div>
	{{/bannerHTML}}
	{{#allLanguagesCount}}
	<h3 class="list-header">{{allLanguagesHeader}} ({{allLanguagesCount}})</h3>
	<ul class="site-link-list all-languages">
		{{#allLanguages}}
			<li>
				<a href="{{url}}" class="{{lang}}" hreflang="{{lang}}" lang="{{lang}}" dir="{{dir}}">
					<span class="autonym">{{autonym}}</span>
					{{#title}}
						<span class="title">{{title}}</span>
					{{/title}}
				</a>
			</li>
		{{/allLanguages}}
	</ul>
	{{/allLanguagesCount}}
	<section class="empty-results hidden">
		<h4 class="empty-results-header">{{noResultsFoundHeader}}</h4>
		<p class="empty-results-body">{{noResultsFoundMessage}}</p>
	</section>
</div>
	` ),
	/**
	 * @inheritdoc
	 * @memberof LanguageSearcher
	 * @instance
	 */
	postRender: function () {
		// cache
		this.$siteLinksList = this.$el.find( '.site-link-list' );
		this.$languageItems = this.$siteLinksList.find( 'a' );
		this.$subheaders = this.$el.find( 'h3' );
		this.$emptyResultsSection = this.$el.find( '.empty-results' );
	},
	/**
	 * Method that can be called outside MF extension to render
	 * a banner inside the language overlay.
	 *
	 * @stable for use inside ContentTranslation
	 * @param {string} bannerHTML
	 */
	addBanner: function ( bannerHTML ) {
		this.options.bannerHTML = bannerHTML;
		this.options.showSuggestedLanguagesHeader = true;
		this.render();
	},
	/**
	 * Article link click event handler
	 *
	 * @memberof LanguageSearcher
	 * @instance
	 * @param {jQuery.Event} ev
	 */
	onLinkClick: function ( ev ) {
		const $link = this.$el.find( ev.currentTarget ),
			lang = $link.attr( 'lang' );

		mw.hook( 'mobileFrontend.languageSearcher.linkClick' ).fire( lang );
		langUtil.saveLanguageUsageCount( lang, langUtil.getFrequentlyUsedLanguages() );
	},
	/**
	 * Search input handler
	 *
	 * @memberof LanguageSearcher
	 * @instance
	 * @param {jQuery.Event} ev Event object.
	 */
	onSearchInput: function ( ev ) {
		this.filterLanguages( this.$el.find( ev.target ).val().toLowerCase() );
	},
	/**
	 * Filter the language list to only show languages that match the current search term.
	 *
	 * @memberof LanguageSearcher
	 * @instance
	 * @param {string} searchQuery of search term (lowercase).
	 */
	filterLanguages: function ( searchQuery ) {
		const filteredList = [];

		if ( searchQuery ) {
			this.options.languages.forEach( function ( language ) {
				const langname = language.langname;
				// search by language code or language name
				if ( language.autonym.toLowerCase().indexOf( searchQuery ) > -1 ||
						( langname && langname.toLowerCase().indexOf( searchQuery ) > -1 ) ||
						language.lang.toLowerCase().indexOf( searchQuery ) > -1
				) {
					filteredList.push( language.lang );
				}
			} );

			if ( this.options.variants ) {
				this.options.variants.forEach( function ( variant ) {
					// search by variant code or variant name
					if ( variant.autonym.toLowerCase().indexOf( searchQuery ) > -1 ||
						variant.lang.toLowerCase().indexOf( searchQuery ) > -1
					) {
						filteredList.push( variant.lang );
					}
				} );
			}

			this.$languageItems.addClass( 'hidden' );
			if ( filteredList.length ) {
				this.$siteLinksList.find( '.' + filteredList.join( ',.' ) ).removeClass( 'hidden' );
				this.$emptyResultsSection.addClass( 'hidden' );
			} else {
				this.$emptyResultsSection.removeClass( 'hidden' );
				// Fire with the search query and the DOM element corresponding to no-results
				// message so that it can be customized in hook handler
				mw.hook( 'mobileFrontend.languageSearcher.noresults' )
					.fire( searchQuery, this.$emptyResultsSection.get( 0 ) );
			}
			this.$siteLinksList.addClass( 'filtered' );
			this.$subheaders.addClass( 'hidden' );
		} else {
			this.$languageItems.removeClass( 'hidden' );
			this.$siteLinksList.removeClass( 'filtered' );
			this.$subheaders.removeClass( 'hidden' );
			this.$emptyResultsSection.addClass( 'hidden' );
		}
	}
} );

module.exports = LanguageSearcher;
