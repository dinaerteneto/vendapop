<x-mail::message>
# Novo Pedido Recebido!

Você recebeu um novo pedido na sua loja **{{ $order->tenant->name }}**.

## Detalhes do Pedido

**Número do Pedido:** #{{ $order->order_number }}

**Cliente:** {{ $order->customer->name }}

**Valor Total:** R$ {{ number_format($order->total_amount, 2, ',', '.') }}

**Status:** {{ ucfirst($order->status) }}

@if($order->customer->phone)
**Telefone:** {{ $order->customer->phone }}
@endif

@if($order->customer->email)
**E-mail:** {{ $order->customer->email }}
@endif

@if($order->notes)
**Observações do Cliente:**
{{ $order->notes }}
@endif

## Acessar Pedido

Clique no botão abaixo para visualizar e gerenciar o pedido:

<x-mail::button :url="$orderUrl">
Ver Pedido
</x-mail::button>

Ou copie e cole este link no seu navegador:
{{ $orderUrl }}

Obrigado,<br>
Equipe {{ config('app.name') }}
</x-mail::message>
