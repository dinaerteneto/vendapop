<x-mail::message>
# Bem-vindo ao VesteZap, {{ $user->name }}!

Sua loja **{{ $user->tenant->name }}** foi criada com sucesso!

## Suas Credenciais de Acesso

**E-mail:** {{ $user->email }}

**Senha:** {{ $password }}

⚠️ **Importante:** Guarde esta senha em local seguro. Recomendamos alterá-la após o primeiro acesso.

## Verificação de E-mail

Para começar a usar sua loja, precisamos verificar seu e-mail. Clique no botão abaixo para confirmar:

<x-mail::button :url="$verificationUrl">
Verificar E-mail
</x-mail::button>

Ou copie e cole este link no seu navegador:
{{ $verificationUrl }}

Este link expira em 24 horas.

Após verificar seu e-mail, você poderá:
- Cadastrar produtos e categorias
- Gerenciar pedidos
- Visualizar seus clientes
- Personalizar sua loja

Se você não criou esta conta, ignore este e-mail.

Obrigado,<br>
Equipe VesteZap
</x-mail::message>
