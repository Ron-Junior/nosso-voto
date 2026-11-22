<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PinterestInspiration;
use App\Models\User;
use App\Services\PinterestMetadataResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InspirationClipController extends Controller
{
    /**
     * Save a Pinterest pin or board clipped via the Chrome Extension or external API integration.
     */
    public function __invoke(Request $request): JsonResponse|Response
    {
        if ($request->isMethod('OPTIONS')) {
            return response('', 204, $this->corsHeaders());
        }

        $token = $this->resolveApiToken($request);

        if ($token === null) {
            return $this->jsonResponse([
                'message' => 'Token de API não informado.',
            ], 401);
        }

        $user = User::query()->where('api_token', $token)->first();

        if ($user === null) {
            return $this->jsonResponse([
                'message' => 'Token de API inválido.',
            ], 401);
        }

        if (! ($user->isCouple() || $user->isAdmin())) {
            return $this->jsonResponse([
                'message' => 'Apenas contas de noivos podem salvar inspirações via extensão.',
            ], 403);
        }

        $rawUrl = trim((string) ($request->input('pinterest_url') ?? $request->input('url') ?? ''));
        $rawNotes = $request->input('bride_notes') ?? $request->input('notes');
        $rawType = $request->input('type');
        $resolvedType = filled($rawType)
            ? (string) $rawType
            : PinterestInspiration::inferTypeFromUrl($rawUrl);

        $validator = Validator::make([
            'category' => $request->input('category', PinterestInspiration::CATEGORY_DECORACAO),
            'type' => $resolvedType,
            'pinterest_url' => $rawUrl,
            'title' => $request->input('title'),
            'image_url' => $request->input('image_url'),
            'bride_notes' => $rawNotes,
        ], [
            'category' => ['required', Rule::in(PinterestInspiration::CATEGORIES)],
            'type' => ['required', Rule::in(PinterestInspiration::TYPES)],
            'pinterest_url' => ['required', 'url', 'max:2048'],
            'title' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'bride_notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'category.required' => 'Informe a categoria da inspiração.',
            'category.in' => 'Categoria inválida. Use decoracao, maquiagem ou penteado.',
            'type.in' => 'Tipo inválido. Use pin ou board.',
            'pinterest_url.required' => 'Informe a URL do Pin ou Pasta do Pinterest.',
            'pinterest_url.url' => 'Informe uma URL válida do Pinterest.',
            'image_url.url' => 'Informe uma URL de imagem válida.',
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse([
                'message' => 'Dados inválidos para salvar inspiração.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();
        $wedding = $user->resolveOrCreateWedding();
        $pinterestUrl = trim($validated['pinterest_url']);
        $imageUrl = filled($validated['image_url'] ?? null) ? trim((string) $validated['image_url']) : null;
        $rawTitle = $validated['title'] ?? null;

        if ($imageUrl === null) {
            $resolvedMeta = app(PinterestMetadataResolver::class)->resolve($pinterestUrl);
            $imageUrl = $resolvedMeta['image_url'];

            if (blank($rawTitle) && filled($resolvedMeta['title'])) {
                $rawTitle = Str::limit($resolvedMeta['title'], 255, '');
            }
        }

        $inspiration = $wedding->pinterestInspirations()->create([
            'category' => $validated['category'],
            'type' => $validated['type'],
            'title' => PinterestInspiration::fallbackTitle(
                $rawTitle,
                $validated['category'],
                $validated['type'],
            ),
            'pinterest_url' => $pinterestUrl,
            'image_url' => $imageUrl,
            'bride_notes' => filled($validated['bride_notes'] ?? null) ? trim((string) $validated['bride_notes']) : null,
            'source' => PinterestInspiration::SOURCE_CHROME_EXTENSION,
        ]);

        return $this->jsonResponse([
            'message' => 'Inspiração salva com sucesso no Nosso Voto.',
            'data' => [
                'id' => $inspiration->id,
                'wedding_id' => $inspiration->wedding_id,
                'category' => $inspiration->category,
                'type' => $inspiration->type,
                'title' => $inspiration->title,
                'pinterest_url' => $inspiration->pinterest_url,
                'image_url' => $inspiration->image_url,
                'bride_notes' => $inspiration->bride_notes,
                'source' => $inspiration->source,
            ],
        ], 201);
    }

    private function resolveApiToken(Request $request): ?string
    {
        $bearer = $request->bearerToken();

        if (filled($bearer)) {
            return trim($bearer);
        }

        $headerToken = $request->header('X-Api-Token');

        if (filled($headerToken)) {
            return trim((string) $headerToken);
        }

        $rawAuthorization = trim((string) $request->header('Authorization', ''));

        if ($rawAuthorization !== '' && ! str_starts_with(strtolower($rawAuthorization), 'bearer ')) {
            return $rawAuthorization;
        }

        $bodyToken = $request->input('api_token');

        return filled($bodyToken) ? trim((string) $bodyToken) : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function jsonResponse(array $payload, int $status): JsonResponse
    {
        return response()->json($payload, $status, $this->corsHeaders());
    }

    /**
     * @return array<string, string>
     */
    private function corsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Api-Token, Accept, Origin',
            'Access-Control-Max-Age' => '86400',
        ];
    }
}
