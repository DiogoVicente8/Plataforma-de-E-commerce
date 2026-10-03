# Plataforma de E-commerce
 
Loja online de venda de roupa, desenvolvida em **Laravel 13** no âmbito da unidade curricular de **Programação Web do lado do Servidor** (2026/2027), Licenciatura em Engenharia Informática, Universidade Fernando Pessoa.
 
## Autores
 
| Nome | GitHub |
|---|---|
| Diogo Vicente | [@DiogoVicente8](https://github.com/DiogoVicente8) |
| João Reis | [@joaoreis2121](https://github.com/joaoreis2121) |

## Descrição
 
Aplicação web do lado do servidor para uma loja de roupa, com dois perfis de utilizador:
 
- **Cliente**: consulta o catálogo, guarda favoritos, usa o carrinho, gere moradas e métodos de pagamento e acompanha as suas encomendas.
- **Administrador**: gere o catálogo (produtos, categorias, variantes, stock e imagens) e as encomendas.

## Estado atual
 
| Funcionalidade | Estado |
|---|---|
| Registo, login, recuperação de password e perfil (Laravel Breeze) | Feito |
| Perfis `admin` e `customer` com acessos diferenciados (middleware `admin`) | Feito |
| Painel de administração (base) | Feito |
| Modelo de dados do catálogo e das encomendas | Em desenvolvimento |
| CRUD de produtos e categorias | Em desenvolvimento |
| Gestão de encomendas no admin | Em desenvolvimento |
| Catálogo público, favoritos, carrinho e checkout | Planeado |
| API REST e consumo de serviço externo | Planeado |
| Testes automatizados e publicação | Planeado |
 
## Tecnologias
 
- PHP 8.3 ou superior e Laravel 13
- Laravel Breeze (Blade) para autenticação
- Tailwind CSS, Alpine.js e Vite
- SQLite (em desenvolvimento)
- Pest para testes

## Estrutura do projeto
 
```
app/
  Enums/                 Enums (ex.: UserRole)
  Http/
    Controllers/         Controladores
    Middleware/          Middleware (ex.: EnsureUserIsAdmin)
    Requests/            Validação (Form Requests)
  Models/                Modelos Eloquent
database/
  migrations/            Migrações
  factories/             Factories
  seeders/               Dados de teste
resources/views/         Vistas Blade
routes/                  Rotas (web.php, auth.php)
tests/                   Testes Pest
```
 