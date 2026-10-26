<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Orientation\Dimension;
use Illuminate\Database\Seeder;

/**
 * Les questions du test d'orientation, quatre par dimension RIASEC.
 *
 * Elles décrivent des activités concrètes plutôt que des traits de caractère :
 * « réparer un appareil » se comprend mieux que « être manuel », surtout quand
 * personne ne t'a jamais montré ce que les métiers recouvrent.
 */
class QuestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        $questions = $this->questions();
        $parDimension = max(array_map('count', $questions));
        $ordre = 0;

        // On alterne les dimensions au lieu de les enchaîner : quatre questions
        // de suite sur le même thème poussent à répondre la même chose partout.
        for ($rang = 0; $rang < $parDimension; $rang++) {
            foreach ($questions as $dimension => $libelles) {
                if (! isset($libelles[$rang])) {
                    continue;
                }

                Question::updateOrCreate(
                    ['libelle' => $libelles[$rang]],
                    [
                        'dimension' => $dimension,
                        'ordre' => ++$ordre,
                        'est_active' => true,
                    ]
                );
            }
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function questions(): array
    {
        return [
            Dimension::Realiste->value => [
                'Réparer un appareil en panne plutôt que le remplacer',
                'Travailler avec mes mains, des outils ou des machines',
                'Passer ma journée dehors ou en atelier plutôt qu’assis à un bureau',
                'Comprendre comment un moteur ou une machine fonctionne',
            ],
            Dimension::Investigateur->value => [
                'Chercher longtemps pourquoi quelque chose ne marche pas',
                'Lire ou regarder des contenus qui expliquent comment le monde fonctionne',
                'Résoudre des problèmes de logique ou de calcul',
                'Vérifier une information avant de la croire',
            ],
            Dimension::Artistique->value => [
                'Créer quelque chose qui n’existait pas : un dessin, un texte, un vêtement',
                'Choisir les couleurs, la mise en page ou la musique d’un projet',
                'Improviser plutôt que suivre une procédure',
                'M’exprimer devant les autres par l’image, le son ou le geste',
            ],
            Dimension::Social->value => [
                'Expliquer une leçon à quelqu’un jusqu’à ce qu’il comprenne',
                'Écouter une personne qui traverse une difficulté',
                'Travailler en équipe plutôt que seul',
                'M’occuper du bien-être des autres',
            ],
            Dimension::Entreprenant->value => [
                'Convaincre quelqu’un d’adhérer à mon idée',
                'Prendre la tête d’un groupe et décider',
                'Vendre un produit ou un service',
                'Lancer ma propre activité plutôt que travailler pour quelqu’un',
            ],
            Dimension::Conventionnel->value => [
                'Tenir des comptes ou un registre à jour',
                'Suivre une procédure précise, étape par étape',
                'Classer, ranger, organiser des informations',
                'Vérifier que tout est exact avant de valider',
            ],
        ];
    }
}
