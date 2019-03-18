<?php

namespace Mundipagg\Core\Test\Unit\Kernel\ValueObjects;

use Mundipagg\Core\Kernel\ValueObjects\VersionInfo;
use PHPUnit\Framework\TestCase;

class VersionInfoTest extends TestCase
{
    protected $moduleVersion;
    protected $coreVersion;
    protected $platformVersion;

    public function setUp()
    {
        $this->coreVersion = 'c1.0.0';
        $this->moduleVersion = 'm1.0.0';
        $this->platformVersion = 'p1.0.0';
    }


    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\VersionInfo
     *
     * @uses \Mundipagg\Core\Kernel\Abstractions\AbstractValueObject
     *
     */
    public function aVersionInfoShouldBeComparable()
    {
        $versionInfo11 = new VersionInfo(1,2,3);
        $versionInfo12 = new VersionInfo(1,2,3);

        $versionInfo2 = new VersionInfo(1,2,1);

        $this->assertTrue($versionInfo11->equals($versionInfo12));
        $this->assertTrue($versionInfo12->equals($versionInfo11));
        $this->assertFalse($versionInfo11->equals($versionInfo2));
    }

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\VersionInfo
     */
    public function aVersionInfoShouldBeJsonSerializable()
    {
        $base = new \stdClass();
        $base->moduleVersion = 2;
        $base->coreVersion = 25;
        $base->platformVersion = 33;

        $versionInfo = new VersionInfo(
            $base->moduleVersion,
            $base->coreVersion,
            $base->platformVersion
        );

        $json = json_encode($versionInfo);
        $expected = json_encode($base);

        $this->assertEquals($expected, $json);
    }

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\VersionInfo::getCoreVersion()
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\VersionInfo
     */
    public function aVersionInfoShouldContainACoreVersion()
    {

        $expectedCoreVersion = $this->coreVersion;

        $versionInfo = new VersionInfo(
            $this->moduleVersion,
            $expectedCoreVersion,
            $this->platformVersion
        );

        $this->assertEquals($expectedCoreVersion, $versionInfo->getCoreVersion());
    }

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\VersionInfo::getModuleVersion()
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\VersionInfo
     */
    public function aVersionInfoShouldContainAModuleVersion()
    {
        $expectedModuleVersion = $this->moduleVersion;

        $versionInfo = new VersionInfo(
            $this->moduleVersion,
            $this->coreVersion,
            $this->platformVersion
        );

        $this->assertEquals($expectedModuleVersion, $versionInfo->getModuleVersion());
    }

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\VersionInfo::getPlatformVersion()
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\VersionInfo
     */
    public function aVersionInfoShouldContainAPlatformVersion()
    {
        $expectedPlatoformVersion = $this->platformVersion;

        $versionInfo = new VersionInfo(
            $this->moduleVersion,
            $this->coreVersion,
            $this->platformVersion
        );

        $this->assertEquals($expectedPlatoformVersion, $versionInfo->getPlatformVersion());
    }

}
