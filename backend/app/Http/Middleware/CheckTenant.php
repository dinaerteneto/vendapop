<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenant
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Tenta pegar o parâmetro da rota 'storeSlug'
        $slug = $request->route('storeSlug');

        if (!$slug) {
             // Se não for rota com slug, talvez seja rota admin ou pública geral.
             // Para este projeto, as rotas públicas exigem slug.
             // Admin pode não exigir se o usuário já estiver logado (mas o user tem tenant_id).
             // Vamos focar nas rotas públicas primeiro.
             return $next($request);
        }

        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        // Seta o tenant no serviço singleton
        $this->tenantService->setTenant($tenant);

        // Opcional: esquecer o parâmetro para não atrapalhar controllers?
        // $request->route()->forgetParameter('storeSlug');

        return $next($request);
    }
}

