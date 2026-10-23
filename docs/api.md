# API REST

Base URL local: `http://127.0.0.1:8000/api/v1`.

## Autenticação

Envia `POST /auth/login` com email, password e, opcionalmente, `device_name`. O token retornado deve ser enviado nos pedidos protegidos no cabeçalho `Authorization: Bearer <token>`. O logout revoga o token usado no pedido.

Os tokens expiram ao fim do tempo definido por `SANCTUM_EXPIRATION` (480 minutos por omissão).

## Pontos de acesso

| Método | Endereço | Autenticação | Descrição | Respostas |
|---|---|---|---|---|
| POST | `/auth/login` | Pública, 5 tentativas/minuto | Cria token | 200, 422, 429 |
| POST | `/auth/logout` | Token | Revoga token atual | 204, 401 |
| GET | `/auth/me` | Token | Utilizador autenticado | 200, 401 |
| GET | `/products` | Pública | Lista produtos ativos, 15 por página | 200 |
| GET | `/products/{product}` | Pública | Consulta produto ativo | 200, 404 |
| POST | `/products` | Token de administrador | Cria produto | 201, 401, 403, 422 |
| PUT/PATCH | `/products/{product}` | Token de administrador | Atualiza produto | 200, 401, 403, 404, 422 |
| DELETE | `/products/{product}` | Token de administrador | Elimina produto e ficheiros de imagem | 204, 401, 403, 404 |

A listagem aceita `category` (ID), `q` (texto no nome), `sort` (`name`, `price`, `newest`) e `page`. Os preços são inteiros em cêntimos. O slug é gerado a partir do nome se for omitido na criação ou atualização.

## Exemplos

Login:

```http
POST /api/v1/auth/login
Content-Type: application/json

{"email":"admin@loja.test","password":"password","device_name":"Postman"}
```

Resposta `200`:

```json
{"token":"1|abc...","user":{"id":1,"name":"Admin","email":"admin@loja.test","role":"admin"}}
```

Criar produto (com `Authorization: Bearer <token>`):

```json
{"category_id":1,"name":"Camisola","description":"Algodão orgânico","price_cents":2599,"is_active":true}
```

Resposta `201` inclui o recurso em `data` e o cabeçalho `Location`. A listagem retorna `data`, `links` e `meta` de paginação. Cada produto contém preço formatado, categoria, variantes e imagens.

## Erros

Os erros são JSON com `message`. Erros de validação `422` também incluem `errors`, um mapa de campos para listas de mensagens. Códigos usados: `401` não autenticado/token inválido; `403` utilizador sem papel de administrador; `404` recurso inexistente ou inativo; `422` validação/credenciais inválidas; `429` limite de tentativas excedido.
