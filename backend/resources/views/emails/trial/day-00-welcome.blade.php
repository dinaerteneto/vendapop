@php
    $utm = 'utm_source=email&utm_medium=trial&utm_campaign=day_00';
    $dashboardUrl = config('app.url') . '/admin?' . $utm;
    $storeUrl = config('app.url') . '/loja/' . $tenant->slug . '?' . $utm;
    $unsubscribeUrl = config('app.url') . '/unsubscribe?email={{ $tenant->email_contact ?? "email@exemplo.com" }}&' . $utm;
@endphp

<x-mail::message>
# Sua loja **{{ $tenant->name }}** está no ar! 🚀

Olá, {{ $tenant->name }}!

Sua loja foi criada com sucesso e já está disponível para receber pedidos. Agora você pode começar a vender seus produtos diretamente pelo WhatsApp.

## O que você pode fazer agora:

- 📦 **Cadastrar produtos** e categorias
- 🎨 **Personalizar sua loja** com suas cores e logo
- 📱 **Compartilhar o link** da sua loja nas redes sociais
- 📊 **Acompanhar pedidos** em tempo real

<x-mail::button :url="$dashboardUrl">
Acessar Painel
</x-mail::button>

<x-mail::button :url="$storeUrl" variant="success">
Ver Minha Loja
</x-mail::button>

Precisa de ajuda? Responda a este e-mail ou acesse nossa central de ajuda.

Obrigado,<br>
Equipe **{{ config('app.name') }}**

---

<x-mail::subcopy>
Se não quiser mais receber esses e-mails, <a href="{{ $unsubscribeUrl }}">descadastre-se aqui</a>.
</x-mail::subcopy>
</x-mail::message>
