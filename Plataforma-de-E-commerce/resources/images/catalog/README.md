# Fotografias do catálogo de demonstração

## Imagens ilustrativas geradas

Os 16 ficheiros em `generated/` foram gerados com IA em 13/11/2026 a partir dos nomes, descrições e cores dos produtos de demonstração que não tinham fotografias. São ilustrações fotográficas do tipo de peça, não fotografias dos artigos reais.

`php artisan db:seed --class=GeneratedProductImagesSeeder` associa estas imagens apenas aos produtos sem fotografias, sem alterar os seus dados, stock ou imagens existentes. Este seeder também é executado pelo `CatalogSeeder`.

As 12 fotografias foram obtidas nas páginas públicas da Zara Portugal em 12/11/2026, a pedido do responsável pelo projeto. As URLs de origem e das imagens estão em `database/seeders/data/zara-catalog.json`. As fotografias pertencem aos respetivos titulares; a sua inclusão não implica uma licença de utilização comercial.

Os nomes e descrições são adaptados ao catálogo de demonstração. Os preços são exemplos em cêntimos e o stock é fictício; não representam a disponibilidade atual da Zara.

Os ficheiros locais em `resources/images/catalog` permitem executar os seeders sem acesso à internet. `PhotoCatalogSeeder` copia-os para o disco público e cria os 12 produtos sem substituir o stock ou os dados de produtos já existentes. Pode ser executado isoladamente com `php artisan db:seed --class=PhotoCatalogSeeder`.

## Coleção Bikinis

As seis fotografias adicionais `bikini-1.jpg` a `bikini-6.jpg` têm as respetivas fontes em `database/seeders/data/bikini-catalog.json`. Aplicam-se as mesmas condições de demonstração acima. Os tops e as partes inferiores são apresentados como artigos separados.

`php artisan db:seed --class=BikiniCatalogSeeder` acrescenta a coleção sem substituir os dados ou o stock existentes. O seeder principal do catálogo também inclui esta coleção.
