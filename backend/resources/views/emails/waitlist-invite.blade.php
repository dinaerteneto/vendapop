<x-mail::message>
# Você foi convidado para o PopVenda! 🎉

Olá! Sua inscrição na lista de espera do **PopVenda** foi aprovada.

Use o código abaixo para criar sua loja:

<x-mail::panel>
**{{ $inviteCode }}**
</x-mail::panel>

Ou clique no botão abaixo:

<x-mail::button :url="$inviteLink">
Criar minha loja
</x-mail::button>

Se o botão não funcionar, copie e cole o link abaixo no navegador:

[{{ $inviteLink }}]({{ $inviteLink }})

Obrigado,<br>
Equipe PopVenda
</x-mail::message>
