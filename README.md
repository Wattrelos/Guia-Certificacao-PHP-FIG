# 🚀 Trilha para a Certificação PHP-FIG

> Este repositório contém o caminho completo de estudos para a certificação oficial PHP-FIG. Siga os passos abaixo para se preparar:


# 📄 Documentação Atualizada: Padrões de Persistência

> Os cinco documentos do módulo de **Padrões de Persistência (Persistence Patterns)** foram completamente reorganizados, corrigidos e enriquecidos com profundidade técnica, diagramas visuais e alinhamento com exames de certificação e mercado:

---

### 📂 Sumário das Melhorias Realizadas

#### 1. [Persistence.md](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Persistence.md) (Hub Central do Ecossistema)
* **Introdução Arquitetural:** Fundamentação teórica baseada em Martin Fowler (*PoEAA - Patterns of Enterprise Application Architecture*).
* **Tabela Comparativa:** *Active Record (Eloquent / Laravel)* vs *Data Mapper + Unit of Work (Doctrine / Symfony)*.
* **Diagrama Mermaid Refinado:** Visualização dos nós de domínio, infraestrutura, cache L1, transação e banco de dados físico.
* **Fluxos Passo a Passo:** Detalhamento do **Fluxo de Leitura (Query)** e **Fluxo de Escrita (Flush)**.
* **Matriz de Responsabilidades:** Resumo prático comparando o papel de cada padrão e o impacto caso não seja utilizado.
* **Simulado com Gabarito:** Inclusão de 3 questões comentadas no estilo de exames de certificação (Zend Certified PHP Engineer e arquitetura de software).

---

#### 2. [Data-Mapper.md](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Data-Mapper.md)
* **Ampliação Estrutural:** O documento passou de 18 linhas para um guia completo e aprofundado com mais de 300 linhas.
* **Conceito de POPO (*Plain Old PHP Object*):** Demonstração de como entidades puras de domínio não herdam de nenhuma classe de banco e ignoram SQL e PDO.
* **O Problema da Impedância Objeto-Relacional:** Explicação do desacoplamento entre tabelas relacionais e grafos orientados a objetos.
* **Código PHP 8.2+ Completo:**
  * Entidade de domínio pura `Cliente` com validações, `DateTimeImmutable` e `Enum`.
  * Contrato `ClienteMapperInterface` para inversão de dependência.
  * Implementação concreta `ClienteMapper` com comandos preparados via PDO (conversão bidirecional entre colunas `snake_case` e propriedades tipadas).
* **Integração no Ecossistema:** Como coopera diretamente com o [Identity Map](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Identity-Map.md), [Unit of Work](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Unit-of-Work.md) e [Virtual Proxy](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Virtual-Proxy.md).

---

#### 3. [Identity-Map.md](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Identity-Map.md)
* **Correção de Erros de Sintaxe:** O código PHP anterior estava truncado/quebrado no início da classe; foi restaurado com tipagem estrita do PHP 8+.
* **Identidade Referencial (`$a === $b`):** Explicação da garantia de unicidade de instâncias na memória e prevenção do problema de *Lost Updates*.
* **Resolução de Loops Circulares:** Como o mapa intercepta a recursão em relacionamentos bidirecionais (`Cliente <-> Pedidos`).
* **Diagrama de Sequência Mermaid:** Fluxo detalhado de *Cache Hit* vs *Cache Miss*.
* **Identity Map vs Cache de Aplicação:** Comparativo detalhado mostrando por que o Identity Map é um **Cache de Primeiro Nível (L1)** por requisição e não concorre com o Redis/PSR-16.
* **Alerta Crítico de Produção:** Riscos de *Memory Leak* em *Workers* de fila contínuos (CLI/Daemons) e a necessidade imperativa de invocar `$identityMap->clear()` / `$em->clear()`.

---

#### 4. [Unit-of-Work.md](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Unit-of-Work.md)
* **Garantia Transacional ACID:** Como o padrão resolve o problema de persistências parciais fragmentadas usando transações PDO atômicas.
* **Ciclo de Vida das Entidades:** Diagrama de estados Mermaid cobrindo as fases `NEW`, `MANAGED`, `DIRTY`, `REMOVED` e `DETACHED`.
* **Ordem de Operações e Integridade Referencial:** Explicação de por que a ordem de persistência importa (`INSERTs` de pais antes de filhos; `DELETEs` de dependentes antes dos pais).
* **Rastreamento Ativo vs Snapshot Diffing:** Como frameworks como o Doctrine calculam o *ChangeSet* comparando o estado atual com o snapshot inicial.
* **Implementação Prática PHP 8.2+:** Classe `UnitOfWork` com `SplObjectStorage`, controle de transação, rollback automático em falhas e suporte a múltiplos mappers.
* **Pergunta Clássica de Prova:** Comparativo detalhado entre `$em->persist()` (em memória) e `$em->flush()` (I/O no banco).

---

#### 5. [Virtual-Proxy.md](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Virtual-Proxy.md)
* **Fundamentação do Lazy Loading:** Resolução do consumo excessivo de memória gerado por *Eager Loading* em relacionamentos pesados.
* **Diagrama Mermaid:** Fluxo de interceptação transparente e materialização da instância real sob demanda.
* **Implementação com Closure:** Código tipado em PHP 8.2+ herdando da classe real com inicialização única garantida.
* **Novidade do PHP 8.4 (Native Lazy Objects):** Destaque técnico sobre as novas funções nativas `ReflectionClass::newLazyGhost()` e `ReflectionClass::newLazyProxy()`, dispensando bibliotecas externas como o ProxyManager.
* **Alerta de Performance:** Detalhamento do problema da consulta N+1 (*N+1 Query Problem*) com estratégias de resolução (*Eager Loading com JOIN* e *Batch Fetching*).

---


### 🧭 Navegação Unificada
Todos os 5 arquivos agora contam com barras de navegação bidirecionais no topo e no rodapé:

> 📚 **Navegação do Guia de Padrões de Persistência**:
> [Visão Geral (Persistence](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Persistence.md)) | [Data Mapper](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Data-Mapper.md) | [Identity Map](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Identity-Map.md) | [Unit of Work](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Unit-of-Work.md) | [Virtual Proxy](/principais-certificacoes/PHP-FIG/Design-Patterns/Persistence/Virtual-Proxy.md)

Um estudante de engenharia de software **não precisa e nem deve** construir um motor de persistência completo de uma só vez. Tentar implementar de início uma infraestrutura complexa como a do Doctrine ou Hibernate introduz uma sobrecarga abstrata e computacional pesada antes mesmo de compreender a necessidade técnica de cada solução.

O ecossistema de persistência de **Martin Fowler** foi desenhado de forma modular: cada padrão resolve um gargalo arquitetural específico. O roteiro recomendado para evoluir gradualmente um CRUD básico até um motor robusto divide-se em seis etapas:

---

### 🛣️ Roteiro de Evolução Gradual

#### 1. CRUD Básico Desacoplado (PDO + Injeção de Dependência)
* **O Ponto de Partida**: O estudante cria uma classe de repositório básica operando com PDO.
* **A Melhoria**: Em vez de usar conexões globais (`Singleton` ou `new PDO()` interno), injeta-se a conexão PDO pelo construtor (**Injeção de Dependência**).
* **Ganho**: Torna o código imediatamente testável com *mocks* em memória.

#### 2. Separação de Domínio (Data Mapper + POPOs)
* **O Problema**: No modelo *Active Record*, os métodos de banco (`save()`, `delete()`) ficam dentro das regras de negócio.
* **A Melhoria**: Separar a entidade em um **POPO (Plain Old PHP Object)** puro, sem dependências de banco de dados. Criar um **Data Mapper** (ex: `ClienteMapper`) encarregado de traduzir os dados entre o banco e o objeto de domínio.
* **Ganho**: Isola a regra de negócio do banco e resolve a impedância objeto-relacional.

#### 3. Automação da Mapeamento (Hydrator)
* **O Problema**: Fazer o mapeamento manual `$row['nome']` propriedade por propriedade em cada mapper gera código repetitivo.
* **A Melhoria**: Implementar um **Hydrator** utilitário usando a API de reflexão do PHP para popular e extrair dados de DTOs e entidades de forma automatizada.
* **Ganho**: Reutilização e centralização do fluxo de conversão entre tabelas e objetos.

#### 4. Integridade em Memória (Identity Map)
* **O Problema**: Buscar o mesmo registro do banco duas vezes na mesma requisição cria duas instâncias diferentes na memória (`$a !== $b`), gerando alterações perdidas (*Lost Updates*) e loops em relacionamentos circulares.
* **A Melhoria**: Introduzir um **Identity Map** (Cache L1 em memória) no Data Mapper. Antes de rodar um `SELECT`, o mapper verifica se o objeto com aquele ID já foi instanciado na requisição.
* **Ganho**: Unicidade referencial e consistência de dados em memória.

#### 5. Carga sob Demanda (Virtual Proxy)
* **O Problema**: Carregar um objeto e todos os seus relacionamentos de forma imediata (*Eager Loading*) consome muita memória e gera consultas desnecessárias ao banco.
* **A Melhoria**: Injetar um **Virtual Proxy** (ou explorar os *Lazy Objects* nativos do PHP 8.4) para adiar a consulta ao banco até o momento em que o relacionamento for acessado.
* **Ganho**: Eficiência de I/O de rede e uso racional de memória RAM.

#### 6. Coordenação Transacional (Unit of Work)
* **O Problema**: Gravar alterações no banco a cada método invocado cria operações fragmentadas, sujeiras no banco em caso de falhas e degradação de performance por múltiplos acessos à rede.
* **A Melhoria**: Implementar o **Unit of Work** para rastrear os estados dos objetos (*New, Dirty, Removed*) e descarregá-los de uma só vez via `$uow->commit()` em uma **transação atômica (ACID)**.
* **Ganho**: Garantia de atomicidade transacional e gravação otimizada em lote.

---

### 🧩 A Etapa Final: A Fachada (`EntityManager`)
Após dominar a implementação individual dessas peças, o estudante pode agrupá-las em uma classe fachada (como o `EntityManager` do Doctrine). 

Entender esse passo a passo é valioso porque, em microsserviços de altíssimo desempenho ou arquiteturas serverless, um **Data Mapper + Hydrator sob medida** geralmente traz muito mais performance e controle do que subir um ORM pesado e automatizado.

---

💡 Quer que eu elabore um roteiro de exercícios práticos em código PHP para você implementar o **Fase 2 (Data Mapper + POPO)** a partir de um CRUD simples?