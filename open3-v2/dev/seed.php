<?php
/**
 * Entwicklungs-Fixtures für WordPress Playground (dev/blueprint*.json).
 *
 * Nicht Teil des Dist-Pakets (open3/dev/ ist export-ignore). Alle Funktionen sind
 * idempotent (Lookup über Slug), sodass ein zweiter Lauf keine Duplikate erzeugt.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'open3_register_projekt' ) ) {
	require_once dirname( __DIR__ ) . '/inc/cpt-projekt.php';
}
if ( ! function_exists( 'open3_projekt_meta_fields' ) ) {
	require_once dirname( __DIR__ ) . '/inc/meta-projekt.php';
}
if ( ! class_exists( 'Open3_Project_Migrator' ) ) {
	require_once dirname( __DIR__ ) . '/inc/migration/class-open3-project-migrator.php';
}

/**
 * Tagline der Live-Site.
 */
function open3_seed_tagline(): string {
	return 'Netzwerk zur Förderung von OpenSociety, OpenGov und OpenData in Österreich';
}

/**
 * Vollständige Fixtures anlegen.
 *
 * @param array<string,bool> $opts 'projekte' => false lässt die 23 Projekt-Posts aus (für blueprint-migration.json).
 */
function open3_seed_fixtures( array $opts = array() ): void {
	$opts = array_merge( array( 'projekte' => true ), $opts );

	if ( ! post_type_exists( 'projekt' ) ) {
		open3_register_projekt();
	}
	if ( ! function_exists( 'open3_register_projekt_meta' ) ) {
		require_once dirname( __DIR__ ) . '/inc/meta-projekt.php';
	}
	if ( ! registered_meta_key_exists( 'post', 'open3_jahr', 'projekt' ) ) {
		open3_register_projekt_meta();
	}
	open3_seed_projekttyp();

	$author     = open3_seed_author();
	$categories = open3_seed_categories();

	open3_seed_real_posts( $author, $categories );
	open3_seed_filler_posts( $author, $categories );
	open3_seed_termine( $author, $categories );
	open3_seed_pages( $author );
	if ( $opts['projekte'] ) {
		open3_seed_projekte( $author );
	}
	open3_seed_options();

	foreach ( Open3_Project_Migrator::setup_menus() as $line ) {
		open3_seed_log( $line );
	}
}

/**
 * Protokoll (nur CLI/runPHP-Ausgabe).
 *
 * @param string $line Text.
 */
function open3_seed_log( string $line ): void {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::log( $line );
		return;
	}
	if ( PHP_SAPI === 'cli' ) {
		echo $line . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI only.
	}
}

/**
 * Autor „Robert Seyfriedsberger“ (Nicename robert) sicherstellen.
 */
function open3_seed_author(): int {
	$user = get_user_by( 'slug', 'robert' );
	if ( $user instanceof WP_User ) {
		return (int) $user->ID;
	}
	$admin = get_user_by( 'id', 1 );
	if ( $admin instanceof WP_User ) {
		wp_update_user(
			array(
				'ID'            => 1,
				'user_nicename' => 'robert',
				'display_name'  => 'Robert Seyfriedsberger',
				'first_name'    => 'Robert',
				'last_name'     => 'Seyfriedsberger',
				'nickname'      => 'robert',
			)
		);
		return 1;
	}
	$id = wp_insert_user(
		array(
			'user_login'    => 'robert',
			'user_pass'     => wp_generate_password( 24 ),
			'user_nicename' => 'robert',
			'display_name'  => 'Robert Seyfriedsberger',
			'first_name'    => 'Robert',
			'last_name'     => 'Seyfriedsberger',
			'role'          => 'administrator',
		)
	);
	return is_wp_error( $id ) ? 1 : (int) $id;
}

/**
 * Kategorien (Slug => ID).
 *
 * @return array<string,int>
 */
function open3_seed_categories(): array {
	$spec = array(
		'news'          => 'News',
		'termine'       => 'Termine',
		'studien'       => 'Studien/Materialien',
		'medienspiegel' => 'Medienspiegel',
		'mehrgrips'     => 'mehrGRIPS',
	);
	$ids  = array();
	foreach ( $spec as $slug => $name ) {
		$existing = term_exists( $slug, 'category' );
		if ( is_array( $existing ) ) {
			$ids[ $slug ] = (int) $existing['term_id'];
			continue;
		}
		$created = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		if ( ! is_wp_error( $created ) ) {
			$ids[ $slug ] = (int) $created['term_id'];
		}
	}
	return $ids;
}

/**
 * Deutscher Fülltext (deterministisch je Seed).
 *
 * @param int $seed       Startwert.
 * @param int $paragraphs Anzahl Absätze.
 */
function open3_seed_text( int $seed, int $paragraphs = 3 ): string {
	$sentences = array(
		'Offene Daten sind die Grundlage für Transparenz, Innovation und Beteiligung.',
		'Verwaltungen, Zivilgesellschaft und Wirtschaft profitieren, wenn Informationen maschinenlesbar und frei nutzbar veröffentlicht werden.',
		'open3 begleitet seit 2010 Projekte, die zeigen, was mit Open Government Data möglich ist.',
		'Entscheidend ist nicht die Menge der Daten, sondern ihre Qualität, Aktualität und Lizenzierung.',
		'Ein gutes Datenportal beantwortet die Frage, wer welche Daten wann und in welchem Format bereitstellt.',
		'In Österreich hat sich in den letzten Jahren viel bewegt, doch der Weg zu einer offenen Verwaltung ist noch nicht zu Ende.',
		'Wir dokumentieren Erfahrungen, damit andere nicht bei null beginnen müssen.',
		'Visualisierungen machen Budgets, Umfragen und Statistiken für Menschen greifbar, die keine Tabellen lesen wollen.',
		'Jede Veröffentlichung sollte mit einer klaren Lizenz und einer Ansprechperson versehen sein.',
		'Wer Daten nutzt, gibt idealerweise Erkenntnisse und Werkzeuge an die Gemeinschaft zurück.',
		'Die Diskussion über Informationsfreiheit zeigt, wie eng Recht, Technik und Kultur zusammenhängen.',
		'Kleine Gemeinden können mit wenig Aufwand große Wirkung erzielen, wenn sie ihre Haushalte offenlegen.',
	);
	$count  = count( $sentences );
	$output = array();
	for ( $p = 0; $p < $paragraphs; $p++ ) {
		$parts = array();
		for ( $s = 0; $s < 4; $s++ ) {
			$parts[] = $sentences[ ( $seed * 7 + $p * 5 + $s * 3 ) % $count ];
		}
		$output[] = '<!-- wp:paragraph --><p>' . implode( ' ', $parts ) . '</p><!-- /wp:paragraph -->';
	}
	return implode( "\n\n", $output );
}

/**
 * Beitrag idempotent anlegen.
 *
 * @param array<string,mixed> $data slug,title,date,category,excerpt,content,author,tags,meta,comment_status.
 * @return int Post-ID.
 */
function open3_seed_post( array $data ): int {
	$existing = get_page_by_path( $data['slug'], OBJECT, 'post' );
	if ( $existing instanceof WP_Post ) {
		return (int) $existing->ID;
	}
	$post_id = wp_insert_post(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'post_name'      => $data['slug'],
			'post_title'     => $data['title'],
			'post_date'      => $data['date'] . ' 10:00:00',
			'post_author'    => $data['author'],
			'post_excerpt'   => $data['excerpt'] ?? '',
			'post_content'   => $data['content'],
			'post_category'  => array( $data['category'] ),
			'comment_status' => $data['comment_status'] ?? 'open',
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		return 0;
	}
	if ( ! empty( $data['tags'] ) ) {
		wp_set_post_tags( (int) $post_id, $data['tags'], false );
	}
	foreach ( (array) ( $data['meta'] ?? array() ) as $key => $value ) {
		update_post_meta( (int) $post_id, $key, $value );
	}
	return (int) $post_id;
}

/**
 * Die 12 echten Beiträge (5 + 3 aus dem Prototyp, 4 weitere).
 *
 * @param int               $author     Autor-ID.
 * @param array<string,int> $categories Kategorie-IDs.
 */
function open3_seed_real_posts( int $author, array $categories ): void {
	$posts = array(
		array(
			'slug'    => 'let-the-data-union-begin-our-submission-to-the-european-commission',
			'title'   => 'Let the Data Union Begin: Our Submission to the European Commission',
			'date'    => '2025-07-15',
			'cat'     => 'news',
			'excerpt' => 'At open3, we believe that the future of a democratic, innovative and resilient Europe depends on how we govern and use data today. That’s why we’ve taken part in the European Commission’s public consultation on the upcoming EU Data Union Strategy.',
			'tags'    => array( 'Open Data', 'EU', 'Data Union' ),
		),
		array(
			'slug'    => 'ein-register-viele-chancen-warum-oesterreich-ein-zentrales-adressregister-mit-open-webservice-braucht',
			'title'   => 'Ein Register, viele Chancen: Warum Österreich ein zentrales Adressregister mit Open Webservice braucht',
			'date'    => '2025-06-24',
			'cat'     => 'news',
			'excerpt' => 'Die unabhängige Initiative mehrGRIPS hat inmitten der Budgetkrise ein bemerkenswert konstruktives Papier vorgelegt: 12 Chancen für Österreich. Einer der Vorschläge: der Aufbau eines zentralen österreichischen Adressregisters samt offenem Webservice.',
			'tags'    => array( 'Adressregister', 'mehrGRIPS', 'Open Data' ),
		),
		array(
			'slug'    => 'veranstaltungstipp-zu-mehrgrips-am-27-1-in-der-urania-wien',
			'title'   => 'Veranstaltungstipp zu #mehrGRIPS am 27.1. in der Urania, Wien',
			'date'    => '2025-01-17',
			'cat'     => 'mehrgrips',
			'excerpt' => 'Ist die Politik noch in der Lage, die großen Zukunftsfragen zu lösen? Die Initiative „MEHR GRIPS“ versucht genau das – am 27.1. in der VHS Wiener Urania mit Johannes Kopf, Sabine Herlitschka und Judith Kohlenberger.',
			'tags'    => array( 'mehrGRIPS', 'Veranstaltung' ),
		),
		array(
			'slug'    => 'digital-governance-in-oesterreich-braucht-es-einen-digitalrat',
			'title'   => 'Digital Governance in Österreich: braucht es einen Digitalrat?',
			'date'    => '2024-11-29',
			'cat'     => 'mehrgrips',
			'excerpt' => 'Die digitale Transformation stellt Österreich und Europa vor immense Herausforderungen. Eine von A1 in Auftrag gegebene Studie schlägt die Einrichtung eines Digitalrats als zentrale Koordinationsstelle vor.',
			'tags'    => array( 'Digitalrat', 'Governance' ),
		),
		array(
			'slug'    => 'das-vertrauen-zurueckgewinnen-mehrgrips-gastkommentar-im-kurier',
			'title'   => '„Das Vertrauen zurückgewinnen“ – #mehrGRIPS Gastkommentar im Kurier',
			'date'    => '2024-09-24',
			'cat'     => 'news',
			'excerpt' => 'Gastkommentar der Initiative #mehrGRIPS im Kurier.',
			'tags'    => array( 'mehrGRIPS', 'Medien' ),
		),
		array(
			'slug'    => 'parlamentspodcast-offen-gesagt-brauchen-wir-mehr-transparenz',
			'title'   => 'Parlamentspodcast Offen gesagt: Brauchen wir mehr Transparenz?',
			'date'    => '2024-04-18',
			'cat'     => 'news',
			'excerpt' => 'Im Parlamentspodcast „Offen gesagt“ geht es um Informationsfreiheit, offene Daten und die Frage, wie viel Transparenz eine Demokratie braucht.',
			'tags'    => array( 'Transparenz', 'Parlament' ),
		),
		array(
			'slug'    => 'opendata-buddy',
			'title'   => 'Opendata Buddy: die Magie von KI zum Finden offener Datensätze nutzen',
			'date'    => '2023-11-14',
			'cat'     => 'news',
			'excerpt' => 'Der Opendata Buddy hilft mit KI dabei, passende offene Datensätze auf data.gv.at und darüber hinaus zu finden.',
			'tags'    => array( 'KI', 'Open Data' ),
		),
		array(
			'slug'    => 'wenn-ki-auf-politik-trifft-ein-streitgespraech-zum-informationsfreiheitsgesetz',
			'title'   => 'Wenn KI auf Politik trifft: Ein Streitgespräch zum Informationsfreiheitsgesetz',
			'date'    => '2023-10-05',
			'cat'     => 'termine',
			'excerpt' => 'Ein Streitgespräch über das Informationsfreiheitsgesetz – mit einer künstlichen Intelligenz als Gegenüber.',
			'tags'    => array( 'Informationsfreiheit', 'KI' ),
		),
		array(
			'slug'    => 'open-data-stammtisch-wien-fruehjahr-2024',
			'title'   => 'Open Data Stammtisch Wien: Frühjahrstreffen 2024',
			'date'    => '2024-02-20',
			'cat'     => 'termine',
			'excerpt' => 'Offener Austausch für alle, die mit offenen Daten arbeiten oder damit beginnen wollen – im Frühjahr wieder in Wien.',
			'tags'    => array( 'Veranstaltung', 'Wien' ),
		),
		array(
			'slug'    => 'studie-open-data-wertschoepfung-in-oesterreich-2024',
			'title'   => 'Studie: Wertschöpfung durch Open Data in Österreich 2024',
			'date'    => '2024-05-07',
			'cat'     => 'studien',
			'excerpt' => 'Eine neue Studie beziffert den volkswirtschaftlichen Nutzen offener Verwaltungsdaten in Österreich und benennt die größten Hürden.',
			'tags'    => array( 'Studie', 'Open Data' ),
		),
		array(
			'slug'    => 'open3-im-standard-open-data-als-standortfaktor',
			'title'   => 'open3 im Standard: Open Data als Standortfaktor',
			'date'    => '2023-06-12',
			'cat'     => 'medienspiegel',
			'excerpt' => 'Der Standard berichtet über die Rolle offener Daten für den Wirtschaftsstandort – mit Stimmen aus dem open3-Netzwerk.',
			'tags'    => array( 'Medien' ),
		),
		array(
			'slug'    => 'erstmals-in-oesterreich-hochwasserschutz-per-open-data',
			'title'   => 'Erstmals in Österreich: Hochwasserschutz per Open Data',
			'date'    => '2013-10-15',
			'cat'     => 'news',
			'excerpt' => 'Kremsmünster stellt Pegelstände als offene Daten bereit – und ermöglicht damit Warn-Apps für die Bevölkerung.',
			'tags'    => array( 'Open Data', 'Kremsmünster' ),
		),
	);

	$first_id = 0;
	foreach ( $posts as $index => $post ) {
		$id = open3_seed_post(
			array(
				'slug'     => $post['slug'],
				'title'    => $post['title'],
				'date'     => $post['date'],
				'category' => $categories[ $post['cat'] ] ?? (int) get_option( 'default_category' ),
				'excerpt'  => $post['excerpt'],
				'content'  => '<!-- wp:paragraph --><p>' . $post['excerpt'] . '</p><!-- /wp:paragraph -->' . "\n\n" . open3_seed_text( $index + 1, 4 ),
				'author'   => $author,
				'tags'     => $post['tags'],
			)
		);
		if ( 0 === $index ) {
			$first_id = $id;
		}
	}

	if ( $first_id > 0 ) {
		open3_seed_comments(
			$first_id,
			array(
				array( 'Maria Huber', 'maria@example.at', 'Danke für die Einreichung – genau solche Stimmen braucht die Konsultation.' ),
				array( 'Thomas Berger', 'thomas@example.at', 'Spannend wäre, wie sich die Data Union zu bestehenden nationalen Portalen verhält.' ),
				array( 'Anna Leitner', 'anna@example.at', 'Gibt es die Einreichung auch als PDF zum Nachlesen?' ),
			),
			'2025-07-16 09:00:00'
		);
	}
}

/**
 * 50 Füllbeiträge („Beitrag 13 … 62“) über 2011–2024 und alle Kategorien.
 *
 * @param int               $author     Autor-ID.
 * @param array<string,int> $categories Kategorie-IDs.
 */
function open3_seed_filler_posts( int $author, array $categories ): void {
	$topics = array(
		'Open Data in der Gemeinde',
		'Neues vom Datenportal',
		'Rückblick auf das Community-Treffen',
		'Was ein offener Haushalt bringt',
		'Lizenzfragen kurz erklärt',
		'Transparenz im Alltag',
		'Werkzeuge für offene Daten',
		'Aus dem Netzwerk',
		'Datenqualität als Daueraufgabe',
		'Ein Blick über die Grenze',
	);
	for ( $i = 13; $i <= 62; $i++ ) {
		$year  = 2011 + ( ( $i - 13 ) % 14 );
		$month = 1 + ( ( $i * 5 ) % 12 );
		$day   = 1 + ( ( $i * 3 ) % 27 );
		$bucket = $i % 25;
		if ( $bucket < 15 ) {
			$cat = 'news';
		} elseif ( $bucket < 20 ) {
			$cat = 'termine';
		} elseif ( $bucket < 23 ) {
			$cat = 'studien';
		} elseif ( 23 === $bucket ) {
			$cat = 'medienspiegel';
		} else {
			$cat = 'mehrgrips';
		}
		$topic = $topics[ $i % count( $topics ) ];
		open3_seed_post(
			array(
				'slug'     => sprintf( 'beitrag-%d', $i ),
				'title'    => sprintf( 'Beitrag %d: %s', $i, $topic ),
				'date'     => sprintf( '%04d-%02d-%02d', $year, $month, $day ),
				'category' => $categories[ $cat ] ?? (int) get_option( 'default_category' ),
				'excerpt'  => sprintf( 'Fülltext Nummer %d zum Thema „%s“, damit Pagination, Archive und Sidebar realistische Mengen zeigen.', $i, $topic ),
				'content'  => open3_seed_text( $i, 3 ),
				'author'   => $author,
				'tags'     => array(),
				'meta'     => array( '_open3_fixture' => '1' ),
			)
		);
	}
}

/**
 * Termine (openEvents): 10 vergangene Veranstaltungen aus dem Prototyp (dc:490-499), 2 kommende,
 * plus Termin-Meta auf den beiden echten Termine-Beiträgen. Idempotent (Meta wird immer gesetzt).
 *
 * @param int               $author     Autor-ID.
 * @param array<string,int> $categories Kategorie-IDs.
 */
function open3_seed_termine( int $author, array $categories ): void {
	$category = $categories['termine'] ?? (int) get_option( 'default_category' );

	// slug, Titel, Termin (Y-m-d\TH:i), Ort, Link, Beitragsdatum (Y-m-d).
	$termine = array(
		array( 'open-government-data-konferenz-2012-in-linz', 'Open Government Data Konferenz 2012 in Linz', '2012-06-26T09:00', 'Linz', '', '2012-06-12' ),
		array( 'open-week-linz-open-source-und-recht-und-apps4linz-preisverleihung', 'Open Week Linz: „Open Source und Recht“ und Apps4Linz Preisverleihung', '2012-05-22T18:00', 'Linz', '', '2012-05-08' ),
		array( '6-ogd-wien-plattform-treffen', '6. OGD Wien-Plattform Treffen', '2012-03-29T14:00', 'Wien', '', '2012-03-15' ),
		array( 'opencamp-2012-in-graz', 'OpenCamp 2012 in Graz', '2012-03-17T10:00', 'Graz', '', '2012-03-03' ),
		array( 'hackathon-in-linz', 'Hackathon in Linz', '2012-02-03T09:00', 'Linz', '', '2012-01-20' ),
		array( 'international-open-data-hackathon', 'International Open Data Hackathon', '2011-12-03T10:00', 'Wien', '', '2011-11-19' ),
		array( 'gov20camp-vienna-2011', 'Gov2.0Camp Vienna 2011', '2011-12-02T09:00', 'Wien', '', '2011-11-18' ),
		array( 'open-geo-governmentdata', 'Open(Geo)GovernmentData', '2011-11-28T18:00', 'Wien', '', '2011-11-14' ),
		array( 'ogd-2011-open-government-data-conference-2011', 'OGD 2011: Open Government Data Conference 2011', '2011-06-16T09:00', 'Wien', '', '2011-06-02' ),
		array( 'gov20camp-2010-wiener-planungswerkstatt', 'Gov2.0Camp 2010: Wiener Planungswerkstatt', '2010-12-03T09:00', 'Wien', '', '2010-11-19' ),
		// Zwei kommende Termine relativ zu „jetzt“, damit der Fixture immer den Zweig
		// „Nächste Veranstaltungen“ bedient (published, Termin in der Zukunft).
		array( 'open3-netzwerktreffen-herbst-2026', 'open3 Netzwerktreffen Herbst 2026', (string) wp_date( 'Y-m-d\TH:i', strtotime( '+5 weeks 18:00' ) ), 'Wien', home_url( '/newsletter' ), (string) wp_date( 'Y-m-d', strtotime( '-7 days' ) ) ),
		array( 'open-data-werkstatt-linz-2026', 'Open Data Werkstatt Linz: Offene Haushalte in der Praxis', (string) wp_date( 'Y-m-d\TH:i', strtotime( '+10 weeks 09:30' ) ), 'Linz', 'https://www.data.gv.at/', (string) wp_date( 'Y-m-d', strtotime( '-5 days' ) ) ),
	);

	foreach ( $termine as $index => $row ) {
		list( $slug, $title, $datum, $ort, $link, $post_date ) = $row;
		$post_id = open3_seed_post(
			array(
				'slug'     => $slug,
				'title'    => $title,
				'date'     => $post_date,
				'category' => $category,
				'excerpt'  => sprintf( 'Veranstaltungshinweis: %s in %s.', $title, $ort ),
				'content'  => '<!-- wp:paragraph --><p>' . sprintf( 'Veranstaltungshinweis: %s in %s.', $title, $ort ) . '</p><!-- /wp:paragraph -->' . "\n\n" . open3_seed_text( 300 + $index, 2 ),
				'author'   => $author,
				'tags'     => array( 'Veranstaltung' ),
				'meta'     => array( '_open3_fixture' => '1' ),
			)
		);
		open3_seed_termin_meta( $post_id, $datum, $ort, $link );
	}

	// Termin-Meta auf den beiden echten Termine-Beiträgen (open3_seed_real_posts).
	$real = array(
		'wenn-ki-auf-politik-trifft-ein-streitgespraech-zum-informationsfreiheitsgesetz' => array( '2023-10-19T19:00', 'Wien', '' ),
		'open-data-stammtisch-wien-fruehjahr-2024'                                        => array( '2024-03-12T18:30', 'Wien', '' ),
	);
	foreach ( $real as $slug => $meta ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );
		if ( $post instanceof WP_Post ) {
			open3_seed_termin_meta( (int) $post->ID, $meta[0], $meta[1], $meta[2] );
		}
	}
}

/**
 * Termin-Meta setzen (leere Werte löschen).
 *
 * @param int    $post_id Beitrags-ID.
 * @param string $datum   Y-m-d\TH:i.
 * @param string $ort     Ort.
 * @param string $link    Anmeldelink.
 */
function open3_seed_termin_meta( int $post_id, string $datum, string $ort, string $link ): void {
	if ( $post_id <= 0 ) {
		return;
	}
	foreach ( array(
		'open3_termin_datum' => $datum,
		'open3_termin_ort'   => $ort,
		'open3_termin_link'  => $link,
	) as $key => $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}

/**
 * Seite idempotent anlegen.
 *
 * @param array<string,mixed> $data slug,title,content,author,parent,menu_order,status,comment_status,template,excerpt.
 */
function open3_seed_page( array $data ): int {
	$path     = '' !== ( $data['path'] ?? '' ) ? $data['path'] : $data['slug'];
	$existing = get_page_by_path( $path, OBJECT, 'page' );
	if ( $existing instanceof WP_Post ) {
		return (int) $existing->ID;
	}
	$post_id = wp_insert_post(
		array(
			'post_type'      => 'page',
			'post_status'    => $data['status'] ?? 'publish',
			'post_name'      => $data['slug'],
			'post_title'     => $data['title'],
			'post_content'   => $data['content'],
			'post_excerpt'   => $data['excerpt'] ?? '',
			'post_author'    => $data['author'],
			'post_parent'    => (int) ( $data['parent'] ?? 0 ),
			'menu_order'     => (int) ( $data['menu_order'] ?? 0 ),
			'post_date'      => $data['date'] ?? current_time( 'mysql' ),
			'comment_status' => $data['comment_status'] ?? 'closed',
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		return 0;
	}
	if ( ! empty( $data['template'] ) ) {
		update_post_meta( (int) $post_id, '_wp_page_template', $data['template'] );
	}
	return (int) $post_id;
}

/**
 * Titel der 21 Grundlagen-Kindseiten (Prototyp-Reihenfolge).
 *
 * @return array<int,string>
 */
function open3_seed_grundlagen_titles(): array {
	return array(
		'Einleitung',
		'Open Government einfach erklärt (Video)',
		'openLinks – Linkverzeichnis',
		'Open Government Datenprinzipien',
		'Um welche Daten geht es?',
		'Open Data Loop – Nutzen und Kosten',
		'Open Data Lizenzen',
		'Der Transparenz-Kreislauf',
		'Three Laws of Open Government Data',
		'Stufen der Wiederverwendbarkeit',
		'Neues Zugangsmodell zu Daten',
		'Opendata Wertschöpfungsnetzwerk',
		'Potentiale und Risiken von Open Data',
		'Verzeichnis Applikationswettbewerbe',
		'Datenkatalog-Verzeichnis',
		'Data Quality: What is It?',
		'Leitfaden zum Veröffentlichen von Daten',
		'Potentielle OGD-Formate',
		'Stufenmodell Open Government',
		'Ausgewählte Studien und Artikel',
		'UNECE Aarhus Convention',
	);
}

/**
 * Top-Level-Seiten und Grundlagen-Kindseiten.
 *
 * @param int $author Autor-ID.
 */
function open3_seed_pages( int $author ): void {
	$pages = array(
		'newsletter'            => array( 'Newsletter', '[newsletter_form]' ),
		'suche'                 => array( 'Suche', '<!-- wp:paragraph --><p>Durchsuchen Sie Beiträge, Projekte und Grundlagen.</p><!-- /wp:paragraph -->' ),
		'datenschutzerklaerung' => array( 'Datenschutzerklärung', open3_seed_text( 101, 4 ) ),
		'medienspiegel'         => array( 'Medienspiegel', '<!-- wp:paragraph --><p>Berichte über open3 und offene Daten in den Medien.</p><!-- /wp:paragraph -->' . "\n\n" . open3_seed_text( 102, 2 ) ),
		'uber-open3'            => array(
			'Open Society, Open Government, Open Data',
			'<!-- wp:paragraph --><p>Der Verein Open3 steht für Open Society, Open Government und Open Data – die drei Dimensionen, die in einem modernen Staat zu hoher <strong>Transparenz</strong>, <strong>Partizipation</strong> und <strong>Partizipationsfähigkeit</strong> und Möglichkeiten der <strong>Kollaboration</strong> zwischen verschiedenen Stakeholdern führen.</p><!-- /wp:paragraph -->' . "\n\n"
			. '<!-- wp:paragraph --><p>Open3 ist ein offenes, gemeinnütziges Netzwerk zur Förderung dieses Ansatzes, der technische wie politikwissenschaftliche und sozio-technische Phänomene verbinden möchte. Seit seiner Gründung Anfang 2010 hat Open3 bereits mehrere <a href="/projekte"><strong>anwendungsorientierte Projekte</strong></a> umgesetzt und hat auch einen weiteren Tätigkeitsbereich eröffnet – die <strong>wissenschaftliche Auseinandersetzung</strong>.</p><!-- /wp:paragraph -->' . "\n\n"
			. '<!-- wp:paragraph --><p>Open3 versucht sich als Intermediär zwischen den beteiligten Gruppen Politik–Verwaltung–Bevölkerung–Wirtschaft zu positionieren und den <strong>Wissenstransfer</strong> in alle Richtungen zu ermöglichen. Der Verein ist eine <strong>Non-Profit-Organisation</strong>, die Mitglieder verfolgen keine finanziellen Interessen, sondern bringen gemäß der Diktion von „Open“ gerne ihre Expertise in der Community ein.</p><!-- /wp:paragraph -->',
		),
		'opennetwork'           => array( 'openNetwork', '<!-- wp:paragraph --><p>Netzwerktätigkeit auf der Plattform: Kontakte, Partner und Initiativen.</p><!-- /wp:paragraph -->' . "\n\n" . open3_seed_text( 104, 2 ) ),
		'grundlagen'            => array(
			'Grundlageninformationen zu openSociety, openGovernment und openData',
			'',
			'Einstieg, Prinzipien, Lizenzen, Formate und ausgewählte Studien – als fortlaufendes Nachschlagewerk gepflegt.',
		),
		'openevents'            => array(
			'Veranstaltungen rund um Open Data und Open Government',
			'<!-- wp:paragraph --><p>openEvents sammelt Termine aus dem open3-Netzwerk: Konferenzen, Community-Treffen, Hackathons und Vorträge zu Open Government und Open Data. Hinweise auf weitere Veranstaltungen nehmen wir gerne per E-Mail entgegen.</p><!-- /wp:paragraph -->',
		),
		'impressum'             => array( 'Impressum', '<!-- wp:paragraph --><p>open3 – Netzwerk zur Förderung von OpenSociety, OpenGov und OpenData in Österreich. Kontakt: office@open3.at</p><!-- /wp:paragraph -->' ),
	);
	$ids = array();
	foreach ( $pages as $slug => $row ) {
		$ids[ $slug ] = open3_seed_page(
			array(
				'slug'    => $slug,
				'title'   => $row[0],
				'content' => $row[1],
				'excerpt' => $row[2] ?? '',
				'author'  => $author,
			)
		);
	}

	$parent = $ids['grundlagen'] ?? 0;
	if ( $parent > 0 ) {
		foreach ( open3_seed_grundlagen_titles() as $index => $title ) {
			$slug = 'openLinks – Linkverzeichnis' === $title ? 'openlinks' : sanitize_title( $title );
			open3_seed_page(
				array(
					'slug'       => $slug,
					'path'       => 'grundlagen/' . $slug,
					'title'      => $title,
					'content'    => open3_seed_text( 200 + $index, 3 ),
					'author'     => $author,
					'parent'     => $parent,
					'menu_order' => $index + 1,
				)
			);
		}
	}
}

/**
 * Jahr und Kurzbeschreibung je Projekt-Slug.
 *
 * @return array<string,array{0:string,1:string}>
 */
function open3_seed_projekt_details(): array {
	return array(
		'regierungsprogramm-2020-2024'      => array( '2020', 'Das Regierungsprogramm als barrierefreies, maschinenlesbares Dokument – durchsuchbar, verlinkbar, weiterverwendbar.' ),
		'gruenbuch-projekte'                => array( '2021', 'Eine strukturierte Übersicht aller Grünbuch-Projekte des Bundesrates mit Status und Quellen.' ),
		'opendata-buddy'                    => array( '2023', 'Ein KI-Assistent, der beim Finden und Verstehen offener Datensätze hilft.' ),
		'create-camp-gemeinsam-mit-wiener-linien-mit-schwerpunkt-verkehr' => array( '2014', 'Ein Wochenende, an dem aus offenen Verkehrsdaten der Wiener Linien Prototypen entstanden.' ),
		'budgetvisualisierung-2011-2014'    => array( '2011', 'Der Bundesfinanzrahmen 2011–2014 inklusive Konsolidierungsmaßnahmen als interaktive Treemap.' ),
		'sparpaket'                         => array( '2010', 'Interaktive Treemaps zu Gesamthaushalt und Kürzungen des Sparpakets 2011–2014.' ),
		'einwaende-gegen-die-veroeffentlichung-von-open-data-und-bewaehrte-antworten-darauf' => array( '2015', 'Die häufigsten Einwände gegen Open Data – und Antworten, die sich in der Praxis bewährt haben.' ),
		'visualisierung-der-hypo-alpe-adria-banken-staatshilfen' => array( '2014', 'Wie viel Geld floss wann in die Hypo Alpe Adria? Eine Visualisierung der Staatshilfen.' ),
		'hochwasserschutz-per-open-data-in-kremsmuenster' => array( '2013', 'Pegelstände als offene Daten – und eine App, die bei Hochwasser warnt.' ),
		'wien-budget-2009-visualisierung'   => array( '2010', 'Die Ausgaben der Stadt Wien 2009 als interaktive Treemap.' ),
		'umfrage'                           => array( '2010', 'Welche Daten der öffentlichen Verwaltung sollten vorrangig im Internet bereitgestellt werden?' ),
		'woergl-haushalt-1949-2009-visualisierung' => array( '2011', 'Voranschläge und Haushaltsabschlüsse der Stadt Wörgl seit 1949 im Überblick.' ),
		'ngo-umfrage'                       => array( '2011', 'Was erwarten sich österreichische NGOs, NPOs und CSOs von Open Government und Open Data?' ),
		'ubahnaufzug-at'                    => array( '2011', 'Welche Aufzüge in Wiens U-Bahn-Stationen funktionieren gerade? 24 Stunden Mobilität durch offene Daten.' ),
		'datamaps-eu'                       => array( '2011', 'Ein Framework, um Daten schnell auf Karten zu bringen – map your data.' ),
		'umfrage-potentiale-von-open-government-fur-osterreichische-nationalratsabgeordnete' => array( '2012', 'Wie schätzen Nationalratsabgeordnete die Potentiale von Open Government ein?' ),
		'toilet-map-vienna'                 => array( '2012', 'Augmented-Reality-App aller öffentlichen WCs in Wien auf Basis von data.wien.gv.at.' ),
		'gluecksspiel-opendata-mashup'      => array( '2016', 'Glücksspielautomaten und Wettbüros auf einer Karte – ein Mashup aus offenen Daten.' ),
		'ozon-info-at-ozon-warnung-am-smartphone' => array( '2012', 'Ozonwerte in Echtzeit und Warnungen direkt am Smartphone.' ),
		'poetry-contest'                    => array( '2011', 'Der erste weltweite Open Government Poetry Contest – mit 23 Einreichungen.' ),
		'linz-open-data-apps-und-visualisierungen' => array( '2012', 'Apps und Visualisierungen auf Basis der Linzer Open-Data-Initiative, etwa die WLAN-Hotspot-Map.' ),
		'kremsmuenster-haushalt-2003-2012-visualisierung' => array( '2013', 'Der Haushalt der Marktgemeinde Kremsmünster 2003–2012 visualisiert.' ),
		'ogd-wien-adressservice-unterstuetzung-fuer-geocoder-php-bibliothek' => array( '2015', 'Ein Provider für die geocoder-php-Bibliothek, der das OGD-Wien-Adressservice nutzt.' ),
	);
}

/**
 * 23 Projekte inkl. Terme, Meta, Bildern und Poetry-Contest-Kommentaren.
 *
 * @param int $author Autor-ID.
 */
function open3_seed_projekte( int $author ): void {
	$projects = open3_migration_projects();
	$order    = open3_migration_order_map();
	$images   = open3_migration_image_map();
	$specials = open3_migration_specials();
	$details  = open3_seed_projekt_details();
	$migrator = new Open3_Project_Migrator(
		array(
			'images'        => 'remote',
			'image_timeout' => 10,
		)
	);

	foreach ( $projects as $slug => $row ) {
		$detail   = $details[ $slug ] ?? array( '2012', '' );
		$existing = get_page_by_path( $slug, OBJECT, 'projekt' );
		if ( $existing instanceof WP_Post ) {
			$post_id = (int) $existing->ID;
		} else {
			$post_id = (int) wp_insert_post(
				array(
					'post_type'      => 'projekt',
					'post_status'    => 'publish',
					'post_name'      => $slug,
					'post_title'     => $row[0],
					'post_excerpt'   => $detail[1],
					'post_content'   => '<!-- wp:paragraph --><p>' . $detail[1] . '</p><!-- /wp:paragraph -->' . "\n\n" . open3_seed_text( $order[ $slug ] + 300, 3 ),
					'post_author'    => $author,
					'post_date'      => sprintf( '%s-06-15 12:00:00', $detail[0] ),
					'menu_order'     => $order[ $slug ],
					'comment_status' => 'closed',
				)
			);
			if ( 0 === $post_id ) {
				continue;
			}
		}

		wp_set_object_terms( $post_id, $row[1], 'projekttyp', false );
		update_post_meta( $post_id, 'open3_jahr', $detail[0] );
		if ( 'visualisierung' === $row[1] ) {
			update_post_meta( $post_id, 'open3_lizenz', 'CC BY 3.0 AT' );
		}
		if ( in_array( $slug, array( 'sparpaket', 'wien-budget-2009-visualisierung', 'budgetvisualisierung-2011-2014' ), true ) ) {
			update_post_meta( $post_id, 'open3_technik', 'JavaScript InfoVis Toolkit (Treemap)' );
			update_post_meta( $post_id, 'open3_datenquelle', 'Offener Datenkatalog' );
		}
		if ( isset( $specials[ $slug ] ) ) {
			foreach ( $specials[ $slug ]['meta'] as $key => $value ) {
				if ( is_string( $value ) ) {
					$value = str_replace( '{home}', untrailingslashit( home_url() ), $value );
				}
				update_post_meta( $post_id, $key, $value );
			}
			if ( '' !== $specials[ $slug ]['comment_status'] ) {
				wp_update_post(
					array(
						'ID'             => $post_id,
						'comment_status' => $specials[ $slug ]['comment_status'],
					)
				);
			}
		}
		if ( isset( $images[ $slug ] ) ) {
			$migrator->attach_image( $post_id, $images[ $slug ] );
		}
		if ( 'poetry-contest' === $slug ) {
			open3_seed_poetry_comments( $post_id );
		}
	}
}

/**
 * 23 freigegebene Einreichungen auf poetry-contest.
 *
 * @param int $post_id Projekt- oder Seiten-ID.
 */
function open3_seed_poetry_comments( int $post_id ): void {
	$names    = array( 'Lena', 'Jakob', 'Sophie', 'Felix', 'Emma', 'Paul', 'Mia', 'Lukas', 'Hannah', 'David', 'Laura', 'Elias', 'Anna', 'Jonas', 'Marie', 'Noah', 'Lea', 'Tobias', 'Sarah', 'Simon', 'Julia', 'Moritz', 'Clara' );
	$comments = array();
	foreach ( $names as $index => $name ) {
		$comments[] = array(
			$name . ' ' . chr( 65 + ( $index % 26 ) ) . '.',
			strtolower( $name ) . $index . '@example.at',
			sprintf( "Einreichung %d\n\nOffene Daten, offener Blick,\nwer teilt, bekommt ein Stück zurück.\nTransparenz ist kein Geschenk –\nsie ist der Anfang vom Gedenk.", $index + 1 ),
		);
	}
	open3_seed_comments( $post_id, $comments, '2011-05-01 08:00:00' );
}

/**
 * Kommentare anlegen, bis die Zielanzahl erreicht ist.
 *
 * @param int                            $post_id  Post-ID.
 * @param array<int,array{0:string,1:string,2:string}> $comments [Name, E-Mail, Text].
 * @param string                         $start    Datum des ersten Kommentars.
 */
function open3_seed_comments( int $post_id, array $comments, string $start ): void {
	$existing = (int) get_comments(
		array(
			'post_id' => $post_id,
			'count'   => true,
			'status'  => 'approve',
		)
	);
	$target = count( $comments );
	if ( $existing >= $target ) {
		return;
	}
	$base = strtotime( $start );
	for ( $i = $existing; $i < $target; $i++ ) {
		$date = gmdate( 'Y-m-d H:i:s', $base + $i * 3600 * 5 );
		wp_insert_comment(
			array(
				'comment_post_ID'      => $post_id,
				'comment_author'       => $comments[ $i ][0],
				'comment_author_email' => $comments[ $i ][1],
				'comment_content'      => $comments[ $i ][2],
				'comment_approved'     => 1,
				'comment_date'         => $date,
				'comment_date_gmt'     => $date,
				'comment_type'         => 'comment',
			)
		);
	}
	wp_update_comment_count( $post_id );
}

/**
 * Site-Optionen der Live-Site.
 */
function open3_seed_options(): void {
	update_option( 'blogname', 'open3.at' );
	update_option( 'blogdescription', open3_seed_tagline() );
	update_option( 'posts_per_page', 5 );
	update_option( 'permalink_structure', '/%year%/%monthnum%/%postname%' );
	update_option( 'category_base', 'kategorie' );
	update_option( 'timezone_string', 'Europe/Vienna' );
	update_option( 'date_format', 'j. F Y' );
	update_option( 'show_on_front', 'posts' );
	update_option( 'thread_comments', 1 );

	$privacy = get_page_by_path( 'datenschutzerklaerung', OBJECT, 'page' );
	if ( $privacy instanceof WP_Post ) {
		update_option( 'page_for_privacy_policy', (int) $privacy->ID );
	}

	open3_seed_remove_default_content();
}

/**
 * Entfernt die WordPress-Standardinhalte („Hello world!“, „Sample Page“, „Privacy Policy“-Entwurf,
 * Kategorie „Uncategorized“), damit die Fixtures dem Live-Zustand entsprechen. Idempotent.
 */
function open3_seed_remove_default_content(): void {
	$news = get_term_by( 'slug', 'news', 'category' );
	if ( $news instanceof WP_Term ) {
		update_option( 'default_category', (int) $news->term_id );
	}

	foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page', 'privacy-policy' => 'page' ) as $slug => $type ) {
		$default = get_page_by_path( $slug, OBJECT, $type );
		if ( ! $default instanceof WP_Post ) {
			$default = get_posts(
				array(
					'name'        => $slug,
					'post_type'   => $type,
					'post_status' => array( 'publish', 'draft', 'auto-draft' ),
					'numberposts' => 1,
				)
			);
			$default = $default[0] ?? null;
		}
		if ( $default instanceof WP_Post && ! get_post_meta( $default->ID, '_open3_fixture', true ) ) {
			wp_delete_post( $default->ID, true );
			open3_seed_log( 'Standardinhalt entfernt: ' . $type . ' „' . $slug . '“' );
		}
	}

	$uncategorized = get_term_by( 'slug', 'uncategorized', 'category' );
	if ( $uncategorized instanceof WP_Term && $news instanceof WP_Term && (int) $uncategorized->term_id !== (int) $news->term_id ) {
		wp_delete_term( (int) $uncategorized->term_id, 'category', array( 'default' => (int) $news->term_id ) );
		open3_seed_log( 'Kategorie „Uncategorized“ entfernt (Standard: News).' );
	}
}

/**
 * Legacy-Struktur für den Migrationstest: Seite „projekte“ + 23 Kindseiten (page).
 */
function open3_seed_legacy_pages(): void {
	$author   = open3_seed_author();
	$projects = open3_migration_projects();
	$order    = open3_migration_order_map();
	$details  = open3_seed_projekt_details();
	$live_ids = open3_migration_live_ids();

	$templates = array(
		'sparpaket'                       => 'sparpaket.php',
		'wien-budget-2009-visualisierung' => 'wienbudget2009.php',
		'woergl-haushalt-1949-2009-visualisierung' => 'woergl-visualisierungen.php',
		'kremsmuenster-haushalt-2003-2012-visualisierung' => 'woergl-visualisierungen.php',
	);

	$parent = get_page_by_path( 'projekte', OBJECT, 'page' );
	if ( ! $parent instanceof WP_Post ) {
		$parent = get_page_by_path( Open3_Project_Migrator::LEGACY_PARENT_SLUG, OBJECT, 'page' );
	}
	if ( ! $parent instanceof WP_Post ) {
		$parent_id = open3_seed_page(
			array(
				'slug'    => 'projekte',
				'title'   => 'Projekte',
				'content' => '<!-- wp:paragraph --><p>Bisherige Projekte von open3. Idee für ein neues Projekt? Schreiben Sie an office@open3.at.</p><!-- /wp:paragraph -->',
				'author'  => $author,
			)
		);
	} else {
		$parent_id = (int) $parent->ID;
	}
	if ( 0 === $parent_id ) {
		return;
	}

	foreach ( $projects as $slug => $row ) {
		if ( post_type_exists( 'projekt' ) && get_page_by_path( $slug, OBJECT, 'projekt' ) instanceof WP_Post ) {
			continue;
		}
		$detail  = $details[ $slug ] ?? array( '2012', '' );
		$page_id = open3_seed_page(
			array(
				'slug'           => $slug,
				'path'           => 'projekte/' . $slug,
				'title'          => $row[0],
				'excerpt'        => $detail[1],
				'content'        => '<!-- wp:paragraph --><p>' . $detail[1] . '</p><!-- /wp:paragraph -->' . "\n\n" . open3_seed_text( $order[ $slug ] + 300, 3 ),
				'author'         => $author,
				'parent'         => $parent_id,
				'menu_order'     => 'sparpaket' === $slug ? 0 : $order[ $slug ],
				'date'           => sprintf( '%s-06-15 12:00:00', $detail[0] ),
				'comment_status' => 'closed',
				'template'       => $templates[ $slug ] ?? '',
			)
		);
		if ( $page_id > 0 ) {
			update_post_meta( $page_id, '_open3_live_id', (string) ( $live_ids[ $slug ] ?? 0 ) );
			if ( 'poetry-contest' === $slug ) {
				open3_seed_poetry_comments( $page_id );
			}
		}
	}
}

/**
 * Kurze Zusammenfassung nach einem Migrationslauf (für runPHP-Ausgabe).
 */
function open3_seed_migration_summary(): string {
	$projekte = get_posts(
		array(
			'post_type'   => 'projekt',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields'      => 'ids',
		)
	);
	$with_thumbnail = 0;
	foreach ( $projekte as $id ) {
		if ( has_post_thumbnail( (int) $id ) ) {
			++$with_thumbnail;
		}
	}
	$poetry   = get_page_by_path( 'poetry-contest', OBJECT, 'projekt' );
	$comments = $poetry instanceof WP_Post ? (int) get_comments(
		array(
			'post_id' => $poetry->ID,
			'count'   => true,
			'status'  => 'approve',
		)
	) : 0;
	$legacy   = get_page_by_path( Open3_Project_Migrator::LEGACY_PARENT_SLUG, OBJECT, 'page' );
	$hidden   = get_page_by_path( 'sparpaket', OBJECT, 'projekt' );

	return sprintf(
		"Projekte: %d | mit Beitragsbild: %d | Kommentare poetry-contest: %d | Elternseite: %s | sparpaket versteckt: %s\n",
		count( $projekte ),
		$with_thumbnail,
		$comments,
		$legacy instanceof WP_Post ? $legacy->post_status . ' (' . $legacy->post_name . ')' : 'nicht gefunden',
		$hidden instanceof WP_Post && '1' === open3_projekt_meta( $hidden->ID, 'open3_versteckt' ) ? 'ja' : 'nein'
	);
}
