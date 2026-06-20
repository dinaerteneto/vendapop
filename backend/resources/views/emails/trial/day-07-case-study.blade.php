@php
    $utm = 'utm_source=email&utm_medium=trial&utm_campaign=day_07';
    $dashboardUrl = config('app.url') . '/admin?' . $utm;
    $unsubscribeUrl = config('app.url') . '/unsubscribe?email={{ $tenant->email_contact ?? "email@exemplo.com" }}&' . $utm;
@endphp

<x-mail::message>
# Como a **{{ $caseStore }}** recebeu 8 pedidos na primeira semana

Olá, {{ $tenant->name }}!

Sabia que muitos lojistas como você já estão faturando com o **{{ config('app.name') }}**? A {{ $caseStore }} é um exemplo disso: em apenas uma semana, ela já havia recebido **8 pedidos** usando apenas o WhatsApp e Instagram.

## O que a {{ $caseStore }} fez certo:

1. **Cadastrou todos os produtos** com fotos de qualidade e descrições detalhadas
2. **Compartilhou o link** da loja no Instagram e grupos de WhatsApp
3. **Manteve os preços atualizados** e respondeu rápido aos clientes

<x-mail::button :url="$dashboardUrl">
Cadastrar Meus Produtos Agora
</x-mail::button>

O segredo é começar! Sua loja {{ $tenant->name }} já está pronta — agora é só divulgar.

Continue vendendo,<br>
Equipe **{{ config('app.name') }}**

---

<x-mail::subcopy>
Se não quiser mais receber esses e-mails, <a href="{{ $unsubscribeUrl }}">descadastre-se aqui</a>.
</x-mail::subcopy>
</x-mail::message>
