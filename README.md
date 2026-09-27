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
```markdown
> 📚 **Navegação do Guia de Padrões de Persistência**:
> [Visão Geral (Persistence)](Persistence.md) | [Data Mapper](Data-Mapper.md) | [Identity Map](Identity-Map.md) | [Unit of Work](Unit-of-Work.md) | [Virtual Proxy](Virtual-Proxy.md)
```