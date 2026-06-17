<x-mail::message>
# Redefinição de Senha

Olá {{ $user->name }},

Você solicitou a redefinição de senha para sua conta no {{ config('app.name') }}.

Clique no botão abaixo para redefinir sua senha:

<x-mail::button :url="$resetUrl">
Redefinir Senha
</x-mail::button>

Se você não solicitou esta redefinição, ignore este e-mail.

Este link expira em 60 minutos.

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
