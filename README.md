# Pix Automático com Pagarme para WooCommerce

** AGORA TAMBÉM DISPONÍVEL PARA V5 e V4 DA PAGARME **

Para incentivar o projeto, de uma ⭐ no repositório.

O Plugin gera um QR code para pagamento PIX na finalização da compra. O cliente só precisa clicar no botão "Copiar Código" e então ir no aplicativo do banco, entrar na opção "PIX Copia e Cola" e fazer o pagamento.

Quando ele terminar de pagar e voltar para o site, o plugin recebe a confirmação de pagamento e automaticamente uma animação é mostrada com a mensagem de "Pagamento Confirmado".

O pedido é alterado para processando automaticamente. (Você pode alterar para que o pedido mude para outro status de sua preferência)

Não é preciso pedir comprovantes para o cliente pois o pagamento é confirmado direto pela API da Pagarme com o Plugin.

### Recursos:

- Escolher o tempo de expiração do qr code (Em dias ou horas)
- Escolher cancelar automaticamente o pedido quando o qr code expirar
- Customizar as mensagens de email e da tela de pagamento
- Escolher a cor do icone do pix nas opções de pagamento
- Entre outras coisas
- Se quiser sugerir uma alteração, acesse a guia suporte na pagina do plugin

### Doação

Sinta-se à vontade de fazer uma doação pela chave aleatória:

`58a2463e-0e6b-4b00-aa7d-c62c6c4b712a`

Esse plugin é integrado com a plataforma de pagamento **Pagar.me**, portanto você precisa criar uma conta caso não tenha.

### Tutorial

Você precisa ter as duas chaves, o API KEY e Encryption Key da **Pagar.me** para que o Plugin funcione.

Para gerar a API Key e Encryption Key é fácil, só entrar na dashboard da Pagar.me. Caso você já tenha a Pagarme como método de pagamento de cartão de crédito e boleto no seu site, você não precisa gerar novas Chaves, use as mesmas adicionando nas configurações do plugin.

Para adicionar essas duas chaves nas configurações do nosso plugin é só ir no menu WooCommerce -> Configurações -> Na aba "Pagamentos", clicar em configuração do "Pix Instantâneo" e preencher os campos com as respectivas chaves.

Muitas vezes a opção de receber PIX pela Pagar.me vem desativada por padrão, mas é bem simples de ativá-la, você pode entrar em contato com a Pagar.me pelo chat deles que eles liberam na mesma hora.

Compativel com a última versão do Woocommerce.

### Changelog

## 2.1.3

- V4 Fix Fingerprint

## 2.1.2

- WooCommerce HPOS compatibilidade
- Wordpress Block Support

## 2.1.0

- Validação de CPF

## 2.0.9

- Adicionado informações de pagamento dentro do detalhe do pedido (caso o cliente saia da tela do pagamento)
- Corrigido Pedidos Vazios gerados quando é usado o plugin em conjunto com o Oficial da pagar.me

## 2.0.8

- Correção do Bug de número quebrado que estava dando erro ao finalizar compra
