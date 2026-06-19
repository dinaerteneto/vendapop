@php
    $utm = 'utm_source=email&utm_medium=trial&utm_campaign=day_15';
    $dashboardUrl = config('app.url') . '/admin?' . $utm;
    $unsubscribeUrl = config('app.url') . '/unsubscribe?email={{ $tenant->email_contact ?? "email@exemplo.com" }}&' . $utm;
@endphp

<x-mail::message>
# 3 dicas pra divulgar sua loja no Instagram

Olá, {{ $tenant->name }}!

O Instagram é uma das melhores formas de divulgar sua loja **{{ $tenant->name }}** de graça. Separamos 3 dicas práticas:

## 📸 1. Mostre os produtos no Stories
Publique fotos dos seus produtos nos Stories do Instagram com o link da sua loja. Use enquetes e caixinhas de perguntas para engajar.

## 🏷️ 2. Use hashtags certas
Combine hashtags populares (#moda, #acessórios) com hashtags de sua cidade (#suaCidade, #comprasNaSuaCidade).

## 💬 3. Responda comentários e DMs
Cada resposta é uma oportunidade de vender. Tenha o link da sua loja sempre salvo para compartilhar rápido.

<x-mail::button :url="$dashboardUrl">
Divulgar Minha Loja Agora
</x-mail::button>

Bora vender! 💪<br>
Equipe **{{ config('app.name') }}**

---

<x-mail::subcopy>
Se não quiser mais receber esses e-mails, <a href="{{ $unsubscribeUrl }}">descadastre-se aqui</a>.
</x-mail::subcopy>
</x-mail::message>
