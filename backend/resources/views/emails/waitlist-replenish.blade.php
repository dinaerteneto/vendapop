<x-mail::message>
# Novas vagas abertas! 🎉

Boas notícias! Acabamos de liberar novas vagas no **{{ config('app.name') }}**.

Agora é sua chance de criar sua loja online e começar a vender pelo WhatsApp de forma simples e rápida.

<x-mail::button :url="url('/cadastro?utm_source=email&utm_medium=waitlist&utm_campaign=replenish')">
Garantir minha vaga
</x-mail::button>

Se o botão não funcionar, copie e cole o link abaixo no navegador:

{{ url('/cadastro?utm_source=email&utm_medium=waitlist&utm_campaign=replenish') }}

Corra, as vagas são limitadas!

Obrigado,<br>
Equipe {{ config('app.name') }}
</x-mail::message>
