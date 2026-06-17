<x-mail::message>
# Acesso ao {{ config('app.name') }}

Você solicitou um código de acesso para o e-mail **{{ $email }}**.

@if($otpCode)
## Código de Verificação

**Seu código é:** {{ $otpCode }}

Este código expira em **10 minutos**.
@endif

@if($magicLinkUrl)
## Link Mágico (Acesso Direto)

<x-mail::button :url="$magicLinkUrl">
Acessar {{ config('app.name') }}
</x-mail::button>

Ou copie e cole este link no seu navegador:
{{ $magicLinkUrl }}

Este link expira em **30 minutos**.
@endif

Se você não solicitou este código, ignore este e-mail.

Obrigado,<br>
Equipe {{ config('app.name') }}
</x-mail::message>
