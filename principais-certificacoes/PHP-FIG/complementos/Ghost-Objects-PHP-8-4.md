# 👻 Ghost Objects no PHP 8.4

> No **PHP 8.4**, um **Ghost Object** (objeto fantasma) é uma estratégia nativa de *Lazy Loading* fornecida diretamente pela API de reflexão do PHP (`ReflectionClass::newLazyGhost`). Ele permite instanciar uma classe em um estado "não inicializado", adiando a busca de dados até o momento em que alguma propriedade for efetivamente acessada.

---

### 1. Como Funciona o Ghost Object?

* **Instância Direta do Domínio**: Ao contrário de abordagens antigas que criavam subclasses ou objetos *wrapper*, o Ghost Object é **uma instância direta e pura** da classe de domínio (como `HistoricoCompras` ou `Cliente`).
* **Interceptação Transparente**: A instância começa "vazia". No primeiro acesso a qualquer uma de suas propriedades, o motor interno do PHP intercepta a chamada e dispara automaticamente uma *closure* de inicialização (*initializer callback*).
* **Auto-Hidratação**: A *closure* recebe a própria referência do objeto fantasma (`$ghost`), busca os dados na fonte de persistência (como o banco de dados via SQL) e hidrata o estado interno da própria instância. Chamadas subsequentes passam a ler os dados em memória sem qualquer interceptação adicional.

---

### 2. Exemplo Conceitual (PHP 8.4)

Sem precisar de bibliotecas externas ou classes herdadas, o motor do PHP gerencia o ciclo de vida do objeto fantasma:

```php
// Reflexão sobre a classe de domínio pura
$reflection = new ReflectionClass(HistoricoCompras::class);

// Cria o Ghost Object nativo
$lazyHistorico = $reflection->newLazyGhost(function (HistoricoCompras $ghost) use ($pdo, $clienteId) {
    // 1. Busca os dados no banco apenas quando alguma propriedade for lida
    $compras = $pdo->query("SELECT * FROM compras WHERE cliente_id = {$clienteId}")->fetchAll();
    
    // 2. Hidrata o próprio objeto "fantasma"
    $ghost->__construct($compras);
});
```

---

### 3. Ghost Object (`newLazyGhost`) vs. Virtual Proxy (`newLazyProxy`)

A API de reflexão do PHP 8.4 oferece duas variantes nativas para carregamento tardio:

| Critério | 👻 Ghost Object (`newLazyGhost`) | 🛡️ Virtual Proxy (`newLazyProxy`) |
| :--- | :--- | :--- |
| **Estrutura em Memória** | **Instância Única**: A própria entidade é criada vazia e se hidrata. | **Dois Objetos**: Um objeto *wrapper* atua como invólucro e delega chamadas para um objeto real interno. |
| **Identidade de Objeto** | Mantém uma referência de memória mais simples e direta. | O ponteiro do invólucro é diferente da instância interna encapsulada. |
| **Caso de Uso Principal** | Ideal para quando a classe de domínio é conhecida e pode ser hidratada em si mesma. | Ideal para quando a instância de destino precisa ser criada por uma fábrica externa complexa. |

---

### 4. Impacto na Arquitetura de Persistência

Historicamente, ORMs no PHP (como o **Doctrine**) precisavam compilar e salvar fisicamente em disco dezenas de arquivos Proxy (em pastas como `var/cache/proxies/`) usando geradores de código pesados. Com os Ghost Objects nativos do PHP 8.4, o *Lazy Loading* passa a ser gerenciado de forma nativa e em memória pelo próprio motor da linguagem, reduzindo drasticamente o consumo de CPU e dispensando ferramentas externas de geração de código.

---

💡 Quer analisar como o **Data Mapper** e o **Identity Map** usam esses Ghost Objects nativos para evitar o carregamento excessivo de dados em memória?