<?php
/**
 * Default site content (French), installed by Apparence → Installation du site.
 * Everything here becomes regular, editable WordPress pages and posts.
 *
 * Tokens replaced at install time: {clinic} {phone} {email} {address}
 *
 * @package Rhomija
 */

defined( 'ABSPATH' ) || exit;

/**
 * Convert simple HTML (h2, h3, p, ul, shortcodes) to block-editor markup so
 * the content opens as normal blocks rather than a "Classic" block.
 */
function rh_to_blocks( $html ) {
	preg_match_all( '#<(h2|h3|p|ul)\b[^>]*>.*?</\1>|\[[a-z_]+[^\]]*\]#s', $html, $m );
	$out = array();
	foreach ( $m[0] as $el ) {
		if ( '[' === $el[0] ) {
			$out[] = "<!-- wp:shortcode -->\n" . $el . "\n<!-- /wp:shortcode -->";
		} elseif ( 0 === strpos( $el, '<h2' ) ) {
			$out[] = "<!-- wp:heading -->\n" . preg_replace( '#^<h2>#', '<h2 class="wp-block-heading">', $el ) . "\n<!-- /wp:heading -->";
		} elseif ( 0 === strpos( $el, '<h3' ) ) {
			$out[] = "<!-- wp:heading {\"level\":3} -->\n" . preg_replace( '#^<h3>#', '<h3 class="wp-block-heading">', $el ) . "\n<!-- /wp:heading -->";
		} elseif ( 0 === strpos( $el, '<ul' ) ) {
			$items = preg_replace( '#<li>(.*?)</li>#s', "<!-- wp:list-item -->\n<li>$1</li>\n<!-- /wp:list-item -->", substr( $el, 4, -5 ) );
			$out[] = "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . $items . "</ul>\n<!-- /wp:list -->";
		} else {
			$out[] = "<!-- wp:paragraph -->\n" . $el . "\n<!-- /wp:paragraph -->";
		}
	}
	return implode( "\n\n", $out );
}

function rh_fill_tokens( $text ) {
	return strtr(
		$text,
		array(
			'{clinic}'  => rh_info( 'clinic_name' ),
			'{phone}'   => rh_info( 'phone' ),
			'{email}'   => rh_info( 'email' ),
			'{address}' => rh_address_inline(),
		)
	);
}

/**
 * Top-level pages, keyed by role.
 */
function rh_default_pages() {
	return array(
		'accueil'     => array(
			'title'    => 'Accueil',
			'slug'     => 'accueil',
			'template' => '',
			'excerpt'  => '',
			'content'  => '',
		),
		'soins'       => array(
			'title'    => 'Nos soins',
			'slug'     => 'soins',
			'template' => 'page-templates/soins.php',
			'excerpt'  => 'Consultation, détartrage, soins des caries, extractions, implants, prothèses, esthétique, soins des enfants et urgences : tous les soins du cabinet dentaire du Dr Rhomija Nabil à Témara.',
			'content'  => '<p>Du simple contrôle aux traitements plus complets, le cabinet du Dr Rhomija Nabil vous accompagne à chaque étape. Chaque traitement commence par un examen et des explications claires, pour que vous puissiez décider en toute confiance.</p>',
		),
		'cabinet'     => array(
			'title'    => 'Le cabinet',
			'slug'     => 'le-cabinet',
			'template' => 'page-templates/cabinet.php',
			'excerpt'  => 'Découvrez le cabinet dentaire du Dr Rhomija Nabil à Témara : un lieu moderne et chaleureux, une salle de stérilisation dédiée et une radiographie panoramique sur place.',
			'content'  => '<h2>Bienvenue au cabinet</h2>'
				. '<p>Le cabinet se trouve au 3ᵉ étage de la Résidence Arrayhane, avenue Tarik Ibn Ziad, à Témara. Il dispose d’une salle de soins entièrement équipée, d’une salle de stérilisation dédiée et d’un appareil de radiographie panoramique.</p>'
				. '<p>Notre approche est simple : prendre le temps d’écouter, d’examiner et d’expliquer. Avant chaque traitement, vous savez ce qui est prévu, pourquoi, et ce que cela coûtera.</p>'
				. '<h2>Nos engagements</h2>'
				. '<ul><li>Des explications claires et un devis détaillé avant tout traitement important ;</li><li>Une hygiène rigoureuse : les instruments sont nettoyés et stérilisés dans une salle dédiée ;</li><li>Des soins doux, adaptés aux patients anxieux comme aux enfants ;</li><li>Une réponse rapide en cas de douleur ou d’urgence.</li></ul>',
		),
		'rendez-vous' => array(
			'title'    => 'Prendre rendez-vous',
			'slug'     => 'rendez-vous',
			'template' => 'page-templates/rendez-vous.php',
			'excerpt'  => 'Prenez rendez-vous en ligne au cabinet dentaire du Dr Rhomija Nabil à Témara : choisissez votre motif et un créneau libre, nous vous confirmons rapidement.',
			'content'  => '[rhomija_booking]',
		),
		'contact'     => array(
			'title'    => 'Contact & accès',
			'slug'     => 'contact',
			'template' => 'page-templates/contact.php',
			'excerpt'  => 'Adresse, téléphone, horaires et plan d’accès du cabinet dentaire du Dr Rhomija Nabil, Résidence Arrayhane, avenue Tarik Ibn Ziad à Témara.',
			'content'  => '<p>Une question, un devis, une urgence ? Appelez-nous, écrivez-nous, ou prenez directement rendez-vous en ligne.</p>',
		),
		'conseils'    => array(
			'title'    => 'Conseils dentaires',
			'slug'     => 'conseils',
			'template' => '',
			'excerpt'  => '',
			'content'  => '',
		),
		'mentions'    => array(
			'title'    => 'Mentions légales & confidentialité',
			'slug'     => 'mentions-legales',
			'template' => '',
			'excerpt'  => '',
			'content'  => '<h2>Éditeur du site</h2>'
				. '<p>{clinic}<br>{address}, Maroc<br>Téléphone : {phone} — E-mail : {email}</p>'
				. '<p>Directeur de la publication : Dr Rhomija Nabil, chirurgien-dentiste.</p>'
				. '<h2>Hébergement</h2>'
				. '<p>Hostinger International Ltd., 61 Lordou Vironos Street, 6023 Larnaca, Chypre — www.hostinger.com</p>'
				. '<h2>Informations médicales</h2>'
				. '<p>Les contenus de ce site sont fournis à titre d’information générale et ne remplacent pas une consultation. Pour tout problème de santé bucco-dentaire, prenez rendez-vous ou appelez le cabinet.</p>'
				. '<h2>Protection des données personnelles</h2>'
				. '<p>Les informations saisies dans le formulaire de prise de rendez-vous (nom, téléphone, e-mail facultatif, motif et message) sont utilisées uniquement pour organiser votre rendez-vous et vous recontacter. Elles sont réservées au cabinet et ne sont jamais vendues ni cédées à des tiers.</p>'
				. '<p>Conformément à la loi n° 09-08 relative à la protection des personnes physiques à l’égard du traitement des données à caractère personnel, vous disposez d’un droit d’accès, de rectification et d’opposition. Pour l’exercer, écrivez à {email} ou adressez-vous directement au cabinet.</p>'
				. '<p>Les demandes de rendez-vous sont conservées pendant la durée nécessaire à la gestion de votre prise en charge.</p>'
				. '<h2>Cookies et mesure d’audience</h2>'
				. '<p>Ce site peut utiliser un outil de mesure d’audience (Google Analytics) afin d’améliorer son contenu. Vous pouvez refuser ces cookies dans les réglages de votre navigateur.</p>'
				. '<h2>Crédits</h2>'
				. '<p>Photographies : {clinic}.</p>',
		),
	);
}

/**
 * Service pages (children of "Nos soins"). "booking" is the reason pre-selected in the booking form.
 */
function rh_default_services() {
	return array(
		array(
			'title'   => 'Consultation & bilan dentaire',
			'slug'    => 'consultation-bilan-dentaire',
			'icon'    => 'xray',
			'booking' => 'premiere-consultation-bilan',
			'excerpt' => 'Un examen complet des dents et des gencives, avec radiographie panoramique si besoin, pour faire le point et planifier vos soins.',
			'content' => '<p>La consultation est le point de départ de tout traitement. Que vous ressentiez une gêne précise ou que vous n’ayez pas consulté depuis longtemps, ce rendez-vous permet de faire un état des lieux complet de votre santé bucco-dentaire.</p>'
				. '<h2>Comment se déroule la consultation ?</h2>'
				. '<ul><li>Un échange sur vos antécédents, vos médicaments et vos attentes ;</li><li>L’examen des dents, des gencives et de la mâchoire ;</li><li>Si nécessaire, une radiographie panoramique réalisée directement au cabinet ;</li><li>Des explications claires sur ce que nous observons et sur les options possibles.</li></ul>'
				. '<h2>Et ensuite ?</h2>'
				. '<p>Si des soins sont nécessaires, nous vous proposons un plan de traitement par ordre de priorité, accompagné d’un devis. Vous prenez le temps de réfléchir : rien n’est engagé sans votre accord.</p>'
				. '<h2>À quelle fréquence consulter ?</h2>'
				. '<p>Un contrôle par an est recommandé pour la plupart des adultes, et plus souvent en cas de problème de gencives, de grossesse ou de traitement en cours. Pour les enfants, une première visite vers 3 ans permet de prendre de bonnes habitudes.</p>',
		),
		array(
			'title'   => 'Détartrage & soins des gencives',
			'slug'    => 'detartrage-soins-gencives',
			'icon'    => 'drop',
			'booking' => 'detartrage-nettoyage',
			'excerpt' => 'Éliminer le tartre, prévenir les caries et garder des gencives saines : le détartrage est la base d’une bouche en bonne santé.',
			'content' => '<p>Même avec un bon brossage, la plaque dentaire finit par durcir et se transformer en tartre, surtout derrière les dents du bas et au ras des gencives. Le tartre ne part pas à la brosse : seul un détartrage au cabinet permet de l’éliminer.</p>'
				. '<h2>Pourquoi faire un détartrage ?</h2>'
				. '<ul><li>Prévenir l’inflammation des gencives (gingivite) et le déchaussement des dents ;</li><li>Limiter la mauvaise haleine ;</li><li>Retrouver des dents plus nettes, débarrassées des colorations (thé, café, tabac).</li></ul>'
				. '<h2>Comment ça se passe ?</h2>'
				. '<p>Le détartrage se fait avec un appareil à ultrasons qui décroche le tartre en douceur, suivi d’un polissage. La séance dure généralement de 20 à 45 minutes et n’est pas douloureuse ; une légère sensibilité peut apparaître pendant un ou deux jours.</p>'
				. '<h2>Vos gencives saignent ?</h2>'
				. '<p>Des saignements au brossage sont souvent le signe d’une inflammation. Plus elle est prise en charge tôt, plus le traitement est simple : n’attendez pas pour consulter. Nous recommandons en général un détartrage une à deux fois par an, selon votre situation.</p>',
		),
		array(
			'title'   => 'Soins des caries & dévitalisation',
			'slug'    => 'soins-caries-devitalisation',
			'icon'    => 'tooth',
			'booking' => 'soin-de-carie',
			'excerpt' => 'Traiter une carie à temps, soigner le nerf d’une dent infectée et conserver vos dents naturelles le plus longtemps possible.',
			'content' => '<p>Une carie détruit la dent progressivement. Au début, elle ne fait pas mal ; puis apparaissent une sensibilité au froid ou au sucré, et enfin des douleurs. Plus elle est traitée tôt, plus le soin est simple et rapide.</p>'
				. '<h2>Le soin de carie</h2>'
				. '<p>Sous anesthésie locale, la partie abîmée est retirée puis la dent est reconstituée avec un matériau composite de la couleur de vos dents. Le plus souvent, une seule séance suffit.</p>'
				. '<h2>La dévitalisation (traitement de canal)</h2>'
				. '<p>Lorsque la carie atteint le nerf, ou en cas d’infection, il faut traiter l’intérieur de la dent : le nerf est retiré, les canaux sont nettoyés, désinfectés puis obturés. Ce traitement permet de garder votre dent au lieu de l’extraire.</p>'
				. '<ul><li>Il se fait sous anesthésie locale ;</li><li>Il peut nécessiter une ou plusieurs séances ;</li><li>Une couronne est souvent conseillée ensuite pour protéger la dent fragilisée.</li></ul>'
				. '<h2>Bon à savoir</h2>'
				. '<p>Une douleur qui disparaît ne veut pas dire que le problème est réglé : une dent infectée peut continuer à évoluer sans bruit. Consultez dès les premiers signes.</p>',
		),
		array(
			'title'   => 'Extractions & dents de sagesse',
			'slug'    => 'extractions-dents-de-sagesse',
			'icon'    => 'extraction',
			'booking' => 'extraction-dent-de-sagesse',
			'excerpt' => 'Quand une dent ne peut pas être conservée ou qu’une dent de sagesse pose problème : une extraction en douceur, sous anesthésie locale.',
			'content' => '<p>Notre priorité est toujours de conserver vos dents. Mais lorsqu’une dent est trop abîmée, infectée ou mal placée, l’extraction peut devenir la meilleure solution.</p>'
				. '<h2>Les dents de sagesse</h2>'
				. '<p>Elles apparaissent souvent entre 17 et 25 ans. Quand elles manquent de place, elles peuvent provoquer douleurs, gonflements, infections ou caries sur les dents voisines. Une radiographie permet de savoir s’il faut les retirer, et à quel moment.</p>'
				. '<h2>Comment se passe une extraction ?</h2>'
				. '<ul><li>Une anesthésie locale efficace : l’intervention elle-même n’est pas douloureuse ;</li><li>Des gestes précis pour préserver l’os et la gencive ;</li><li>Des consignes écrites et, si nécessaire, une ordonnance pour les jours suivants.</li></ul>'
				. '<h2>Après l’extraction</h2>'
				. '<p>Le premier jour, évitez de cracher, de fumer et de boire chaud. Un léger gonflement est normal pendant 2 à 3 jours. Si la douleur augmente après quelques jours, appelez-nous.</p>'
				. '<p>Une dent manquante peut ensuite être remplacée, par exemple par un implant ou un bridge : nous en parlerons ensemble.</p>',
		),
		array(
			'title'   => 'Implants dentaires',
			'slug'    => 'implants-dentaires',
			'icon'    => 'implant',
			'booking' => 'implant-dentaire',
			'excerpt' => 'Remplacer une ou plusieurs dents de façon fixe et durable, avec un résultat naturel et sans abîmer les dents voisines.',
			'content' => '<p>L’implant dentaire est une petite racine artificielle en titane placée dans l’os de la mâchoire. Une fois intégré à l’os, il porte une couronne, un bridge, ou stabilise une prothèse amovible. C’est aujourd’hui la solution de référence pour remplacer une dent manquante sans toucher aux dents voisines.</p>'
				. '<h2>Les avantages</h2>'
				. '<ul><li>Une dent fixe, qui ressemble à une dent naturelle et se brosse comme elle ;</li><li>Les dents voisines sont préservées : pas besoin de les tailler ;</li><li>L’os de la mâchoire reste sollicité, ce qui limite sa fonte avec le temps.</li></ul>'
				. '<h2>Les grandes étapes</h2>'
				. '<ul><li><strong>Bilan :</strong> examen et radiographies pour vérifier la quantité d’os et votre état de santé général ;</li><li><strong>Pose de l’implant :</strong> sous anesthésie locale ;</li><li><strong>Cicatrisation :</strong> généralement 2 à 4 mois, le temps que l’implant se soude à l’os ;</li><li><strong>Pose de la couronne :</strong> réalisée sur mesure, dans la teinte de vos dents.</li></ul>'
				. '<h2>Est-ce fait pour moi ?</h2>'
				. '<p>La plupart des adultes en bonne santé peuvent recevoir un implant. Le tabac, un diabète mal équilibré ou certaines maladies des gencives demandent une attention particulière. Un devis détaillé vous est remis après le bilan.</p>',
		),
		array(
			'title'   => 'Couronnes, bridges & prothèses',
			'slug'    => 'couronnes-bridges-protheses',
			'icon'    => 'crown',
			'booking' => 'couronne-bridge-ou-prothese',
			'excerpt' => 'Protéger une dent abîmée ou remplacer des dents manquantes avec des prothèses fixes ou amovibles réalisées sur mesure.',
			'content' => '<p>Les prothèses dentaires permettent de retrouver le confort pour manger, l’esthétique du sourire et une bonne élocution. Chacune est réalisée sur mesure, à partir d’empreintes de votre bouche.</p>'
				. '<h2>La couronne</h2>'
				. '<p>Elle recouvre et protège une dent très abîmée ou dévitalisée. Sa teinte est choisie pour s’harmoniser avec vos autres dents.</p>'
				. '<h2>Le bridge</h2>'
				. '<p>Il remplace une dent manquante en prenant appui sur les dents voisines. C’est une solution fixe, qui ne s’enlève pas.</p>'
				. '<h2>Les prothèses amovibles</h2>'
				. '<p>Partielles ou complètes, elles remplacent plusieurs dents, voire toutes les dents d’une mâchoire. Elles peuvent être stabilisées par des implants pour plus de confort.</p>'
				. '<h2>Quelle solution choisir ?</h2>'
				. '<p>Tout dépend de l’état de vos dents, de vos gencives et de votre budget. Après l’examen, nous vous présentons les options possibles avec leurs avantages, et un devis pour chacune.</p>',
		),
		array(
			'title'   => 'Esthétique & blanchiment',
			'slug'    => 'esthetique-blanchiment',
			'icon'    => 'sparkle',
			'booking' => 'blanchiment-esthetique',
			'excerpt' => 'Blanchiment, facettes et retouches esthétiques pour un sourire plus lumineux, avec un résultat naturel et dans le respect de vos dents.',
			'content' => '<p>Dents ternies, taches, petites cassures, espaces entre les dents : la dentisterie esthétique propose des solutions pour retrouver un sourire qui vous plaît, avec un résultat naturel.</p>'
				. '<h2>Le blanchiment dentaire</h2>'
				. '<p>Il éclaircit la teinte naturelle des dents grâce à un gel adapté, appliqué au cabinet et/ou à la maison avec des gouttières sur mesure. Un contrôle et un détartrage sont nécessaires avant tout blanchiment. Une sensibilité passagère est possible.</p>'
				. '<p><em>Le blanchiment n’agit pas sur les couronnes, les facettes ni les obturations existantes.</em></p>'
				. '<h2>Les facettes</h2>'
				. '<p>Fines pellicules collées sur la face visible des dents, elles corrigent la forme, la couleur ou de petits défauts d’alignement.</p>'
				. '<h2>Les restaurations esthétiques</h2>'
				. '<p>Pour réparer une dent cassée, fermer un petit espace ou remplacer d’anciens plombages visibles par un matériau de la couleur de la dent.</p>'
				. '<p>Lors d’une consultation esthétique, nous analysons votre sourire et vous disons honnêtement ce qui est réalisable.</p>',
		),
		array(
			'title'   => 'Soins dentaires pour enfants',
			'slug'    => 'soins-dentaires-enfants',
			'icon'    => 'child',
			'booking' => 'soins-pour-enfant',
			'excerpt' => 'Des soins doux et rassurants pour les plus jeunes, et de bonnes habitudes dès le plus jeune âge.',
			'content' => '<p>Les dents de lait comptent : elles permettent de bien manger, de bien parler, et elles guident la place des dents définitives. Une carie sur une dent de lait doit être soignée.</p>'
				. '<h2>Une première visite en douceur</h2>'
				. '<p>Nous conseillons une première visite vers 3 ans, ou plus tôt en cas de doute. Ce rendez-vous sert d’abord à faire connaissance : l’enfant découvre le cabinet, le fauteuil et les instruments, sans stress.</p>'
				. '<h2>Ce que nous proposons</h2>'
				. '<ul><li>Des contrôles réguliers et des conseils de brossage adaptés à l’âge ;</li><li>Le scellement des sillons des molaires définitives, pour prévenir les caries ;</li><li>Le soin des caries, sur dents de lait comme sur dents définitives ;</li><li>Une orientation vers un orthodontiste si nécessaire.</li></ul>'
				. '<h2>Conseils aux parents</h2>'
				. '<p>Brossez les dents de votre enfant deux fois par jour avec un dentifrice fluoré adapté à son âge, et limitez le grignotage et les boissons sucrées, surtout le soir. Jusqu’à 7 ou 8 ans environ, un adulte doit vérifier ou terminer le brossage.</p>',
		),
		array(
			'title'   => 'Urgences dentaires',
			'slug'    => 'urgences-dentaires',
			'icon'    => 'alert',
			'booking' => 'douleur-ou-urgence',
			'excerpt' => 'Rage de dents, abcès, dent cassée ou choc : appelez-nous, nous faisons le maximum pour vous recevoir rapidement.',
			'content' => '<p>Une douleur dentaire ne doit pas attendre. En cas d’urgence, appelez directement le cabinet au {phone} : nous faisons notre possible pour vous recevoir dans les meilleurs délais pendant nos heures d’ouverture.</p>'
				. '<h2>Quand consulter rapidement ?</h2>'
				. '<ul><li>Douleur intense, ou qui vous réveille la nuit ;</li><li>Gonflement de la joue ou de la gencive (abcès) ;</li><li>Dent cassée, fêlée ou déplacée après un choc ;</li><li>Saignement qui persiste après une extraction ;</li><li>Couronne ou bridge qui s’est décollé.</li></ul>'
				. '<h2>Une dent définitive est tombée après un choc ?</h2>'
				. '<p>Récupérez-la en la tenant par la couronne (jamais par la racine), rincez-la rapidement sans la frotter, et gardez-la dans du lait ou dans la bouche, contre la joue. Venez au cabinet le plus vite possible : chaque minute compte.</p>'
				. '<h2>En attendant votre rendez-vous</h2>'
				. '<ul><li>Prenez un antalgique habituel en respectant la notice (évitez l’aspirine en cas de saignement) ;</li><li>Appliquez du froid sur la joue en cas de gonflement ;</li><li>Ne posez jamais d’aspirine directement sur la gencive.</li></ul>'
				. '<p><strong>Gonflement important, fièvre, difficulté à avaler ou à respirer :</strong> rendez-vous immédiatement aux urgences de l’hôpital le plus proche.</p>',
		),
	);
}

function rh_default_posts() {
	return array(
		array(
			'title'   => 'Détartrage : à quelle fréquence le faire, et pourquoi ?',
			'slug'    => 'detartrage-frequence',
			'excerpt' => 'Le tartre ne s’élimine pas au brossage. À quoi sert le détartrage, à quel rythme le faire, et comment limiter le tartre au quotidien.',
			'content' => '<p>Le tartre, c’est de la plaque dentaire qui a durci. Il se forme plus ou moins vite selon les personnes, la salive, l’alimentation et la qualité du brossage. Une fois installé, seule une séance chez le dentiste permet de l’enlever.</p>'
				. '<h2>Pourquoi est-ce important ?</h2>'
				. '<p>Le tartre est rugueux : il retient les bactéries qui irritent la gencive. Sans traitement, une gingivite (gencives rouges qui saignent) peut évoluer en parodontite, qui attaque l’os autour des dents et peut, à terme, les déchausser.</p>'
				. '<h2>À quel rythme ?</h2>'
				. '<p>Pour la plupart des adultes, un détartrage une à deux fois par an suffit. Les fumeurs, les personnes diabétiques, les femmes enceintes ou les patients qui portent un appareil orthodontique peuvent avoir besoin de séances plus rapprochées.</p>'
				. '<h2>Limiter le tartre au quotidien</h2>'
				. '<ul><li>Brossez-vous les dents deux fois par jour, pendant deux minutes ;</li><li>Nettoyez entre les dents avec du fil dentaire ou des brossettes ;</li><li>Utilisez un dentifrice fluoré ;</li><li>Limitez le tabac, le thé et le café, qui colorent le tartre.</li></ul>'
				. '<h2>Le détartrage abîme-t-il l’émail ?</h2>'
				. '<p>Non. Réalisé correctement, le détartrage n’use pas l’émail. La sensation de dents « plus fines » ou d’espaces entre les dents vient simplement du tartre retiré, qui comblait ces espaces.</p>',
		),
		array(
			'title'   => 'Rage de dents : que faire en attendant le dentiste ?',
			'slug'    => 'rage-de-dents-que-faire',
			'excerpt' => 'Une douleur dentaire intense ne passe pas toute seule. Les bons réflexes pour soulager la douleur, et savoir quand consulter en urgence.',
			'content' => '<p>La « rage de dents » est le plus souvent due à une inflammation du nerf de la dent, provoquée par une carie profonde, ou à un abcès. La douleur peut être violente, lancinante, et augmenter quand on est allongé.</p>'
				. '<h2>Les bons réflexes</h2>'
				. '<ul><li>Appelez votre dentiste pour obtenir un rendez-vous rapide ;</li><li>Prenez un antalgique habituel, comme le paracétamol, en respectant les doses indiquées sur la notice ;</li><li>Dormez la tête légèrement surélevée ;</li><li>Évitez les aliments très chauds, très froids ou sucrés du côté douloureux.</li></ul>'
				. '<h2>Les erreurs à éviter</h2>'
				. '<ul><li>Ne posez pas d’aspirine directement sur la gencive : elle la brûle ;</li><li>Ne prenez pas d’antibiotiques sans avis médical : ils ne traitent pas la cause et peuvent masquer l’infection ;</li><li>N’attendez pas que la douleur passe : une dent dont le nerf meurt peut cesser de faire mal… tout en continuant à s’infecter.</li></ul>'
				. '<h2>Quand consulter en urgence ?</h2>'
				. '<p>Si un gonflement apparaît sur la joue, s’accompagne de fièvre, ou si vous avez du mal à ouvrir la bouche, à avaler ou à respirer, consultez sans attendre, aux urgences de l’hôpital si le cabinet est fermé.</p>'
				. '<p>Au cabinet, le traitement soulage le plus souvent la douleur dès la première séance : soin de la carie, traitement du nerf ou drainage de l’abcès selon le cas.</p>',
		),
		array(
			'title'   => 'Implant dentaire : les étapes du traitement, pas à pas',
			'slug'    => 'implant-dentaire-etapes',
			'excerpt' => 'Bilan, pose, cicatrisation, couronne : comment se déroule un traitement par implant, et combien de temps il faut prévoir.',
			'content' => '<p>Remplacer une dent manquante par un implant demande un peu de patience, mais le résultat est une dent fixe, confortable et durable. Voici comment se déroule le traitement.</p>'
				. '<h2>1. Le bilan</h2>'
				. '<p>Tout commence par un examen de la bouche et des radiographies, pour évaluer la quantité et la qualité de l’os. Nous faisons aussi le point sur votre santé générale et vos traitements. C’est à cette étape que vous recevez un plan de traitement et un devis détaillé.</p>'
				. '<h2>2. La pose de l’implant</h2>'
				. '<p>L’intervention se fait sous anesthésie locale et dure en général moins d’une heure pour un implant. Les suites sont le plus souvent simples : un léger gonflement et une gêne pendant quelques jours, soulagés par des antalgiques.</p>'
				. '<h2>3. La cicatrisation</h2>'
				. '<p>Pendant 2 à 4 mois en moyenne, l’os se soude à l’implant. Selon la situation, une dent provisoire peut être posée pendant cette période, notamment pour les dents visibles.</p>'
				. '<h2>4. La couronne définitive</h2>'
				. '<p>Une fois l’implant stable, une empreinte est prise et la couronne est fabriquée sur mesure, dans la teinte de vos dents, puis fixée sur l’implant.</p>'
				. '<h2>Et après ?</h2>'
				. '<p>Un implant se brosse comme une dent naturelle et demande des contrôles réguliers. Un bon entretien et l’arrêt du tabac sont les meilleurs garants de sa longévité.</p>',
		),
	);
}

/**
 * Home page FAQ.
 *
 * @return array<int,array{0:string,1:string}>
 */
function rh_faq() {
	$wa   = rh_info( 'whatsapp' ) ? ' ' . __( 'ou sur WhatsApp', 'rhomija' ) : '';
	$faqs = array(
		array(
			__( 'Comment prendre rendez-vous ?', 'rhomija' ),
			sprintf(
				/* translators: 1: phone, 2: " ou sur WhatsApp" */
				__( 'En ligne, sur ce site : choisissez le motif de votre visite puis un créneau libre. Vous pouvez aussi nous appeler au %1$s%2$s.', 'rhomija' ),
				rh_info( 'phone' ),
				$wa
			),
		),
		array(
			__( 'Mon rendez-vous en ligne est-il confirmé tout de suite ?', 'rhomija' ),
			__( 'Votre demande réserve le créneau choisi. Le cabinet vous contacte ensuite par téléphone ou WhatsApp pour le confirmer. Si vous indiquez votre e-mail, vous recevez aussi la confirmation par e-mail avec une invitation pour votre agenda.', 'rhomija' ),
		),
		array(
			__( 'J’ai mal, est-ce que vous prenez les urgences ?', 'rhomija' ),
			sprintf(
				/* translators: %s: phone */
				__( 'Oui. Appelez directement le cabinet au %s : nous faisons le maximum pour vous recevoir rapidement pendant nos horaires d’ouverture. En ligne, choisissez le motif « Douleur ou urgence ».', 'rhomija' ),
				rh_info( 'phone' )
			),
		),
		array(
			__( 'Remettez-vous un devis avant les soins ?', 'rhomija' ),
			__( 'Oui. Pour tout traitement important (implant, prothèse, esthétique…), un devis détaillé vous est remis après l’examen, avant de commencer. Rien n’est engagé sans votre accord.', 'rhomija' ),
		),
		array(
			__( 'Comment me faire rembourser ?', 'rhomija' ),
			__( 'Nous vous remettons les documents nécessaires (feuille de soins, devis) pour votre dossier auprès de votre assurance maladie : AMO, mutuelle ou assurance privée.', 'rhomija' ),
		),
		array(
			__( 'Où se trouve le cabinet ?', 'rhomija' ),
			sprintf(
				/* translators: %s: address */
				__( 'Le cabinet se trouve au %s. Le plan d’accès et le bouton « Itinéraire » de la page Contact vous guident jusqu’à nous avec Google Maps.', 'rhomija' ),
				rh_address_inline()
			),
		),
	);
	return apply_filters( 'rh_faq', $faqs );
}
