<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawMinkContext
{
    /**
     * @Given que eu estou na página inicial
     */
    public function queEuEstouNaPaginaInicial()
    {
        // Visita a URL definida no base_url do behat.yml
        $this->visitPath('/');
    }

    /**
     * @Then eu devo ver o texto :texto
     */
    public function euDevoVerOTexto($texto)
    {
        // Verifica se o texto existe na página atual
        $this->assertSession()->pageTextContains($texto);
    }
}
