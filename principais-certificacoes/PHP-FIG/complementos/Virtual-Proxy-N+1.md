# 🔮 Virtual Proxy e o Problema N+1

> As fontes esclarecem uma importante ressalva arquitetural: o **Virtual Proxy** (mecanismo que viabiliza o *Lazy Loading*) **não resolve** por si só o problema N+1. Pelo contrário, quando utilizado de forma indiscriminada ao iterar sobre uma lista de entidades, ele é a causa direta da armadilha de desempenho conhecida como **Problema N+1**.

---

### 1. O Papel do Virtual Proxy (Evitando o *Eager Loading*)
O objetivo primário do Virtual Proxy é evitar o **Eager Loading** (carregamento imediato/ansioso). Em vez de fazer consultas SQL pesadas para trazer grafos inteiros de dados relacionados (como milhares de compras de um cliente), o Data Mapper acopla um proxy leve. A consulta ao banco só ocorre no momento exato em que algum método da entidade associada for invocado.

Isso economiza memória RAM e I/O de rede quando você só precisa consultar dados da entidade principal.

---

### 2. Como o Virtual Proxy Gera o Problema N+1
A armadilha surge quando o Virtual Proxy é acionado dentro de laços de repetição (loops):

1. O repositório executa **1 consulta** inicial para listar \\(N\\) entidades (por exemplo, 100 clientes).
2. O código de aplicação itera sobre os clientes e chama um método da associação proxy (ex: `$cliente->getHistorico()->getTotalCompras()`).
3. Como cada proxy é inicializado individualmente sob demanda, ele dispara **\\(N\\) consultas adicionais** ao banco de dados.

**Resultado:** \\(1 \text{ (consulta inicial)} + N \text{ (consultas dos proxies)} = N+1 \text{ consultas}\\) (ou seja, 101 chamadas ao banco).

---

### 3. Como Solucionar o N+1 em Conjunto com Proxies
Para evitar o impacto do N+1 sem abdicar da arquitetura de persistência, as fontes apontam duas estratégias principais:

* **Eager Loading com JOIN**: Quando o caso de uso sabe de antemão que precisará percorrer os relacionamentos de uma lista, orienta-se o repositório a realizar um `LEFT JOIN` na consulta inicial (ex: `findWithHistorico()`), trazendo os dados relacionados de uma só vez.
* **Batch Fetching (Carregamento em Lote)**: O ORM ou repositório agrupa os IDs das entidades da lista e carrega os dados dos proxies de forma conjunta usando a cláusula SQL `WHERE cliente_id IN (...)`.

---

💡 Diagramas exemplos comparando o comportamento de um loop com Virtual Proxy (N+1) contra a solução usando **Eager Loading com JOIN** no Data Mapper:

### Virtual Proxy (N+1)
```mermaid
sequenceDiagram
    participant APP as Aplicação
    participant REPO as Repositório
    participant DB as Banco de Dados

    APP->>REPO: Busca uma lista de 100 clientes
    
    Note right of REPO: **Cenário 1: Virtual Proxy (Lazy Loading)**
    
    REPO->>DB: SELECT * FROM clientes LIMIT 100
    DB-->>REPO: Retorna 100 clientes
    
    loop Para cada cliente na lista
        APP->>REPO: getHistorico()
        
        Note right of REPO: O Proxy verifica se os dados estão carregados
        
        alt Dados não carregados (Primeira vez)
            REPO->>DB: SELECT * FROM historico WHERE cliente_id = X
            DB-->>REPO: Retorna histórico do cliente X
            
            Note right of REPO: O Proxy armazena os dados localmente
            Note right of REPO: O Proxy retorna os dados para a aplicação
        else Dados já carregados (Subsequentes)
            Note right of REPO: O Proxy retorna os dados em memória (sem DB)
        end
    end
    
    Note right of REPO: **Resultado:** 1 consulta inicial + 100 consultas dos proxies = 101 consultas ao banco


```
### Solução: Eager Loading com JOIN
```mermaid
sequenceDiagram
    participant APP as Aplicação
    participant REPO as Repositório
    participant DB as Banco de Dados

    APP->>REPO: Busca uma lista de 100 clientes com histórico
    
    Note right of REPO: **Cenário 2: Eager Loading com JOIN**
    
    REPO->>DB: SELECT * FROM clientes c LEFT JOIN historico h ON c.id = h.cliente_id WHERE c.id IN (1, 2, ..., 100)
    DB-->>REPO: Retorna todos os dados em uma única consulta
    
    Note right of REPO: O Repositório processa os resultados e cria as entidades
    Note right of REPO: Cada entidade já tem seus relacionamentos carregados

    loop Para cada cliente na lista
        APP->>REPO: getHistorico()
        Note right of REPO: Retorna os dados já carregados em memória
    end

    Note right of REPO: **Resultado:** 1 consulta única ao banco de dados

```