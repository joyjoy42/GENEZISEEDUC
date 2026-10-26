<?php

namespace App\Http\Requests;

use App\Models\Question;
use App\Orientation\Reponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReponsesTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reponses' => ['required', 'array'],
            'reponses.*' => ['required', 'integer', Rule::in(Reponse::valeurs())],
        ];
    }

    /**
     * Un test incomplet ne donne pas un profil fiable : on refuse plutôt que
     * de compter une question sans réponse comme un « pas du tout ».
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('reponses')) {
                    return;
                }

                $repondues = array_keys($this->input('reponses', []));
                $manquantes = Question::actives()
                    ->whereNotIn('id', $repondues)
                    ->count();

                if ($manquantes > 0) {
                    $validator->errors()->add(
                        'reponses',
                        $manquantes === 1
                            ? 'Il reste 1 question sans réponse.'
                            : "Il reste {$manquantes} questions sans réponse."
                    );
                }
            },
        ];
    }
}
