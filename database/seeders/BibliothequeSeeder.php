<?php

namespace Database\Seeders;

use App\Models\Filiere;
use App\Models\Metier;
use App\Models\Secteur;
use App\Models\Temoignage;
use App\Orientation\Profil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Contenu de départ de la bibliothèque des métiers.
 *
 * Tout ce qui est entre crochets doit être rempli à partir des interviews
 * Joy's World : citations, salaires réels, lieux. Rien n'est inventé ici.
 */
class BibliothequeSeeder extends Seeder
{
    public function run(): void
    {
        $secteurs = $this->secteurs();
        $filieres = $this->filieres();

        foreach ($this->metiers() as $data) {
            $liens = $data['filieres'];
            $secteurSlug = $data['secteur'];
            unset($data['filieres'], $data['secteur']);

            $data['secteur_id'] = $secteurs[$secteurSlug]->id;
            $data['publie_le'] = $data['est_publie'] ? now() : null;

            $metier = Metier::updateOrCreate(['slug' => $data['slug']], $data);
            $metier->filieres()->sync(
                collect($liens)->map(fn ($slug) => $filieres[$slug]->id)->all()
            );
        }

        $this->temoignages();
    }

    private function secteurs(): Collection
    {
        return collect([
            [
                'nom' => 'Mécanique et automobile',
                'slug' => 'mecanique-automobile',
                'couleur' => '#B5481A',
                'ordre' => 1,
                'description' => "Les métiers qui réparent, entretiennent et font rouler les véhicules. Le premier secteur visité par Joy's World.",
            ],
            [
                'nom' => 'Mode et textile',
                'slug' => 'mode-textile',
                'couleur' => '#2E7D6E',
                'ordre' => 2,
                'description' => 'De la mesure à la finition : les métiers qui habillent, du sur-mesure à la petite production.',
            ],
            [
                'nom' => 'Santé et santé mentale',
                'slug' => 'sante-mentale',
                'couleur' => '#1B1F3B',
                'ordre' => 3,
                'description' => "Soigner le corps et l'esprit. Un secteur où les métiers sont souvent confondus entre eux.",
            ],
            [
                'nom' => 'Informatique et numérique',
                'slug' => 'informatique-numerique',
                'couleur' => '#E8743B',
                'ordre' => 4,
                'description' => "Bien plus que « l'informatique » : une dizaine de métiers très différents derrière un seul mot.",
            ],
        ])->mapWithKeys(fn (array $data) => [
            $data['slug'] => Secteur::updateOrCreate(['slug' => $data['slug']], $data),
        ]);
    }

    private function filieres(): Collection
    {
        return collect([
            [
                'nom' => 'Formation professionnelle en mécanique automobile',
                'slug' => 'formation-mecanique-auto',
                'niveau' => 'CAP / CQP',
                'duree_annees' => 3,
                'description' => "Apprentissage en atelier, souvent complété par un centre de formation technique. Beaucoup de mécaniciens au Bénin se forment d'abord chez un patron.",
                'etablissements' => '[Lister les centres de formation technique de Cotonou]',
                'financements' => "[À vérifier : coût de l'apprentissage chez un patron, et les aides du centre de formation]",
            ],
            [
                'nom' => 'Formation en couture et stylisme',
                'slug' => 'formation-couture-stylisme',
                'niveau' => 'CQM / CQP',
                'duree_annees' => 3,
                'description' => "Trois chemins. L'apprentissage traditionnel chez un maître couturier : on regarde, "
                    .'on imite, on recommence, et ça dure rarement trois ans — Jean a appris de 1993 à 2000, '
                    .'soit sept ans. La voie en alternance, atelier plus centre de formation, trois ans, avec le '
                    .'CEP pour entrer, qui prépare le CQP. Ou une école de mode privée pour la création et le '
                    ."modélisme. La plupart des couturiers du pays n'ont pas appris à l'école. Une étude soutenue "
                    .'par l’OIT au Ghana mesure environ 25 % de revenu en plus pour les apprentis mieux formés.',
                'etablissements' => 'Ateliers de maîtres couturiers, centres publics CFPA, écoles de mode privées. '
                    ."[Lister les centres de Cotonou et d'Abomey-Calavi, à confirmer auprès de la CMA-B]",
                'financements' => "L'apprentissage chez un patron coûte au moins 300 000 FCFA d'après Jean "
                    .'(interview du 17 octobre 2026). Le CQM se passe surtout en épreuve pratique pour '
                    ."12 500 FCFA et permet d'ouvrir son atelier ; le CQP ouvre en plus les marchés publics. "
                    .'Le CQM est récent : son premier examen date de 2013, donc les couturiers formés avant '
                    ."n'ont au mieux qu'une attestation d'atelier. Montants et conditions à revalider auprès de "
                    .'la CMA-B. [Bourses et aides : à vérifier]',
            ],
            [
                'nom' => 'Licence de psychologie',
                'slug' => 'licence-psychologie',
                'niveau' => 'Licence puis Master',
                'duree_annees' => 5,
                'description' => "Trois ans de licence puis un master pour exercer comme psychologue. Ce n'est pas une formation de médecine.",
                'etablissements' => '[Lister les universités qui proposent la psychologie au Bénin]',
                'financements' => '[À vérifier : bourses nationales et frais de scolarité par université]',
            ],
            [
                'nom' => 'Médecine puis spécialisation en psychiatrie',
                'slug' => 'medecine-psychiatrie',
                'niveau' => 'Doctorat en médecine',
                'duree_annees' => 10,
                'description' => "Le cursus complet de médecine, puis une spécialisation en psychiatrie. C'est ce qui donne le droit de prescrire des médicaments.",
                'etablissements' => '[Lister les facultés de médecine]',
                'financements' => '[À vérifier : bourses nationales, internat rémunéré, frais par année]',
            ],
            [
                'nom' => 'Génie informatique',
                'slug' => 'genie-informatique',
                'niveau' => 'Licence puis Master',
                'duree_annees' => 5,
                'description' => 'On y apprend à concevoir et construire des systèmes : la machine, le réseau, le logiciel qui tourne dessus. Orientation ingénieur.',
                'etablissements' => '[Lister les écoles qui proposent le génie informatique]',
                'financements' => '[À vérifier : bourses nationales, bourses des écoles privées agréées]',
            ],
            [
                'nom' => 'Science informatique',
                'slug' => 'science-informatique',
                'niveau' => 'Licence puis Master',
                'duree_annees' => 5,
                'description' => 'On y apprend le raisonnement derrière la machine : algorithmes, structures de données, théorie. Orientation conception logicielle et recherche.',
                'etablissements' => "Aucune filière de ce nom dans le guide du ministère : au Bénin, c'est le génie informatique ou une licence de l'IFRI.",
                'formation_disponible_pays' => false,
                'financements' => "[À vérifier : à l'étranger, les bourses de coopération ; au Bénin, voir le génie informatique]",
            ],
        ])->mapWithKeys(fn (array $data) => [
            $data['slug'] => Filiere::updateOrCreate(['slug' => $data['slug']], $data),
        ]);
    }

    private function metiers(): array
    {
        return [
            [
                'secteur' => 'mecanique-automobile',
                'filieres' => ['formation-mecanique-auto'],
                'nom' => 'Mécanicien automobile',
                'slug' => 'mecanicien-automobile',
                'accroche' => "Il écoute un moteur comme un médecin écoute un cœur, et répare ce que personne d'autre ne sait réparer.",
                'le_quotidien' => '[À écrire à partir de la visite de garage : à quelle heure il ouvre, combien de véhicules par jour, comment il diagnostique sans valise électronique, comment il gère les clients et les pièces.]',
                'competences' => ['Diagnostic de panne', 'Mécanique moteur', 'Freinage et suspension', 'Relation client', 'Gestion des pièces'],
                'outils' => ['Clés et douilles', 'Cric et chandelles', 'Valise de diagnostic', 'Compresseur'],
                'ou_travailler' => "Garages de quartier, concessions, flottes d'entreprise, transport. Beaucoup finissent par ouvrir leur propre garage.",
                'debouches' => "Chef d'atelier, patron de garage, spécialiste d'une marque, formateur d'apprentis.",
                'idee_fausse' => "« C'est un métier pour ceux qui ont échoué à l'école. »",
                'realite' => "Un bon mécanicien lit des schémas électriques, se forme en continu sur des véhicules de plus en plus électroniques, et gère une petite entreprise. Le garage visité par Joy's World tourne sur carnets, WhatsApp et mémoire : la compétence est technique autant que commerciale.",
                'niveau_etude' => 'Apprentissage, CAP ou CQP',
                'est_publie' => true,
            ],
            [
                'secteur' => 'mode-textile',
                'filieres' => ['formation-couture-stylisme'],
                'nom' => 'Couturier',
                'slug' => 'couturier',
                'accroche' => 'Il transforme six mètres de tissu et une photo floue en vêtement qui tombe juste.',
                'le_quotidien' => "La journée commence par le programme : ce qui doit sortir aujourd'hui, et pour qui. "
                    ."Vient ensuite l'analyse du modèle apporté par le client — souvent une simple photo —, la prise de "
                    .'mesures, le traçage, la coupe, la couture à la machine, les finitions à la main, puis '
                    ."l'essayage. L'atelier travaille en équipe et les urgences passent devant le reste. C'est "
                    .'physique pour le dos et les yeux, et mental pour les délais. Les pics tombent à Noël, aux fêtes '
                    .'et pendant la saison des mariages. Interview du 17 octobre 2026, atelier John Confection, '
                    .'Ségbéya.',
                'competences' => ['Prise de mesures', 'Coupe et patronage', 'Couture machine et finitions main',
                    'Écouter la personne', 'Avoir du goût', 'Soigner la finition', 'Gestion des délais'],
                'outils' => ['Machine à coudre', 'Surjeteuse', 'Mètre ruban', 'Ciseaux de coupe', 'Fer à repasser'],
                'ou_travailler' => 'Atelier à son compte, maison de couture, petite production textile, costumes et uniformes.',
                'debouches' => 'Maître couturier avec apprentis, styliste-modéliste, marque de prêt-à-porter, costumier.',
                'idee_fausse' => "« La couture, c'est un repli quand on n'a pas pu faire d'études. »",
                'realite' => "Le sur-mesure est un métier de précision et de relation client, et c'est l'un des rares où l'on peut ouvrir son atelier jeune. Le modélisme, lui, est de la géométrie appliquée. "
                    .'Le marché de Cotonou est saturé et les prix se cassent : ce qui fait tenir un atelier, '
                    ."c'est la clientèle et la qualité des finitions, pas le prix le plus bas. Les réseaux sociaux "
                    .'amorcent une partie des commandes.',
                'niveau_etude' => "Apprentissage en atelier, CEP pour l'alternance en centre, BEPC conseillé par Jean ; CQM ou école de mode",
                'est_publie' => true,
            ],
            [
                'secteur' => 'sante-mentale',
                'filieres' => ['licence-psychologie'],
                'nom' => 'Psychologue',
                'slug' => 'psychologue',
                'accroche' => "Il écoute, évalue et accompagne par la parole. Il n'est pas médecin et ne prescrit pas de médicaments.",
                'le_quotidien' => "[À écrire après l'interview d'un psychologue à Cotonou : combien de séances par jour, dans quel cadre, quels publics.]",
                'competences' => ['Entretien clinique', 'Tests et évaluation psychologique', 'Accompagnement thérapeutique', 'Écoute active', 'Secret professionnel'],
                'outils' => ['Tests psychométriques', "Grilles d'entretien", 'Dossiers de suivi'],
                'ou_travailler' => 'Cabinet privé, hôpital, école, ONG, entreprise, services sociaux.',
                'debouches' => 'Psychologue clinicien, psychologue scolaire, psychologue du travail, neuropsychologue, enseignant-chercheur.',
                'idee_fausse' => "« Aller voir un psy, c'est être fou. »",
                'realite' => "On consulte un psychologue comme on consulte pour une douleur au genou : parce que quelque chose fait mal et qu'on veut comprendre. Et psychologue n'est pas psychiatre : le premier accompagne par la parole, le second est médecin et peut prescrire. Les deux travaillent souvent ensemble sur un même patient.",
                'niveau_etude' => 'Licence puis Master en psychologie',
                'est_publie' => true,
            ],
            [
                'secteur' => 'sante-mentale',
                'filieres' => ['medecine-psychiatrie'],
                'nom' => 'Psychiatre',
                'slug' => 'psychiatre',
                'accroche' => "C'est un médecin spécialisé dans les troubles mentaux. Il diagnostique et peut prescrire un traitement.",
                'le_quotidien' => "[À écrire après l'interview d'un psychiatre : consultations, hospitalisation, travail avec les psychologues.]",
                'competences' => ['Diagnostic médical', 'Prescription et suivi de traitement', "Gestion de l'urgence psychiatrique", 'Travail en équipe pluridisciplinaire'],
                'outils' => ['Classifications diagnostiques', 'Dossier médical', 'Pharmacopée psychiatrique'],
                'ou_travailler' => 'Hôpital, service de psychiatrie, cabinet, centre de santé mentale.',
                'debouches' => 'Psychiatrie générale, pédopsychiatrie, addictologie, expertise, enseignement hospitalier.',
                'idee_fausse' => "« Psychiatre et psychologue, c'est pareil. »",
                'realite' => "Le psychiatre a fait médecine : c'est ce qui lui donne le droit de prescrire. Le psychologue a fait un cursus de psychologie et travaille par la parole et l'évaluation. Ce sont deux chemins d'études complètement différents pour deux métiers qui se complètent.",
                'niveau_etude' => 'Doctorat en médecine puis spécialisation',
                'est_publie' => true,
            ],
            [
                'secteur' => 'informatique-numerique',
                'filieres' => ['genie-informatique'],
                'nom' => 'Ingénieur en génie informatique',
                'slug' => 'ingenieur-genie-informatique',
                'accroche' => 'Il construit le système complet : la machine, le réseau et le logiciel qui tourne dessus.',
                'le_quotidien' => "[À écrire après l'interview d'un ingénieur à Cotonou : sur quels projets, avec quelle équipe, combien de terrain.]",
                'competences' => ['Architecture système', 'Réseaux', 'Systèmes embarqués', 'Gestion de projet technique'],
                'outils' => ['Langages système', 'Équipement réseau', 'Cartes et microcontrôleurs'],
                'ou_travailler' => 'Opérateurs télécom, banques, industrie, intégrateurs, administration.',
                'debouches' => 'Architecte système, ingénieur réseau, ingénieur embarqué, directeur technique.',
                'idee_fausse' => "« Génie informatique et science informatique, c'est le même diplôme. »",
                'realite' => "Le génie informatique construit le système, matériel compris. La science informatique étudie le raisonnement derrière la machine. Deux filières, deux métiers, et le choix se fait au moment de l'inscription — souvent sans que personne l'explique.",
                'niveau_etude' => 'Licence puis Master en génie informatique',
                'est_publie' => true,
            ],
            [
                'secteur' => 'informatique-numerique',
                'filieres' => ['science-informatique'],
                'nom' => 'Développeur logiciel',
                'slug' => 'developpeur-logiciel',
                'accroche' => "Il écrit le code qui fait marcher une application, et passe plus de temps à réfléchir qu'à taper.",
                'le_quotidien' => "[À écrire après l'interview d'un développeur à Cotonou : sur quoi il travaille, en équipe ou seul, pour des clients locaux ou à distance.]",
                'competences' => ['Algorithmique', 'Un ou plusieurs langages de programmation', 'Bases de données', 'Travail en équipe et versioning', 'Tests'],
                'outils' => ['Éditeur de code', 'Git', 'Base de données', 'Framework web'],
                'ou_travailler' => "Agences et start-up, banques, télécoms, freelance pour des clients à l'étranger.",
                'debouches' => 'Développeur back-end ou front-end, lead technique, data, fondateur de produit.',
                'idee_fausse' => '« Il faut être très fort en maths pour coder. »',
                'realite' => "Il faut surtout savoir découper un problème en étapes et accepter de se tromper longtemps. Les maths servent beaucoup sur certains postes, presque pas sur d'autres.",
                'niveau_etude' => 'Licence en science informatique, ou autodidacte avec portfolio',
                'est_publie' => true,
            ],
        ];
    }

    /**
     * Les interviews déjà tournées. Les citations restent à extraire des rushes.
     */
    private function temoignages(): void
    {
        // Une deuxième couturière a été interrogée pour l'épisode couturier, sans
        // donner son consentement écrit : ni son nom ni ses mots (dont la carte
        // « C'est un don » du reel du 20 octobre) n'entrent ici.
        $temoignages = [
            [
                'metier' => 'couturier',
                'nom' => 'Jean',
                'fonction' => 'Couturier depuis plus de 30 ans, atelier à son compte depuis 2003',
                'entreprise' => 'John Confection',
                'ville' => 'Cotonou',
                'citation' => "Tant que l'homme vit, il doit s'habiller.",
                'plateforme' => 'instagram',
                'fichier_video' => 'Couturiers/Reel 20 oct - Couturier partie 1.mp4',
                'duree_secondes' => 178,
                'date_interview' => '2026-10-17',
                'est_publie' => true,
            ],
            [
                'metier' => 'couturier',
                'nom' => 'Jean',
                'fonction' => 'Couturier depuis plus de 30 ans, atelier à son compte depuis 2003',
                'entreprise' => 'John Confection',
                'ville' => 'Cotonou',
                'citation' => 'Seul, tu ne peux rien faire.',
                'plateforme' => 'instagram',
                'fichier_video' => 'Couturiers/Reel 22 oct - Couturier partie 2.mp4',
                'duree_secondes' => 174,
                'date_interview' => '2026-10-17',
                'est_publie' => true,
            ],
            [
                'metier' => 'mecanicien-automobile',
                'nom' => '[Nom du garagiste interviewé]',
                'fonction' => 'Patron de garage',
                'ville' => 'Cotonou',
                'citation' => '[Citation à extraire de la visite de garage]',
                'date_interview' => null,
                'est_publie' => true,
            ],
        ];

        foreach ($temoignages as $data) {
            $metier = Metier::where('slug', $data['metier'])->first();

            if (! $metier) {
                continue;
            }

            unset($data['metier']);
            $data['metier_id'] = $metier->id;

            Temoignage::updateOrCreate(
                [
                    'metier_id' => $metier->id,
                    'nom' => $data['nom'],
                    'fichier_video' => $data['fichier_video'] ?? null,
                ],
                $data
            );
        }

        $this->profilsRiasec();
    }

    /**
     * Les profils RIASEC de départ des métiers.
     *
     * Ce sont des estimations, pas des mesures : elles viennent de la nature
     * connue de chaque métier, pas des interviews. Ce sont elles qui décident
     * des recommandations du test, donc elles se corrigent dans le back-office
     * au fur et à mesure que les interviews montrent le métier réel.
     */
    private function profilsRiasec(): void
    {
        $profils = [
            'mecanicien-automobile' => [
                'realiste' => 90, 'investigateur' => 55, 'artistique' => 15,
                'social' => 30, 'entreprenant' => 40, 'conventionnel' => 35,
            ],
            'couturier' => [
                'realiste' => 70, 'investigateur' => 25, 'artistique' => 80,
                'social' => 35, 'entreprenant' => 50, 'conventionnel' => 40,
            ],
            'psychologue' => [
                'realiste' => 10, 'investigateur' => 70, 'artistique' => 35,
                'social' => 95, 'entreprenant' => 30, 'conventionnel' => 40,
            ],
            'psychiatre' => [
                'realiste' => 20, 'investigateur' => 85, 'artistique' => 20,
                'social' => 80, 'entreprenant' => 35, 'conventionnel' => 55,
            ],
            'ingenieur-genie-informatique' => [
                'realiste' => 65, 'investigateur' => 85, 'artistique' => 30,
                'social' => 25, 'entreprenant' => 40, 'conventionnel' => 60,
            ],
            'developpeur-logiciel' => [
                'realiste' => 35, 'investigateur' => 85, 'artistique' => 45,
                'social' => 25, 'entreprenant' => 35, 'conventionnel' => 60,
            ],
        ];

        foreach ($profils as $slug => $scores) {
            Metier::where('slug', $slug)->update(
                Profil::depuisTableau($scores)->enColonnes()
            );
        }
    }
}
