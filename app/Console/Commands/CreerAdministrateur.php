<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreerAdministrateur extends Command
{
    protected $signature = 'genezise:administrateur
                            {nom : Le nom de la personne}
                            {email : Son adresse e-mail}
                            {--mot-de-passe= : Le mot de passe (généré au hasard si absent)}';

    protected $description = "Crée un compte d'accès au back-office GENEZISE";

    public function handle(): int
    {
        $nom = $this->argument('nom');
        $email = $this->argument('email');
        $motDePasse = $this->option('mot-de-passe') ?: Str::password(16);
        $genere = $this->option('mot-de-passe') === null;

        $validation = Validator::make([
            'name' => $nom,
            'email' => $email,
            'password' => $motDePasse,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::min(12)],
        ]);

        if ($validation->fails()) {
            foreach ($validation->errors()->all() as $erreur) {
                $this->error($erreur);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $nom,
            'email' => $email,
            'password' => Hash::make($motDePasse),
        ]);

        $this->info("Compte créé pour {$nom} ({$email}).");

        if ($genere) {
            $this->newLine();
            $this->line('Mot de passe généré, à noter maintenant : '.$motDePasse);
            $this->comment("Il n'est affiché qu'une seule fois.");
        }

        return self::SUCCESS;
    }
}
