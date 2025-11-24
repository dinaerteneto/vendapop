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
<div style="margin-bottom: 20px; padding: 15px; background-color: #f9fafb; border-radius: 8px; border-left: 4px solid #7c3aed;">
  <table style="width: 100%; border-collapse: collapse;">
    <tr>
      @if($item->product && $item->product->main_image_url)
      <td style="width: 80px; vertical-align: top; padding-right: 15px;">
        <img src="{{ $item->product->main_image_url }}" alt="{{ $item->product_name }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;">
      </td>
      @endif
      <td style="vertical-align: top;">
        <p style="margin: 0 0 8px 0; font-weight: bold; font-size: 16px; color: #111827;">{{ $item->product_name }}</p>
        <p style="margin: 4px 0; font-size: 14px; color: #6b7280;">
          Quantidade: <strong>{{ $item->quantity }}</strong>
        </p>
        @if($item->size)
        <p style="margin: 4px 0; font-size: 14px; color: #6b7280;">
          Tamanho: <strong>{{ $item->size }}</strong>
        </p>
        @endif
        @if($item->color)
        <p style="margin: 4px 0; font-size: 14px; color: #6b7280;">
          Cor: <strong>{{ $item->color }}</strong>
        </p>
        @endif
        <p style="margin: 8px 0 0 0; font-size: 16px; font-weight: bold; color: #111827;">
          Subtotal: R$ {{ number_format($item->subtotal, 2, ',', '.') }}
        </p>
      </td>
    </tr>
  </table>
</div>
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

