<?php

namespace App\Support;

use Illuminate\Support\Str;

class BrazilLocations
{
    /**
     * Map of Brazilian states (UF => Name).
     *
     * @var array<string, string>
     */
    private const STATES = [
        'AC' => 'Acre',
        'AL' => 'Alagoas',
        'AP' => 'Amapá',
        'AM' => 'Amazonas',
        'BA' => 'Bahia',
        'CE' => 'Ceará',
        'DF' => 'Distrito Federal',
        'ES' => 'Espírito Santo',
        'GO' => 'Goiás',
        'MA' => 'Maranhão',
        'MT' => 'Mato Grosso',
        'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais',
        'PA' => 'Pará',
        'PB' => 'Paraíba',
        'PR' => 'Paraná',
        'PE' => 'Pernambuco',
        'PI' => 'Piauí',
        'RJ' => 'Rio de Janeiro',
        'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul',
        'RO' => 'Rondônia',
        'RR' => 'Roraima',
        'SC' => 'Santa Catarina',
        'SP' => 'São Paulo',
        'SE' => 'Sergipe',
        'TO' => 'Tocantins',
    ];

    /**
     * Curated cities per Brazilian state (capitals, metropolitan regions, and main wedding destinations).
     *
     * @var array<string, list<string>>
     */
    private const CITIES_BY_STATE = [
        'AC' => ['Cruzeiro do Sul', 'Rio Branco', 'Sena Madureira', 'Tarauacá'],
        'AL' => ['Arapiraca', 'Maceió', 'Maragogi', 'Marechal Deodoro', 'Penedo', 'São Miguel dos Milagres'],
        'AP' => ['Macapá', 'Oiapoque', 'Santana'],
        'AM' => ['Itacoatiara', 'Manacapuru', 'Manaus', 'Parintins', 'Presidente Figueiredo'],
        'BA' => [
            'Barreiras',
            'Camaçari',
            'Feira de Santana',
            'Ilhéus',
            'Itacaré',
            'Juazeiro',
            'Lauro de Freitas',
            'Mata de São João',
            'Morro de São Paulo',
            'Porto Seguro',
            'Praia do Forte',
            'Salvador',
            'Trancoso',
            'Vitória da Conquista',
        ],
        'CE' => ['Aquiraz', 'Caucaia', 'Fortaleza', 'Jijoca de Jericoacoara', 'Juazeiro do Norte', 'Sobral'],
        'DF' => ['Brasília'],
        'ES' => ['Cachoeiro de Itapemirim', 'Cariacica', 'Colatina', 'Domingos Martins', 'Guarapari', 'Linhares', 'Serra', 'Vila Velha', 'Vitória'],
        'GO' => ['Anápolis', 'Aparecida de Goiânia', 'Caldas Novas', 'Goiânia', 'Pirenópolis', 'Rio Verde'],
        'MA' => ['Barreirinhas', 'Caxias', 'Imperatriz', 'São José de Ribamar', 'São Luís'],
        'MT' => ['Chapada dos Guimarães', 'Cuiabá', 'Rondonópolis', 'Sinop', 'Várzea Grande'],
        'MS' => ['Bonito', 'Campo Grande', 'Corumbá', 'Dourados', 'Três Lagoas'],
        'MG' => [
            'Belo Horizonte',
            'Betim',
            'Brumadinho',
            'Contagem',
            'Diamantina',
            'Divinópolis',
            'Governador Valadares',
            'Ipatinga',
            'Juiz de Fora',
            'Lavras',
            'Mariana',
            'Montes Claros',
            'Nova Lima',
            'Ouro Preto',
            'Poços de Caldas',
            'Pouso Alegre',
            'Sete Lagoas',
            'Tiradentes',
            'Uberaba',
            'Uberlândia',
        ],
        'PA' => ['Alter do Chão', 'Ananindeua', 'Belém', 'Castanhal', 'Marabá', 'Santarém'],
        'PB' => ['Cabedelo', 'Campina Grande', 'Conde', 'João Pessoa', 'Patos'],
        'PR' => [
            'Antonina',
            'Cascavel',
            'Curitiba',
            'Foz do Iguaçu',
            'Londrina',
            'Maringá',
            'Morretes',
            'Ponta Grossa',
            'São José dos Pinhais',
        ],
        'PE' => [
            'Caruaru',
            'Fernando de Noronha',
            'Ipojuca',
            'Jaboatão dos Guararapes',
            'Olinda',
            'Petrolina',
            'Porto de Galinhas',
            'Recife',
        ],
        'PI' => ['Luís Correia', 'Parnaíba', 'Picos', 'Teresina'],
        'RJ' => [
            'Angra dos Reis',
            'Armação dos Búzios',
            'Arraial do Cabo',
            'Cabo Frio',
            'Campos dos Goytacazes',
            'Itaipava',
            'Macaé',
            'Niterói',
            'Nova Friburgo',
            'Paraty',
            'Petrópolis',
            'Rio de Janeiro',
            'Teresópolis',
            'Volta Redonda',
        ],
        'RN' => ['Mossoró', 'Natal', 'Parnamirim', 'Pipa', 'São Miguel do Gostoso', 'Tibau do Sul'],
        'RS' => [
            'Bento Gonçalves',
            'Canela',
            'Canoas',
            'Caxias do Sul',
            'Gramado',
            'Gravataí',
            'Novo Hamburgo',
            'Passo Fundo',
            'Pelotas',
            'Porto Alegre',
            'Santa Maria',
            'São Leopoldo',
            'Torres',
        ],
        'RO' => ['Ariquemes', 'Cacoal', 'Ji-Paraná', 'Porto Velho', 'Vilhena'],
        'RR' => ['Boa Vista', 'Caracaraí', 'Rorainópolis'],
        'SC' => [
            'Balneário Camboriú',
            'Blumenau',
            'Bombinhas',
            'Chapecó',
            'Criciúma',
            'Florianópolis',
            'Governador Celso Ramos',
            'Itajaí',
            'Itapema',
            'Jaraguá do Sul',
            'Joinville',
            'Lages',
            'São José',
        ],
        'SP' => [
            'Águas de São Pedro',
            'Americana',
            'Araçatuba',
            'Araraquara',
            'Atibaia',
            'Barueri',
            'Bauru',
            'Bragança Paulista',
            'Campinas',
            'Campos do Jordão',
            'Cotia',
            'Franca',
            'Guarujá',
            'Guarulhos',
            'Ilhabela',
            'Indaiatuba',
            'Itu',
            'Jundiaí',
            'Marília',
            'Mogi das Cruzes',
            'Piracicaba',
            'Presidente Prudente',
            'Ribeirão Preto',
            'Santo André',
            'Santos',
            'São Bernardo do Campo',
            'São Caetano do Sul',
            'São Carlos',
            'São José do Rio Preto',
            'São José dos Campos',
            'São Paulo',
            'São Sebastião',
            'Sorocaba',
            'Ubatuba',
            'Vinhedo',
        ],
        'SE' => ['Aracaju', 'Itabaiana', 'Nossa Senhora do Socorro', 'São Cristóvão'],
        'TO' => ['Araguaína', 'Gurupi', 'Palmas', 'Porto Nacional'],
    ];

    /**
     * Return all 27 Brazilian states indexed by UF code.
     *
     * @return array<string, string>
     */
    public static function states(): array
    {
        return self::STATES;
    }

    /**
     * Return the list of valid UF codes.
     *
     * @return list<string>
     */
    public static function stateCodes(): array
    {
        return array_keys(self::STATES);
    }

    /**
     * Return all states mapped to their pt_BR-sorted cities.
     *
     * @return array<string, list<string>>
     */
    public static function allCitiesByState(): array
    {
        return self::CITIES_BY_STATE;
    }

    /**
     * Return sorted cities for a given UF code.
     *
     * @return list<string>
     */
    public static function citiesForState(?string $state): array
    {
        $normalized = Str::upper(trim((string) $state));

        if ($normalized === '') {
            return [];
        }

        return self::allCitiesByState()[$normalized] ?? [];
    }

    /**
     * Check whether the given city belongs to the given UF.
     */
    public static function isValidCityForState(?string $state, ?string $city): bool
    {
        if (blank($city)) {
            return false;
        }

        return in_array(trim($city), self::citiesForState($state), true);
    }
}
