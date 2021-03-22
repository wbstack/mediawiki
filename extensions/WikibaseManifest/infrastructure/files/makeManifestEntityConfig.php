<?php
$dbConnection = new mysqli(
	getenv( "DB_SERVER" ),
	getenv( "DB_USER" ),
	getenv( "DB_PASS" ),
	getenv( "DB_NAME" )
);

$P31_id = 'P31';
$P279_id = 'P279';

foreach ( [ $P31_id, $P279_id ] as $id ) {
	$stmt = $dbConnection->prepare( 'SELECT wbs_local_id, wbs_original_id FROM wbs_entity_mapping WHERE wbs_original_id = ?' );
	$stmt->bind_param( 's', $id );
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$mapping['properties'][$id] = $row['wbs_local_id'];
}

file_put_contents(
	'/var/www/html/LocalSettings.d/entityConfig.php', '<?php
	$wgWbManifestWikidataEntityMapping = ' .
	var_export( $mapping, true ) . ";\n"
);
