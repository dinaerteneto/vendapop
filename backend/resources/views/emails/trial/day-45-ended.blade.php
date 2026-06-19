@php
    $utm = 'utm_source=email&utm_medium=trial&utm_campaign=day_45';
    $upgradeUrl = config('app.url') . '/admin/assinatura?' . $utm;
    $unsubscribeUrl = config('app.url') . '/unsubscribe?email={{ $tenant->email_contact ?? "email@exemplo.com" }}&' . $utm;
@endphp

<x-mail::message>
# Você voltou pro plano Grátis. Continue vendendo.

Olá, {{ $tenant->name }}!

Seu período Básico grátis da loja **{{ $tenant->name }}** terminou. Agora você está no **plano Grátis** — e sua loja continua funcionando normalmente!

## O que mudou:

- 📦 Limite de **30 produtos**
- 📊 Sem relatórios avançados
- 🔒 Restrições de personalização

## Mas você pode voltar quando quiser:

<x-mail::button :url="$upgradeUrl">
Ver Planos e Preços
</x-mail::button>

Sua loja continua no ar e você pode fazer upgrade a qualquer momento para liberar todas as funcionalidades.

Continue vendendo! 💪<br>
Equipe **{{ config('app.name') }}**

---

<x-mail::subcopy>
Se não quiser mais receber esses e-mails, <a href="{{ $unsubscribeUrl }}">descadastre-se aqui</a>.
</x-mail::subcopy>
</x-mail::message>
