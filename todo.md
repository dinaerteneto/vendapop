# Todo - Fluxo de Compras Vestezap

## Fluxo de Pedido

### 1. Pedido pelo App
- Pagamento é feito **antes** de falar com o vendedor pelo WhatsApp
- Cliente faz o pedido através do app

### 2. Mensagem no WhatsApp do Vendedor
Quando o cliente finaliza o pedido, o vendedor recebe no WhatsApp:
- Listagem das peças e valores
- Campo de observações para o cliente preencher:
  - Endereço de entrega
  - Observações de cores
  - Outras informações relevantes

### 3. Validação de Estoque
- **Antes de finalizar a compra**: verificar se o produto está em estoque
- Se **não houver estoque**: cliente **não consegue finalizar** a compra
- Cliente deve **voltar no carrinho** para substituir a mercadoria

### 4. Pagamento PIX
- Na finalização, o app deve ter funcionalidade de **copiar e colar PIX**
- Envia um link que cai no WhatsApp do vendedor
- O próprio cliente envia o comprovante pelo WhatsApp

## Observações
- Por enquanto está tranquilo, ideia genial
- Pensar um pouco mais à frente: se houver um pedido no celular, mostrar conversa ao vendedor
