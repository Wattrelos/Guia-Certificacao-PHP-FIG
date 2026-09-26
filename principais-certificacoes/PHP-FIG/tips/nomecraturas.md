# Nomes de arquivos

> No PHP, a boa prática amplamente aceita é utilizar o nome completo e descritivo, evitando abreviações confusas. O exemplo DatabaseConnectionInterface.php segue perfeitamente os padrões modernos da comunidade, como as recomendações da PSR (PHP Standard Recommendations), especificamente a PSR-1 e a PSR-12.

## Por que escolher nomes completos?

* Clareza e Legibilidade: O código é lido muito mais vezes do que é escrito. Nomes claros como DatabaseConnectionInterface deixam evidente o propósito do arquivo sem que o desenvolvedor precise adivinhar o significado de uma abreviação (como DbConnIntf).
* Autocompletação Moderna: Hoje, os editores e IDEs (como VS Code e PHPStorm) possuem recursos avançados de autocompletação. Escrever nomes longos não atrasa o desenvolvimento.
* Manutenibilidade: Facilita a integração de novos membros na equipe, pois o código se torna autoexplicativo.

## Quando abreviações são aceitáveis?
As abreviações só devem ser usadas se forem padrões universais da indústria que todo desenvolvedor conhece instantaneamente, tais como:

* ID (Identifier)
* URL (Uniform Resource Locator)
* API (Application Programming Interface)
* JSON (JavaScript Object Notation)

Fora esses acrônimos amplamente consolidados, prefira sempre a abordagem descritiva e completa.
Se quiser, posso te mostrar:

* Como aplicar as regras de CamelCase e PascalCase em classes e métodos.
* Como organizar os arquivos seguindo o padrão de carregamento automático da PSR-4.



