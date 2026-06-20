@php
    $utm = 'utm_source=email&utm_medium=trial&utm_campaign=day_30';
    $upgradeUrl = config('app.url') . '/admin/assinatura?' . $utm;
    $unsubscribeUrl = config('app.url') . '/unsubscribe?email={{ $tenant->email_contact ?? "email@exemplo.com" }}&' . $utm;
@endphp

<x-mail::message>
# Faltam 15 dias de Básico grátis — aproveite

Olá, {{ $tenant->name }}!

Sua loja **{{ $tenant->name }}** está no plano **Básico grátis** e faltam **apenas 15 dias** para ele terminar.

## Aproveite enquanto pode:

- ✅ **Produtos ilimitados** — cadastre quantos quiser
- ✅ **Sem anúncios** — experiência limpa pra seus clientes
- ✅ **Relatórios completos** — acompanhe suas vendas

Após o período, sua loja continuará no **plano Grátis** com funcionalidades essenciais.

<x-mail::button :url="$upgradeUrl">
Ver Planos Disponíveis
</x-mail::button>

Continue vendendo!<br>
Equipe **{{ config('app.name') }}**

---

<x-mail::subcopy>
Se não quiser mais receber esses e-mails, <a href="{{ $unsubscribeUrl }}">descadastre-se aqui</a>.
</x-mail::subcopy>
</x-mail::message>
