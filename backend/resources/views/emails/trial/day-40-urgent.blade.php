@php
    $utm = 'utm_source=email&utm_medium=trial&utm_campaign=day_40';
    $upgradeUrl = config('app.url') . '/admin/assinatura?' . $utm;
    $unsubscribeUrl = config('app.url') . '/unsubscribe?email={{ $tenant->email_contact ?? "email@exemplo.com" }}&' . $utm;
@endphp

<x-mail::message>
# ⏰ Seu Básico grátis termina em 5 dias

Olá, {{ $tenant->name }}!

**Atenção:** o período Básico grátis da sua loja **{{ $tenant->name }}** termina em **apenas 5 dias**!

## O que você perde voltando pro plano Grátis:

- ❌ Produtos ilimitados (limite de 30 produtos)
- ❌ Relatórios completos
- ❌ Experiência sem anúncios

<x-mail::button :url="$upgradeUrl">
Garantir Meu Plano Agora
</x-mail::button>

Não perca esses benefícios! ⏳<br>
Equipe **{{ config('app.name') }}**

---

<x-mail::subcopy>
Se não quiser mais receber esses e-mails, <a href="{{ $unsubscribeUrl }}">descadastre-se aqui</a>.
</x-mail::subcopy>
</x-mail::message>
