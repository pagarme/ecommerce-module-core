<?php

namespace Mundipagg\Core\Test\Functional\Features\Bootstrap;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Exception\ResponseTextException;
use Behat\MinkExtension\Context\MinkContext;


/**
 * Features context.
 */
class CoreFeature extends MinkContext
{
    /**
     *
     * @var Behat\Gherkin\Node\StepNode
     */
    protected $currentStep = null;
    protected $scenarioTokens = null;
    protected static $featureHash = null;
    protected $screenshotDir = DIRECTORY_SEPARATOR . 'tmp';
}
