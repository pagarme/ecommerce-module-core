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

    /**
     *
     * @AfterStep
     * @param     $event
     */
    protected function afterStepFailureScreenshot($event)
    {
        $e = $event->getTestResult()->getCallResult()->getException();
        if ($e) {
            if (!file_exists($this->screenshotDir)) {
                mkdir($this->screenshotDir);
            }
            $filename = tempnam($this->screenshotDir, "failure_screenshoot_");
            unlink($filename);
            $filename .= ".png";
            $this->screenshot($filename);
            echo "saved failure screenshot to '$filename'";
        }
    }

    /**
     *
     * @BeforeFeature
     */
    protected static function beforeFeature($event)
    {
        self::$featureHash = null;
        $requestTime = $_SERVER['REQUEST_TIME'];
        $featureTitle = $event->getFeature()->getTitle();
        $hash = hash('sha512', $featureTitle . $requestTime);
        self::$featureHash = substr($hash, 0, 16);
    }


    /**
     *
     * @BeforeScenario
     */
    protected function beforeScenario($event)
    {
        if ($event->getScenario()->hasTag('smartStep')) {
            /*throw new PendingException(
                'This is a partial @smartStep Scenario and should not be isolatedly executed.'
            );*/
        }

        $this->scenarioTokens = null;
        try {
            //trying to save examples to use in @smartStep
            $this->scenarioTokens =
                $event->getScenario()->getTokens();
        }catch(Throwable $e) {
        }
    }

}
