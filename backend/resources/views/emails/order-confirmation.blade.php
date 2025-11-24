<x-mail::message>
# Pedido Confirmado! 🎉

Olá **{{ $order->customer->name }}**,

Seu pedido foi recebido com sucesso na loja **{{ $order->tenant->name }}**!

## Detalhes do Pedido

**Número do Pedido:** #{{ $order->order_number }}

**Data:** {{ $order->created_at->format('d/m/Y H:i') }}

**Valor Total:** R$ {{ number_format($order->total_amount, 2, ',', '.') }}

**Status:** {{ ucfirst($order->status) }}

## Itens do Pedido

@foreach($order->items as $item)
- **{{ $item->product_name }}**
  - Quantidade: {{ $item->quantity }}
  @if($item->size)
  - Tamanho: {{ $item->size }}
  @endif
  @if($item->color)
  - Cor: {{ $item->color }}
  @endif
  - Subtotal: R$ {{ number_format($item->subtotal, 2, ',', '.') }}

@endforeach

@if($order->notes)
## Observações

{{ $order->notes }}
@endif

## Acompanhar Pedido

Clique no botão abaixo para acompanhar o status do seu pedido:

<x-mail::button :url="$orderUrl">
Acompanhar Pedido
</x-mail::button>

Ou copie e cole este link no seu navegador:
{{ $orderUrl }}

## Próximos Passos

Em breve você receberá um contato da loja para confirmar os detalhes e finalizar seu pedido.

Obrigado pela sua compra!<br>
**{{ $order->tenant->name }}**

---

<small>Este é um email automático, por favor não responda.</small>
</x-mail::message>

