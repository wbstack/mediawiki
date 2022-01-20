<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use WBStack\Internal\ApiWbStackElasticSearchInit;
final class ApiWbStackElasticSearchInitTest extends TestCase
{
    public function testCanBeCreatedFromValidEmailAddress(): void
    {

        $mockMain = $this->createMock(ApiMain:class);

        $this->assertInstanceOf(
            ApiWbStackElasticSearchInit::class,
            new ApiWbStackElasticSearchInit(
                $mockMain,
                'cool module',
                'derp'
            )
        );
    }
}

