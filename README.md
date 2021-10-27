# Gerador de Hashes

## Solução
A abordagem aplicada foi baseada no padrão API Restful, definindo um padrão nas resposta e exceções.
O design system separa responsabilidades por camadas, como segue:

#### 1 - Controllers:
- Validação dos dados recebidos nas requisições;
- Formatação de respostas.

#### 2 - Serviços:
- Regras de negócio.

#### 3 - Repositórios:
- Manipulação de dados.

#### 4 - Entidades:
- Mapeamento da base de dados.

Recursos auxiliares como paginação de respostas, filtros de dados e formatação de respostas foram aplicadas de forma modular.
Existem alguns pontos em que se pode realizar a aplicação de interfaces, inversão de dependência, padronização de respostas e melhorias na manipulação dos lançamentos de exceções.

## Instruções de instalação Docker
No diretório da aplicação clonada, execute os comandos:
```
// Copiar o arquivo .env.example e renomear para .env
$ cp .env.example .env

// Build dos containers
$ docker-compose up -d --build

// Instalação das dependências
$ docker exec -it brasiltecpar_php composer install

// Migrações
$ docker exec -it brasiltecpar_php php bin/console doctrine:migrations:migrate
```
OBS: Verifique no .env as portas dos serviços postgres e nginx
```
DOCKER_DB_PORT=5433
DOCKER_NGINX_PORT=8080
```

## Rotas
- Busca de registros
```
GET /api/hashes/{<optional page>}
    query: {
        // Filtro opcional por coluna da tabela
        filter[<table column>]=<string>
        
        // Filtra o registro com número de tentativas menor do que o informado
        filter[attempts_less_than]=<attempts>
    },
    response: {
        "data": [
            {
                "batch": {
                    "date": "2021-10-27 12:50:17.000000",
                    "timezone_type": 3,
                    "timezone": "UTC"
                },
                "block_number": 2,
                "input": "0000ff249da24dc89cbc69b6892e520009fd",
                "hash": "0000e33c25d366d111c9bc57c7bf5da4370c"
            }
        ],
        "total": 1,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1
  }
```

## Comando
- Comando para criação de hashes
```
$ docker exec -it brasiltecpar_php php bin/console avato:test <string> --requests=<total de requests>
```
