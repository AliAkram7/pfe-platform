<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => 'Allocation et ordonnancement des tâches
            dans un environnement de cloud
            computing à base d’énergie',

            'key_word' => '[{"key":"Optimisation"},{"key":"Signalisation des feux"},{"key":"Multi-Intersection- Control"}]',

            'description' => 'proposition d un réseau de plusieurs intersection et faire le controle de la signalisation des feux pour un
            réseau de plusieurs intersections pour réduire les retards des véhicules et leurs attentes.
            L&#39;optimisation par colonies de fourmis(ACO) est une méta-heuristique basé sur le comportement des
            colonies de fourmis pour la recherche de nourriture.',

            'research_domain' => 'Optimisation Trafic Routier Intelligence Artficielle}.

            (Préciser : le domaine d’appartenance exple : optimisation, traitement d’images, réseaux, web …)',

            'objectives_of_the_project' => 'Optimisation de la signalisation des feux par colonie de fourmis',

            'work_plan' => '[{"plan":"Réseau de plusieurs intersections."},{"plan":"Calcul de débit des véhicules au cours de toute la journée, on propose la zone où se trouve les intersections."},{"plan":"Adaptation du problème par la méthode (ACO)."},{"plan":"Optimisation de la signalisation des feux par colonie de fourmis."}]',

            'specialty_id' => $this->faker->numberBetween(1, 1),
            'teacher_id' => $this->faker->numberBetween(40, 40),
        ];
    }
}
