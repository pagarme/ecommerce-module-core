<?php

declare(strict_types=1);

namespace Pagarme\Core\Test\Kernel\Helper;

use Pagarme\Core\Kernel\Helper\StringFunctionsHelper;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Pagarme\Core\Kernel\Helper\StringFunctionsHelper
 */
class StringFunctionsHelperTest extends TestCase
{
    /** @var StringFunctionsHelper */
    private $helper;

    public function setUp(): void
    {
        $this->helper = new StringFunctionsHelper();
    }

    // =========================================================================
    // removeLineBreaksRecursive
    // =========================================================================

    public function testRemoveLineBreaksRecursiveWithSimpleStringBehavesLikeOriginalMethod(): void
    {
        $input = "Hello\nWorld\r\nFoo\tBar";

        $this->assertSame(
            StringFunctionsHelper::removeLineBreaks($input),
            $this->helper->removeLineBreaksRecursive($input)
        );
    }

    public function testRemoveLineBreaksRecursiveWithNullBehavesLikeOriginalMethod(): void
    {
        $this->assertSame(
            StringFunctionsHelper::removeLineBreaks(null),
            $this->helper->removeLineBreaksRecursive(null)
        );
    }

    public function testRemoveLineBreaksRecursiveWithFlatArrayCleansAllStringValues(): void
    {
        $input = [
            'name'    => "John\nDoe",
            'address' => "Street\r\nCity",
            'note'    => "Tab\there",
        ];

        $result = $this->helper->removeLineBreaksRecursive($input);

        $this->assertSame(['name', 'address', 'note'], array_keys($result));
        $this->assertSame(0, substr_count($result['name'],    "\n"));
        $this->assertSame(0, substr_count($result['address'], "\r\n"));
        $this->assertSame(0, substr_count($result['note'],    "\t"));
    }

    public function testRemoveLineBreaksRecursiveWithNestedArrayCleansAllLevelsRecursively(): void
    {
        $input = [
            'billing' => [
                'inner' => [
                    'deep' => "Deep\nValue\r\n",
                ],
                'sibling' => "Sibling\r",
            ],
            'top' => "Top\nLevel",
        ];

        $result = $this->helper->removeLineBreaksRecursive($input);

        $this->assertSame(0, substr_count($result['billing']['inner']['deep'], "\n"));
        $this->assertSame(0, substr_count($result['billing']['inner']['deep'], "\r\n"));
        $this->assertSame(0, substr_count($result['billing']['sibling'],       "\r"));
        $this->assertSame(0, substr_count($result['top'],                      "\n"));
    }

    public function testRemoveLineBreaksRecursivePreservesOriginalArrayKeys(): void
    {
        $input = [
            0       => "zero\n",
            'alpha' => "alpha\n",
            5       => "five\n",
        ];

        $result = $this->helper->removeLineBreaksRecursive($input);

        $this->assertSame([0, 'alpha', 5], array_keys($result));
    }

    public function testRemoveLineBreaksRecursiveWithStdClassReturnsSameClassType(): void
    {
        $nested       = new stdClass();
        $nested->city = "Sao\r\nPaulo";

        $input          = new stdClass();
        $input->name    = "John\nDoe";
        $input->address = $nested;

        $result = $this->helper->removeLineBreaksRecursive($input);

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertInstanceOf(stdClass::class, $result->address);
        $this->assertSame(0, substr_count($result->name,         "\n"));
        $this->assertSame(0, substr_count($result->address->city, "\r\n"));
    }

    public function testRemoveLineBreaksRecursiveDoesNotMutateOriginalObject(): void
    {
        $input       = new stdClass();
        $input->text = "Hello\nWorld";

        $result = $this->helper->removeLineBreaksRecursive($input);

        $this->assertSame("Hello\nWorld", $input->text);
        $this->assertNotSame($input, $result);
    }

    /**
     * @dataProvider nonStringScalarProvider
     * @param mixed $scalar
     */
    public function testRemoveLineBreaksRecursivePreservesNonStringScalarsWithoutAlteringTheirType($scalar): void
    {
        $input = [
            'scalar' => $scalar,
            'str'    => "clean\nme",
        ];

        $result = $this->helper->removeLineBreaksRecursive($input);

        $this->assertSame($scalar, $result['scalar']);
        $this->assertSame(0, substr_count($result['str'], "\n"));
    }

    // =========================================================================
    // cleanRecursive
    // =========================================================================

    public function testCleanRecursiveWithSimpleStringBehavesLikeOriginalMethod(): void
    {
        $input = "It's a <b>test</b> with special chars & more";

        $this->assertSame(
            $this->helper->cleanStrToDb($input),
            $this->helper->cleanRecursive($input)
        );
    }

    public function testCleanRecursiveWithFlatArrayCleansAllStringValues(): void
    {
        $input = [
            'html'   => '<script>alert("xss")</script>',
            'quote'  => "O'Brian",
            'symbol' => 'Price $100 & more!',
        ];

        $result = $this->helper->cleanRecursive($input);

        $this->assertSame(['html', 'quote', 'symbol'], array_keys($result));
        $this->assertSame(0, substr_count($result['html'],   '<script>'));
        $this->assertSame(0, substr_count($result['quote'],  "'"));
        $this->assertSame(0, substr_count($result['symbol'], '$'));
    }

    public function testCleanRecursiveWithNestedArrayCleansAllLevelsRecursively(): void
    {
        $input = [
            'billing' => [
                'name'    => "Alice O'Malley",
                'address' => [
                    'street' => "<b>123 Main St</b>",
                ],
            ],
        ];

        $result = $this->helper->cleanRecursive($input);

        $this->assertSame(0, substr_count($result['billing']['name'],              "'"));
        $this->assertSame(0, substr_count($result['billing']['address']['street'], '<b>'));
    }

    public function testCleanRecursivePreservesOriginalArrayKeys(): void
    {
        $input = [
            0        => "zero <b>tag</b>",
            'nested' => ["inner <b>key</b>"],
        ];

        $result = $this->helper->cleanRecursive($input);

        $this->assertSame([0, 'nested'], array_keys($result));
        $this->assertSame([0], array_keys($result['nested']));
    }

    public function testCleanRecursiveWithStdClassReturnsSameClassType(): void
    {
        $child       = new stdClass();
        $child->html = '<p>paragraph</p>';

        $input       = new stdClass();
        $input->name = "Bob O'Brien";
        $input->body = $child;

        $result = $this->helper->cleanRecursive($input);

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertInstanceOf(stdClass::class, $result->body);
        $this->assertSame(0, substr_count($result->name,       "'"));
        $this->assertSame(0, substr_count($result->body->html, '<p>'));
    }

    public function testCleanRecursiveDoesNotMutateOriginalObject(): void
    {
        $input       = new stdClass();
        $input->name = "Bob O'Brien";

        $result = $this->helper->cleanRecursive($input);

        $this->assertSame("Bob O'Brien", $input->name);
        $this->assertNotSame($input, $result);
    }

    /**
     * @dataProvider nonStringScalarProvider
     * @param mixed $scalar
     */
    public function testCleanRecursivePreservesNonStringScalarsWithoutAlteringTheirType($scalar): void
    {
        $input = [
            'scalar' => $scalar,
            'str'    => "clean <b>this</b>",
        ];

        $result = $this->helper->cleanRecursive($input);

        $this->assertSame($scalar, $result['scalar']);
        $this->assertSame(0, substr_count($result['str'], '<b>'));
    }

    // =========================================================================
    // cleanUrlToDb
    // =========================================================================

    public function testCleanUrlToDbPreservesQueryParamsAndNeutralizesQuote(): void
    {
        $url = "https://boleto-payments.stone.com.br/boleto?fmt=html&id=6a6bb12d59f677cb3028a834&pk=b3bd83cbb7aac0357e66ebafe2efa64f3ff268beb315a65e52e38c5072b9cfda";

        $this->assertSame($url, $this->helper->cleanUrlToDb($url));
        $this->assertSame(0, substr_count($this->helper->cleanUrlToDb("a'b"), "'"));
        $this->assertSame('', $this->helper->cleanUrlToDb(null));
    }

    // =========================================================================
    // removeSpecialCharactersRecursive
    // =========================================================================

    public function testRemoveSpecialCharactersRecursiveWithSimpleStringBehavesLikeOriginalMethod(): void
    {
        $input = 'Olá Mundo! Ação & Reação';

        $this->assertSame(
            $this->helper->removeSpecialCharacters($input),
            $this->helper->removeSpecialCharactersRecursive($input)
        );
    }

    public function testRemoveSpecialCharactersRecursiveWithFlatArrayProducesOnlyAsciiAlphaAndSpaces(): void
    {
        $input = [
            'greeting' => 'Olá!',
            'action'   => 'Ação & Reação',
            'name'     => 'José',
        ];

        $result = $this->helper->removeSpecialCharactersRecursive($input);

        $this->assertSame(['greeting', 'action', 'name'], array_keys($result));

        foreach ($result as $key => $value) {
            $this->assertSame(
                1,
                preg_match('/^[a-zA-Z ]*$/', $value),
                "Field '{$key}' still contains non-ASCII or special characters: '{$value}'"
            );
        }
    }

    public function testRemoveSpecialCharactersRecursiveWithNestedArrayCleansAllLevelsRecursively(): void
    {
        $input = [
            'user' => [
                'name'    => 'Ângela',
                'hobbies' => ['Leitura!', 'Música & Arte'],
            ],
        ];

        $result = $this->helper->removeSpecialCharactersRecursive($input);

        $this->assertSame(
            1,
            preg_match('/^[a-zA-Z ]*$/', $result['user']['name'])
        );

        foreach ($result['user']['hobbies'] as $hobby) {
            $this->assertSame(1, preg_match('/^[a-zA-Z ]*$/', $hobby));
        }
    }

    public function testRemoveSpecialCharactersRecursivePreservesOriginalArrayKeys(): void
    {
        $input = [
            'first' => 'Ângela!',
            'last'  => 'Müller@',
        ];

        $result = $this->helper->removeSpecialCharactersRecursive($input);

        $this->assertSame(['first', 'last'], array_keys($result));
    }

    public function testRemoveSpecialCharactersRecursiveWithStdClassReturnsSameClassType(): void
    {
        $nested       = new stdClass();
        $nested->tag  = '!Héroe!';

        $input        = new stdClass();
        $input->name  = 'Ângela Ação';
        $input->extra = $nested;

        $result = $this->helper->removeSpecialCharactersRecursive($input);

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertInstanceOf(stdClass::class, $result->extra);
        $this->assertSame(1, preg_match('/^[a-zA-Z ]*$/', $result->name));
        $this->assertSame(1, preg_match('/^[a-zA-Z ]*$/', $result->extra->tag));
    }

    public function testRemoveSpecialCharactersRecursiveDoesNotMutateOriginalObject(): void
    {
        $input       = new stdClass();
        $input->name = 'Ângela!';

        $result = $this->helper->removeSpecialCharactersRecursive($input);

        $this->assertSame('Ângela!', $input->name);
        $this->assertNotSame($input, $result);
    }

    /**
     * @dataProvider nonStringScalarProvider
     * @param mixed $scalar
     */
    public function testRemoveSpecialCharactersRecursivePreservesNonStringScalarsWithoutAlteringTheirType($scalar): void
    {
        $input = [
            'scalar' => $scalar,
            'str'    => 'Olá!',
        ];

        $result = $this->helper->removeSpecialCharactersRecursive($input);

        $this->assertSame($scalar, $result['scalar']);
        $this->assertSame(1, preg_match('/^[a-zA-Z ]*$/', $result['str']));
    }

    // =========================================================================
    // applyRecursive: non-string scalar passthrough (cross-method)
    // =========================================================================

    public function testIntegerPassesThroughAllThreeRecursiveMethods(): void
    {
        $this->assertSame(42,   $this->helper->removeLineBreaksRecursive(42));
        $this->assertSame(42,   $this->helper->cleanRecursive(42));
        $this->assertSame(42,   $this->helper->removeSpecialCharactersRecursive(42));
    }

    public function testFloatPassesThroughAllThreeRecursiveMethods(): void
    {
        $this->assertSame(3.14, $this->helper->removeLineBreaksRecursive(3.14));
        $this->assertSame(3.14, $this->helper->cleanRecursive(3.14));
        $this->assertSame(3.14, $this->helper->removeSpecialCharactersRecursive(3.14));
    }

    public function testBooleanTruePassesThroughAllThreeRecursiveMethods(): void
    {
        $this->assertSame(true, $this->helper->removeLineBreaksRecursive(true));
        $this->assertSame(true, $this->helper->cleanRecursive(true));
        $this->assertSame(true, $this->helper->removeSpecialCharactersRecursive(true));
    }

    public function testBooleanFalsePassesThroughAllThreeRecursiveMethods(): void
    {
        $this->assertSame(false, $this->helper->removeLineBreaksRecursive(false));
        $this->assertSame(false, $this->helper->cleanRecursive(false));
        $this->assertSame(false, $this->helper->removeSpecialCharactersRecursive(false));
    }

    public function testNullPassesThroughCleanRecursiveAndRemoveSpecialCharactersRecursive(): void
    {
        $this->assertSame(null, $this->helper->cleanRecursive(null));
        $this->assertSame(null, $this->helper->removeSpecialCharactersRecursive(null));
    }

    public function testMixedTypesInDeeplyNestedArrayAreHandledCorrectly(): void
    {
        $input = [
            'name'    => "John\nDoe",
            'age'     => 30,
            'score'   => 9.5,
            'active'  => true,
            'deleted' => false,
            'notes'   => null,
            'address' => [
                'street' => "Main\nSt",
                'number' => 100,
                'extra'  => null,
            ],
        ];

        $result = $this->helper->removeLineBreaksRecursive($input);

        $this->assertSame(0,     substr_count($result['name'], "\n"));
        $this->assertSame(30,    $result['age']);
        $this->assertSame(9.5,   $result['score']);
        $this->assertSame(true,  $result['active']);
        $this->assertSame(false, $result['deleted']);
        $this->assertSame(null,  $result['notes']);
        $this->assertSame(0,     substr_count($result['address']['street'], "\n"));
        $this->assertSame(100,   $result['address']['number']);
        $this->assertSame(null,  $result['address']['extra']);
    }

    // =========================================================================
    // Data providers
    // =========================================================================

    public function nonStringScalarProvider(): array
    {
        return [
            'integer zero' => [0],
            'integer'      => [42],
            'float'        => [3.14],
            'bool true'    => [true],
            'bool false'   => [false],
            'null'         => [null],
        ];
    }
}
