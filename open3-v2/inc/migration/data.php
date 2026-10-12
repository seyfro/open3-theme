<?php
/**
 * Reine Datentabellen der Projekt-Migration (keine WordPress-Aufrufe).
 *
 * Quellen: docs/plan/03-migration-verifikation.md §1.4/§1.5,
 * Prototyp „open3 Neuentwurf.dc.html“ Zeilen 458-479 (Reihenfolge, Titel),
 * legacy_theme/projekt-explorer.php (Bilddateien).
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Basis-URL der Legacy-Explorer-Bilder (Remote-Import).
 */
function open3_migration_image_base(): string {
	return 'https://www.open3.at/wp-content/uploads/projekt-explorer/';
}

/**
 * Live-IDs der 23 Kindseiten und der Elternseite (nur Dokumentation/Report).
 *
 * @return array<string,int>
 */
function open3_migration_live_ids(): array {
	return array(
		'projekte'                          => 17418,
		'regierungsprogramm-2020-2024'      => 55179,
		'gruenbuch-projekte'                => 55192,
		'opendata-buddy'                    => 55311,
		'create-camp-gemeinsam-mit-wiener-linien-mit-schwerpunkt-verkehr' => 37320,
		'budgetvisualisierung-2011-2014'    => 17419,
		'sparpaket'                         => 14991,
		'einwaende-gegen-die-veroeffentlichung-von-open-data-und-bewaehrte-antworten-darauf' => 50594,
		'visualisierung-der-hypo-alpe-adria-banken-staatshilfen' => 49584,
		'hochwasserschutz-per-open-data-in-kremsmuenster' => 50514,
		'wien-budget-2009-visualisierung'   => 17913,
		'umfrage'                           => 8727,
		'woergl-haushalt-1949-2009-visualisierung' => 20690,
		'ngo-umfrage'                       => 20358,
		'ubahnaufzug-at'                    => 18303,
		'datamaps-eu'                       => 20868,
		'umfrage-potentiale-von-open-government-fur-osterreichische-nationalratsabgeordnete' => 23978,
		'toilet-map-vienna'                 => 24908,
		'gluecksspiel-opendata-mashup'      => 53713,
		'ozon-info-at-ozon-warnung-am-smartphone' => 25636,
		'poetry-contest'                    => 28824,
		'linz-open-data-apps-und-visualisierungen' => 28759,
		'kremsmuenster-haushalt-2003-2012-visualisierung' => 33560,
		'ogd-wien-adressservice-unterstuetzung-fuer-geocoder-php-bibliothek' => 51554,
	);
}

/**
 * Projekte in Prototyp-Reihenfolge: Slug => [Titel, Projekttyp-Slug, Kurztitel].
 *
 * „sparpaket“ (14991) ist im Prototyp nicht eigens gelistet; er teilt Position 5
 * mit „budgetvisualisierung-2011-2014“ und wird versteckt.
 *
 * @return array<string,array{0:string,1:string,2:string}>
 */
function open3_migration_projects(): array {
	return array(
		'regierungsprogramm-2020-2024'      => array( 'Barrierefreies und maschinenlesbares Österreichisches Regierungsprogramm 2020-2024', 'dokumentation', 'Regierungsprogramm' ),
		'gruenbuch-projekte'                => array( 'Übersicht der Grünbuch-Projekte des Bundesrates', 'dokumentation', 'Grünbuch' ),
		'opendata-buddy'                    => array( 'Opendata Buddy auf Basis von KI & GPTs', 'app', 'Opendata Buddy' ),
		'create-camp-gemeinsam-mit-wiener-linien-mit-schwerpunkt-verkehr' => array( 'Create Camp gemeinsam mit Wiener Linien mit Schwerpunkt „Verkehr“', 'veranstaltung', 'Create Camp' ),
		'budgetvisualisierung-2011-2014'    => array( 'Budgetvisualisierung 2011-2014', 'visualisierung', 'Sparpaket 2011–2014' ),
		'sparpaket'                         => array( 'Sparpaket', 'visualisierung', 'Sparpaket' ),
		'einwaende-gegen-die-veroeffentlichung-von-open-data-und-bewaehrte-antworten-darauf' => array( 'Einwände gegen die Veröffentlichung von Open Data und bewährte Antworten darauf', 'dokumentation', 'Einwände' ),
		'visualisierung-der-hypo-alpe-adria-banken-staatshilfen' => array( 'Visualisierung der Hypo-Alpe-Adria-Banken-Staatshilfen', 'visualisierung', 'Hypo Alpe Adria' ),
		'hochwasserschutz-per-open-data-in-kremsmuenster' => array( 'Hochwasserschutz per Open Data in Kremsmünster', 'app', 'Pegelstand Kremsmünster' ),
		'wien-budget-2009-visualisierung'   => array( 'Wien Budget 2009 Visualisierung', 'visualisierung', 'Wien Budget 2009' ),
		'umfrage'                           => array( 'Umfrage zu opendata', 'umfrage', 'Umfrage Datenarten' ),
		'woergl-haushalt-1949-2009-visualisierung' => array( 'Wörgl Haushalt 1949-2009 Visualisierung', 'visualisierung', 'Wörgl' ),
		'ngo-umfrage'                       => array( 'Umfrage „Potentiale von Open Government/Open Data für österreichische NGOs/NPOs/CSOs“', 'umfrage', 'Umfrage NGOs' ),
		'ubahnaufzug-at'                    => array( 'ubahnaufzug.at – 24 Stunden Mobilität durch opendata', 'app', 'ubahnaufzug.at' ),
		'datamaps-eu'                       => array( 'DataMaps.eu – map your data', 'app', 'DataMaps.eu' ),
		'umfrage-potentiale-von-open-government-fur-osterreichische-nationalratsabgeordnete' => array( 'Umfrage „Potentiale von Open Government für österreichische Nationalratsabgeordnete“', 'umfrage', 'Umfrage Parlament' ),
		'toilet-map-vienna'                 => array( 'Toilet Map Vienna', 'app', 'Toilet Map Vienna' ),
		'gluecksspiel-opendata-mashup'      => array( 'Glücksspiel-OpenData-Mashup', 'app', 'Glücksspiel-Karte' ),
		'ozon-info-at-ozon-warnung-am-smartphone' => array( 'ozon-info.at – Ozon-Warnung am Smartphone', 'app', 'ozon-info.at' ),
		'poetry-contest'                    => array( 'Open Government Poetry Contest', 'veranstaltung', 'Poetry Contest' ),
		'linz-open-data-apps-und-visualisierungen' => array( 'Linz Open Data: Apps und Visualisierungen', 'app', 'WLAN Hotspot Map Linz' ),
		'kremsmuenster-haushalt-2003-2012-visualisierung' => array( 'Kremsmünster Haushalt 2003-2012 Visualisierung', 'visualisierung', 'Kremsmünster Haushalt' ),
		'ogd-wien-adressservice-unterstuetzung-fuer-geocoder-php-bibliothek' => array( 'OGD Wien-Adressservice-Unterstützung für geocoder-php Bibliothek', 'code', 'geocoder-php' ),
	);
}

/**
 * Slug => Projekttyp-Slug.
 *
 * @return array<string,string>
 */
function open3_migration_type_map(): array {
	$map = array();
	foreach ( open3_migration_projects() as $slug => $row ) {
		$map[ $slug ] = $row[1];
	}
	return $map;
}

/**
 * Slug => menu_order (Prototyp-Position; sparpaket teilt Position 5).
 *
 * @return array<string,int>
 */
function open3_migration_order_map(): array {
	$map      = array();
	$position = 0;
	foreach ( array_keys( open3_migration_projects() ) as $slug ) {
		if ( 'sparpaket' === $slug ) {
			$map[ $slug ] = 5;
			continue;
		}
		++$position;
		$map[ $slug ] = $position;
	}
	return $map;
}

/**
 * Slug => Dateiname in /wp-content/uploads/projekt-explorer/.
 *
 * @return array<string,string>
 */
function open3_migration_image_map(): array {
	return array(
		'opendata-buddy'                    => 'opendata-buddy.png',
		'gluecksspiel-opendata-mashup'      => 'gluecksspiel-projekt.png',
		'hochwasserschutz-per-open-data-in-kremsmuenster' => 'pegelstand-kremsmuenster.jpg',
		'create-camp-gemeinsam-mit-wiener-linien-mit-schwerpunkt-verkehr' => 'wlcc.png',
		'budgetvisualisierung-2011-2014'    => 'sparpaket11-14.png',
		'sparpaket'                         => 'sparpaket11-14.png',
		'toilet-map-vienna'                 => 'toiletmap.png',
		'umfrage-potentiale-von-open-government-fur-osterreichische-nationalratsabgeordnete' => 'umfrage-parlament.png',
		'datamaps-eu'                       => 'datamapseu.png',
		'ubahnaufzug-at'                    => 'ubahnaufzug.png',
		'ngo-umfrage'                       => 'umfrage-ngos.png',
		'linz-open-data-apps-und-visualisierungen' => 'fz-wlan-linz.png',
		'woergl-haushalt-1949-2009-visualisierung' => 'woergl.png',
		'umfrage'                           => 'umfrage-datenarten.png',
		'wien-budget-2009-visualisierung'   => 'budget-wien.png',
		'poetry-contest'                    => 'poetry-contest.png',
		'ozon-info-at-ozon-warnung-am-smartphone' => 'ozoninfo.png',
	);
}

/**
 * Sonderfälle je Slug. Meta-Werte mit „{home}“ werden durch home_url() ersetzt.
 *
 * @return array<string,array{meta:array<string,string|bool>,comment_status:string}>
 */
function open3_migration_specials(): array {
	return array(
		'sparpaket'                       => array(
			'meta'           => array( 'open3_versteckt' => true ),
			'comment_status' => '',
		),
		'budgetvisualisierung-2011-2014'  => array(
			'meta'           => array(
				'open3_app_url'       => '{home}/projekte/sparpaket',
				'open3_datensatz_url' => 'http://offener.datenkatalog.at/package/bundesbudget-2011-2014',
			),
			'comment_status' => '',
		),
		'wien-budget-2009-visualisierung' => array(
			'meta'           => array(
				'open3_datensatz_url' => 'http://offener.datenkatalog.at/package/wien-budget-2009-ausgaben',
			),
			'comment_status' => '',
		),
		'poetry-contest'                  => array(
			'meta'           => array( 'open3_comments_heading' => 'Alle 23 Einreichungen' ),
			'comment_status' => 'closed',
		),
	);
}

/**
 * Legacy-Seitentemplates, die per --reset-templates von allen Seiten entfernt werden.
 *
 * @return array<int,string>
 */
function open3_migration_legacy_templates(): array {
	return array(
		'woergl-visualisierungen.php',
		'poetry-slam.php',
		'sparpaket.php',
		'wienbudget2009.php',
		'delicious.php',
		'open3.php',
	);
}

/**
 * Menü-Spezifikation: Theme-Location => [name, items].
 *
 * Item-Typen: home (Custom-Link auf Startseite), post_type_archive (object), page (slug).
 *
 * @return array<string,array{name:string,items:array<int,array<string,string>>}>
 */
function open3_migration_menu_spec(): array {
	$items = array(
		array(
			'type'  => 'home',
			'title' => 'Blog',
		),
		array(
			'type'   => 'post_type_archive',
			'object' => 'projekt',
			'title'  => 'Projekte',
		),
		array(
			'type'  => 'page',
			'slug'  => 'grundlagen',
			'title' => 'Grundlagen',
		),
		array(
			'type'  => 'page',
			'slug'  => 'openevents',
			'title' => 'openEvents',
		),
		array(
			'type'  => 'page',
			'slug'  => 'opennetwork',
			'title' => 'openNetwork',
		),
		array(
			'type'  => 'page',
			'slug'  => 'medienspiegel',
			'title' => 'Medienspiegel',
		),
		array(
			'type'  => 'page',
			'slug'  => 'uber-open3',
			'title' => 'Über open3',
		),
	);

	$footer   = $items;
	$footer[] = array(
		'type'  => 'page',
		'slug'  => 'newsletter',
		'title' => 'Newsletter',
	);

	return array(
		'primary' => array(
			'name'  => 'Hauptnavigation',
			'items' => $items,
		),
		'footer'  => array(
			'name'  => 'Footer',
			'items' => $footer,
		),
	);
}
