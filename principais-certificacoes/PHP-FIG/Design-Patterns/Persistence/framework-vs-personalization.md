# Frameworks vs Personalização: O Debate Arquitetural
## Por quê personalizar o motor de persistência ao invés de usar ORMs?
> Usar um ORM pesado como o Doctrine para resolver um problema simples é exatamente como levar uma oficina mecânica móvel inteira apenas para martelar um prego na parede.
A questão do consumo de recursos (RAM e CPU) é real, mas o motivo principal pelo qual desenvolvedores optam por abordagens sob medida (customizadas/puras) envolve uma combinação de fatores arquiteturais e operacionais.
------------------------------
## Por que o Doctrine (e frameworks) consomem tanta RAM e CPU?
O Doctrine não é lento por defeito de fabricação, mas sim pelo custo de tudo o que ele faz de forma automatizada por baixo dos panos:

   1. Hydration (Hidratação) pesada: Transformar arrays puros de resultados do PDO em grafos de objetos complexos exige muita CPU e cria milhares de alocações na memória RAM.
   2. Mecanismo de Unit of Work (Unit of Work Overhead): O Doctrine precisa manter um "Instantâneo" (Snapshot) do estado original de cada entidade na memória. Quando você roda o flush(), ele faz uma varredura completa (Change Tracking) comparando propriedade por propriedade de cada objeto para descobrir o que mudou. Se você carregar 5.000 registros, essa comparação consome muita CPU.
   3. Metadados e Reflexão: Ler anotações, atributos PHP 8 ou arquivos YAML para entender o mapeamento das tabelas exige processamento pesado na inicialização da requisição.

------------------------------
## Os 3 principais motivos para criar soluções personalizadas (PHP Puro)
Embora a performance seja um pilar central, a decisão de escrever código sob medida geralmente passa por três grandes motivos:
## 1. Arquiteturas de Altíssimo Desempenho e Escala (Microserviços)
Em ambientes de microsserviços modernos, funções Lambda/Serverless ou APIs que precisam responder em poucos milissegundos, o overhead de inicialização do Doctrine inviabiliza o serviço. O PHP puro com PDO consegue processar requisições gastando uma fração minúscula de memória, aumentando drasticamente a densidade de requisições por segundo que o mesmo servidor aguenta.
## 2. Casos de Uso Específicos vs. Soluções Genéricas
Um framework é feito para resolver o problema de qualquer empresa no mundo. Ele precisa prever relacionamentos complexos, heranças de tabelas e múltiplos bancos de dados. Quando você escreve seu próprio repositório puro, você escreve o SQL exato, sob medida para o seu negócio. Você não precisa de uma camada que adivinha o que o banco de dados vai fazer.
## 3. Controle Total e Domínio do Grafo (DDD Avançado)
Muitas vezes, as restrições que os ORMs impõem sobre como as entidades devem ser desenhadas (como a exigência de IDs numéricos específicos ou métodos específicos) violam as regras puras de um domínio complexo de negócio. Criar mappers manuais em PHP puro liberta as classes de negócio de qualquer herança ou acoplamento com ferramentas de infraestrutura.
------------------------------
## Resumo da Analogia
Se você tem uma aplicação corporativa gigante com centenas de tabelas e dezenas de regras de negócio interligadas, o Doctrine é o guindaste automatizado que você precisa.
Mas se o seu objetivo é criar um microsserviço focado em ler e escrever dados de forma cirúrgica e ultra-rápida, implementar manualmente os padrões necessários (ou usar um micro-ORM simples) é a escolha certa para não pagar o preço em hardware.
Excelente jornada de estudos sobre persistência! Agora que cobrimos desde o funcionamento dos relacionamentos até a viabilidade em produção, quer fechar com o simulado de duas questões rápidas focado em fixar esses conceitos de arquitetura para a sua prova?

