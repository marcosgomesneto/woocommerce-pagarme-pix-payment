# Pix Automático com Pagarme para WooCommerce #

O Plugin gera um QR code para pagamento PIX na finalização da compra. O cliente só precisa clicar no botão "Copiar Código" e então ir no aplicativo do banco, entrar na opção "PIX Copia e Cola" e fazer o pagamento.

Quando ele terminar de pagar e voltar para o site, o plugin recebe a confirmação de pagamento e automaticamente uma animação é mostrada com a mensagem de "Pagamento Confirmado".

O pedido é alterado para processando automaticamente.

Não é preciso pedir comprovantes para o cliente pois o pagamento é confirmado direto pela API da Pagarme com o Plugin.

Esse plugin é integrado com a plataforma de pagamento **Pagar.me**, portanto você precisa criar uma conta caso não tenha.

Você precisa ter as duas chaves, o API KEY e Encryption Key da **Pagar.me** para que o Plugin funcione.

Para gerar a API Key e Encryption Key é fácil, só entrar na dashboard da Pagar.me. Caso você já tenha a Pagarme como método de pagamento de cartão de crédito e boleto no seu site, você não precisa gerar novas Chaves, use as mesmas adicionando nas configurações do plugin.

Para adicionar essas duas chaves nas configurações do nosso plugin é só ir no menu WooCommerce -> Configurações -> Na aba "Pagamentos", clicar em configuração do "Pix Instantâneo" e preencher os campos com as respectivas chaves.

Muitas vezes a opção de receber PIX pela Pagar.me vem desativada por padrão, mas é bem simples de ativá-la, você pode entrar em contato com a Pagar.me pelo chat deles que eles liberam na mesma hora.

Compativel com a última versão do Woocommerce.

## 1.2.0 ##

* QR Code de pagamento PIX na tela de pedidos
* Instruções de pagamento PIX no E-mail
* Agora é possivel definir a localização da imagem do QR Code e do botão no texto
* Texto no finalizar compra pode ser feito em HTML
* Correções de bugs para temas que não tem bootstrap ( Centralização de textos padrões )

## 1.1.0 ##

* Customização de textos nas mensagens na tela de pagamento
* Otimização do código

## 1.0.1 ##

* Correções de bugs

## 1.0.0  ##

* Versão inicial do plugin.
