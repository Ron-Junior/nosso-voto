<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\VendorProfile;
use App\Models\WeddingVendor;
use App\Support\BrazilLocations;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::guest')]
#[Title('Criar Conta — Nosso Voto')]
class Register extends Component
{
    public string $role = User::ROLE_COUPLE;

    public string $name = '';

    public string $state = '';

    public string $city = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * @var list<string>
     */
    public array $vendor_categories = [];

    public function selectRole(string $role): void
    {
        if (in_array($role, [User::ROLE_COUPLE, User::ROLE_VENDOR], true)) {
            $this->role = $role;
        }
    }

    public function updatedState(): void
    {
        $this->state = Str::upper(trim($this->state));

        if (! BrazilLocations::isValidCityForState($this->state, $this->city)) {
            $this->city = '';
        }
    }

    public function register(): void
    {
        $this->state = Str::upper(trim($this->state));
        $this->city = trim($this->city);

        $rules = [
            'role' => ['required', 'in:couple,vendor'],
            'name' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', Rule::in(BrazilLocations::stateCodes())],
            'city' => ['required', 'string', Rule::in(BrazilLocations::citiesForState($this->state))],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($this->role === User::ROLE_VENDOR) {
            $rules['vendor_categories'] = ['required', 'array', 'min:1'];
            $rules['vendor_categories.*'] = ['required', 'string', Rule::in(WeddingVendor::CATEGORIES)];
        }

        $validated = $this->validate($rules, [
            'role.in' => 'Selecione um perfil válido (Noivos ou Fornecedor).',
            'name.required' => 'Informe seu nome completo ou nome do casal.',
            'state.required' => 'Selecione o seu estado (UF).',
            'state.in' => 'Selecione um estado (UF) válido.',
            'city.required' => 'Selecione a sua cidade.',
            'city.in' => 'Selecione uma cidade válida para o estado escolhido.',
            'email.required' => 'Informe seu endereço de e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'Crie uma senha de acesso.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'vendor_categories.required' => 'Selecione ao menos uma área de atuação para continuar.',
            'vendor_categories.array' => 'Selecione ao menos uma área de atuação válida.',
            'vendor_categories.min' => 'Selecione ao menos uma área de atuação para continuar.',
            'vendor_categories.*.in' => 'Selecione apenas áreas de atuação válidas.',
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $createdUser = User::query()->create([
                'name' => trim($validated['name']),
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
                'state' => $validated['state'],
                'city' => $validated['city'],
            ]);

            if ($createdUser->isVendor()) {
                $categories = VendorProfile::sanitizeCategories($validated['vendor_categories'] ?? []);

                VendorProfile::query()->create([
                    'user_id' => $createdUser->id,
                    'business_name' => trim($validated['name']),
                    'category' => $categories[0],
                    'categories' => $categories,
                    'state' => $validated['state'],
                    'city' => $validated['city'],
                    'whatsapp' => '',
                ]);
            }

            return $createdUser;
        });

        Auth::login($user);
        session()->regenerate();

        $this->redirect($user->homeRoute());
    }

    public function render(): View
    {
        return view('livewire.auth.register', [
            'categoryLabels' => WeddingVendor::categoryLabels(),
            'states' => BrazilLocations::states(),
            'citiesForSelectedState' => BrazilLocations::citiesForState($this->state),
            'citiesByState' => BrazilLocations::allCitiesByState(),
        ]);
    }
}
