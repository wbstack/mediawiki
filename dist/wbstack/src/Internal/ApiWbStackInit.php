<?php

namespace WBStack\Internal;

use MediaWiki\MediaWikiServices;
use MediaWiki\User\UserFactory;
use MediaWiki\Revision\SlotRecord;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * This API is called when a wiki is first created.
 * It creates a user account.
 * It optionally sets a password.
 * It optionally sets an email.
 * If an email is set and no password is set, then a reset email will be sent.
 *
 * This code is a combination of maintenance/createAndPromote.php and SpecialPasswordReset.php ideas
 */

class ApiWbStackInit extends \ApiBase {
    public function mustBePosted() {return true;}
    public function isWriteMode() {return true;}
    public function isInternal() {return true;}
    public function execute() {
        $this->executeWikiUser();
        WbStackPlatformReservedUser::createIfNotExists();
        $this->initMainPage();
    }
    public function executeWikiUser() {
        $username = $this->getParameter('username');
        $email = $this->getParameter('email');
        $password = $this->getParameter('password');

        $services = MediaWikiServices::getInstance();

        // TODO validation? but our app should always send the right stuff now anyway..

        // Get a user object that we will be interacting with
        $user = $services->getUserFactory()->newFromName( $username, UserFactory::RIGOR_VALID );

        // The user that we want to create should NOT already exist, so bail quickly if it does.
        // TODO the user could be renamed?, so check if # of users > 0 instead here too?
        if($user->idForName() !== 0){
            $this->addFailedNote( 'User already existed: ' . $user->idForName() );
            return;
        }

        // Create the user
        $createStatus = $services->getAuthManager()->autoCreateUser(
            $user,
            \MediaWiki\Auth\AuthManager::AUTOCREATE_SOURCE_MAINT,
            false
        );
        // TODO check the status of $createStatus

        // Mark the e-mail address confirmed.
        if($email){
            $user->setEmail( $email );
            $user->confirmEmail();
            $user->saveSettings();
        }

        // Set a password if needed
        if($password){
            $passwordStatus = $user->changeAuthenticationData([
                'username' => $user->getName(),
                'password' => $password,
                'retype' => $password,
            ]);
            if(!$passwordStatus->isGood()){
                $this->addFailedNote('User password could not be set');
                return;
            }
            // TODO this saveSettings might not be needed...
            $user->saveSettings();
        }

        // Add groups to the user
        $promotions = [
            'sysop',
            'bureaucrat',
            //'interface-admin',
            //'bot'
        ];
        array_map( [ $user, 'addGroup' ], $promotions );

        // Send a password reset email (If password not specified)
        $sendResetPasswordEmail = $email && !$password;
        if($sendResetPasswordEmail){
            $services = \MediaWiki\MediaWikiServices::getInstance();
            $passwordReset = $services->getPasswordReset();
            $resetStatus = $passwordReset->execute( $user, $username, $email );
            // TODO check $resetStatus?
        }

        // Update the site stats
        $ssu = \SiteStatsUpdate::factory( [ 'users' => 1 ] );
        $ssu->doUpdate();

        // Return an API Result
        $this->getResult()->addValue(
            null,
            $this->getModuleName(),
            [
                'success' => '1',
                'userId' => $user->getId(),
                'userSet' => $username,
                'emailSet' => (bool)$email,
                'passwordSet' => (bool)$password,
                'emailSent' => $sendResetPasswordEmail,
            ]
        );
    }
    private function addFailedNote( $note ) {
        $this->getResult()->addValue(
            null,
            $this->getModuleName(),
            [
                'success' => '0',
                'note' => $note,
            ]
        );
    }
    public function getAllowedParams() {
        return [
            'username' => [
                ParamValidator::PARAM_TYPE => 'string',
                // Always require a username, always provided by default, and can be provided for sandboxes too?
                ParamValidator::PARAM_REQUIRED => true
            ],
            'email' => [
                ParamValidator::PARAM_TYPE => 'string',
                // Don't require, as for sandboxes we will not have any emails...
                ParamValidator::PARAM_REQUIRED => false
            ],
            'password' => [
                ParamValidator::PARAM_TYPE => 'string',
                // For sandboxes we want to specify a password, but for default behaviour we still want to do password reset emails...
                ParamValidator::PARAM_REQUIRED => false
            ],
        ];
    }

    public function initMainPage() {
        $user = WbStackPlatformReservedUser::getUser();
        $comment = \CommentStoreComment::newUnsavedComment('(automated) add example content');

		$title = \Title::newMainPage();
        $page = new \WikiPage($title);

        $text = <<<EOF
        = What do I need to know about how Wikibase works? =
        == Wikibase ecosystem ==
        There are many Wikibases in the ecosystem. The community around Wikibase includes Wikibase users, partners, volunteer developers and tool builders, forming the vibrant and diverse Wikibase Ecosystem. In this ecosystem, we imagine that one day all the Wikibase instances will be connected between themselves and back to Wikidata. 
         == How is information structured? ==
        Data is stored in Wikibase in the shape of Items. Each item is accorded its own page. Items are used to represent all the things in human knowledge, including topics, concepts, and objects. For example, the "1988 Summer Olympics", "love", "Elvis Presley", and "gorilla" can all be items. 
        Items are made up of Statements that describe detailed characteristics of an Item. A statement (graph format: Subject-Predicate-Object) is how the information we know about an item - the data we have about it - gets recorded in your Wikibase instance. 
        This happens by pairing a property with at least one value; this pair is at the heart of a statement. Statements also serve to connect items to each other, resulting in a linked data structure.
        
        The property in a statement describes the data value, and can be thought of as a category of data like "color", "population," or "Commons media" (files hosted on Wikimedia Commons). The value in the statement is the actual piece of data that describes the item. Each property has a data type which defines the kind of values allowed in statements with that property. For example, the property “date of birth” will only accept data in the format of a date.
        
        Example:
        In order to record information about the occupation of Marie Curie, you would need to add a statement to the item for Marie Curie, Marie Curie (Q7186). Using the property, occupation (P106), you could then add the value physicist (Q169470). You could also add the value chemist (Q593644). Note how both chemist and physicist are each their own item, thereby allowing Marie Curie to be linked to these items.
        
        How to create items, properties (what is a good property)
        Click "Create a new Item" on the menu to the left. You will be taken to a page that asks you to give the new item a label and a description. When you're done, click "Create". 
        
        Create the property with Special:NewProperty.
        Property entities can be edited like item entities with labels, descriptions, aliases, and statements.
        Property labels should be as unambiguous as possible so that it is clear to a user which property is the correct one to use when editing items and adding statements. Properties rarely refer to commonly known concepts but they are more constructs of the Wikidata with specific meanings. Unlike items, property labels must be unique.
        Property descriptions are less relevant for disambiguation but they should provide enough information about the scope and context of the property so that users understand appropriate usage of the property without having to consult additional help.
        Property aliases should include all alternative ways of referring to the property.
        Example:
        property: P161
        label: cast member
        description: actor performing live for a camera or audience
        aliases: film starring; actor; actress; starring
        [GOOD PLACE TO CONNECT TO TEMPLATES OF OTHER TEAM]
        
        What to not create but reuse from WD
        Not all items have to be created in your instance. Common concepts can be re-used from other Wikibases, mainly Wikidata. Consider only creating items that are unique to your dataset, or that need their own item page and statements. All items that you only plan on linking to in a statement, could be a link to the item on Wikidata.
        
        For example:
        Item: painting XYZ
        Statements: 
        Property: artist
        Value: Monet 
        Property: depicts
        Value: lilies
        Value: pond
        → these two values could link to Wikidata instead of being their own items on your instance
        Don’t panic -> links to help
        This is a lot of information and possibilities and might seem daunting. Don’t panic. There’s a lot of documentation for you to read, many other Wikibases you can look at to get inspired, a community that is happy to help out with any questions, and the development team who’s happy to support you where needed.
        
        
        
        
        
        == What do i need to know to prepare my data? ==
        === Data Import ===
        Instead of inserting your data manually you can make use of more efficient ways to integrate it in bulk.
        API
        The Wikibase API allows querying, adding, removing and editing information on Wikidata or any other Wikibase instance. A new version of the API is in the works with theREST API.
        Tools
        A variety of tools exist to fill your Wikibase with data and use the API:
        https://www.mediawiki.org/wiki/Wikibase/Importing
        QuickStatements
        WikibaseImport
        WikibaseIntegrator
        OpenRefine
        Wikibase seed data
        Native MediaWiki export/import
        RaiseWikibase
        wikibase-cli
        Examples
        Your choice of properties depends on the use case for your Wikibase. An art museum might want to index all exhibits and put the data in relation to each other.
        Example properties -> templates e.g.[ADD LINK]
        https://prop-explorer.toolforge.org/
        Example: Museum
        Instance of (type: Item)
        Title (type: String)
        Created by (type: Item)
        Depicts (type: item)
        Located at (type: item)
        Movement (type: item)
        Exhibited since (type: date)
        Example triples
        
        Item
        Property
        Value
        Mona Lisa
        Instance of
        painting
        Mona Lisa
        Title
        La Gioconda
        Mona Lisa
        Created by
        Leaonardo da Vinci
        Mona Lisa
        Depicts
        Lisa del giocondo, sky, body of water, bridge, …
        Mona Lisa
        Located at
        Louvre
        Mona Lisa
        Movement
        High renaissance
        Mona Lisa
        Exhibited since
        1797
        
        
        Example conversion of imaginary DB to triplets
        Exhibition object 24: 
        Painting, Name: Mona lisa, Painter: Leonardo da vinci, pained in 1500, hangs in louvre, uses oil paints
        
        (if you have your data in rows, maybe your columns could be your properties, and your row header is your item)
        
        
        
        
        
        source
        
        EOF;

        $content = \ContentHandler::makeContent( $text, $title );

		$updater = $page->newPageUpdater( $user );
        $updater->setContent( SlotRecord::MAIN, $content );
        $updater->setRcPatrolStatus( \RecentChange::PRC_PATROLLED );
        $newRev = $updater->saveRevision( $comment, EDIT_NEW);
    }
}
