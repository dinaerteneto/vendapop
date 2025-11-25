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

#### 3.1. Sistema de Estoque por Combinação de Atributos
- **Estoque é por combinação de atributos, não por produto**
  - Exemplo: Camisa básica P+Azul tem estoque 5, P+Verde tem estoque 0, M+Azul tem estoque 3
- **Sistema simples inicial**:
  - Lojistas que **não controlam estoque**: continuam usando `sizes` e `colors` como JSON (sem cadastro de atributos)
  - Lojistas que **controlam estoque**: sistema migra automaticamente `sizes` e `colors` para tabelas de atributos quando ativarem controle de estoque
- **Estrutura de dados** (quando controle de estoque estiver ativo):
  - `product_attributes` (tenant_id, name, slug, order) - Atributos da loja (ex: "Tamanho", "Cor", "Gola")
  - `product_attribute_values` (attribute_id, value, order) - Valores dos atributos (ex: "P", "M", "Azul")
  - `product_variations` (product_id, attributes_json, stock, price, sku) - Combinações com estoque
- **Migração automática**: Quando lojista ativa controle de estoque, sistema:
  1. Cria atributo "Tamanho" se produto tiver `sizes`
  2. Cria atributo "Cor" se produto tiver `colors`
  3. Cria variações para todas as combinações existentes
  4. Mantém dados originais para rollback se necessário
- **Validação no checkout**: Verificar `variation.stock >= item.quantity` para cada combinação selecionada

### 4. Pagamento PIX
- Na finalização, o app deve ter funcionalidade de **copiar e colar PIX**
- Envia um link que cai no WhatsApp do vendedor
- O próprio cliente envia o comprovante pelo WhatsApp
- **Campos necessários na tabela `tenants`**:
  - `pix_key` (string, nullable) - Chave PIX do vendedor
  - `pix_key_type` (enum: 'cpf', 'cnpj', 'email', 'phone', 'random') - Tipo da chave
- **Na página de confirmação do pedido**:
  - Mostrar chave PIX para copiar
  - Mostrar QR Code PIX (opcional, usar biblioteca como `endroid/qr-code`)
  - Botão "Enviar comprovante via WhatsApp" que abre WhatsApp com mensagem pré-formatada

### 5. Inativar temporariamente
- Alguns clientes, precisam trocar constantemente seus produtos (colocar coleção nova).
  - E para que isto aconteça algumas vezes é necessário deixar a loja offline por algum tempo.
  - Esta funcionalidade deve aceitar uma imagem (banner), e uma mensagem para aparecer na loja.
- **Campos necessários na tabela `tenants`**:
  - `is_temporarily_inactive` (boolean, default false)
  - `inactive_banner_image_url` (string, nullable)
  - `inactive_banner_image_path` (string, nullable)
  - `inactive_message` (text, nullable)
  - `inactive_until` (timestamp, nullable) - Data de reativação automática
- **Comportamento**:
  - Middleware `CheckTenant` verifica se loja está inativa
  - Se inativa: mostra página especial com banner + mensagem
  - Bloqueia acesso ao catálogo e checkout
  - Opção de agendar reativação automática

### 6. Cancelar plano
  - Pode voltar para o plano gratuito, ou pode remover a loja.
  - Remover a loja:
    - Vamos marcar como inativo e deixar o slug disponível para que outra loja possa usar o mesmo endereço.
- **Campos necessários na tabela `tenants`**:
  - `plan_type` (enum: 'free', 'basic', 'premium', 'enterprise') - default 'free'
  - `plan_status` (enum: 'active', 'cancelled', 'suspended') - default 'active'
  - `cancelled_at` (timestamp, nullable)
  - `cancellation_reason` (text, nullable)
- **Comportamento**:
  - Ao cancelar: escolher "voltar para gratuito" ou "remover loja"
  - Se "voltar para gratuito": `plan_type = 'free'`, aplicar limites do plano
  - Se "remover loja": `plan_status = 'cancelled'`, `is_active = false`, liberar slug
  - Considerar período de carência (ex: 30 dias) antes de liberar slug definitivamente


## Observações
- Por enquanto está tranquilo, ideia genial
- Pensar um pouco mais à frente: se houver um pedido no celular, mostrar conversa ao vendedor

## Fellings
Os tenants podem ser:
  - Varejistas:
    - Vendem de forma unitária, normalmente seus produtos são rotativos.
    - Podem ou não controlar estoque (sistema simples para quem não controla)
  - Atacadistas
    - Estes devem ter controle de estoque e também qtd mínima para venda.
    - Sistema migra automaticamente atributos simples (sizes, colors) quando ativam controle de estoque

### Ação do Botão no Produto
Cada produto pode ter uma ação diferente no botão principal:
- **Adicionar ao carrinho** (padrão): Comportamento normal, adiciona produto ao carrinho
- **Link de afiliado**: Botão "Comprar agora" → abre link externo em nova aba
  - Campo `affiliate_link` no produto
  - Usado por pessoas que anunciam produtos de afiliados
- **Contato WhatsApp**: Botão "Fale com um vendedor" → abre WhatsApp do vendedor
  - Campo `whatsapp_message` (opcional, usa padrão se vazio)
  - Usado por imobiliárias, corretores, etc.
- **Campos no produto**:
  - `action_type` (enum: 'add_to_cart', 'affiliate_link', 'whatsapp_contact') - default 'add_to_cart'
  - `affiliate_link` (string, nullable) - URL do link de afiliado
  - `whatsapp_message` (text, nullable) - Mensagem personalizada para WhatsApp
- **UI no cadastro**: Radio button para selecionar ação do botão

## Planos
Os planos no site serão:
  - Gratuito: Direto a 6 produtos, porém irá apresentar anúncio para o tenant e para o cliente do tenant.
  - R$ 9,90 mensal - Igual ao de cima porém sem anúncios.
  - R$ 19,90 mensal - Sem anúncio e com produtos ilimitados.
  - R$ xx,xx mensal - No futuro teremos integraçao com gateway de pagamento.
  
## Ramos de atividade de possíveis clientes:
  - Imobiliários
  - Eletrônicos
  - Roupas
  - Joias
  - Bolo caseiro
  - Encomendas

## Landing page
  - Vamos adicionar imagens das lojas que temos para mostrar os cases
  - Adicionar página de contato
  - Adicionar página de planos
  