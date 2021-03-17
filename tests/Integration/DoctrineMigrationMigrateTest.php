<?php
declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;

final class DoctrineMigrationMigrateTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     *
     * @param array<string, mixed> $bundleConfiguration
     * @param string $connectionName
     * @param array<int, mixed> $expectedQueryCalls
     */
    public function itShouldLock(array $bundleConfiguration, string $connectionName, array $expectedQueryCalls)
    {
        $application = new Application(
            (new Kernel($bundleConfiguration))
        );
        $application->setAutoExit(false);

        $applicationTester = new ApplicationTester($application);

        $applicationTester->run(
            [
                'command' => 'doctrine:database:create',
                '--connection' => $connectionName,
                '--if-not-exists' => true
            ]
        );

        $actualExitCode = $applicationTester->run(
            [
                'command' => 'doctrine:migrations:migrate',
                '--conn' => $connectionName,
                '--no-interaction' => true
            ]
        );

        $actualQueryCalls = $application->getKernel()
            ->getContainer()
            ->get('doctrine.dbal.' . $connectionName . '_connection')
            ->executedQueryCalls;

        foreach ($expectedQueryCalls as $expectedQueryCall) {
            self::assertTrue(
                in_array($expectedQueryCall, $actualQueryCalls),
                'Query call "' . json_encode($expectedQueryCall) . '" not found.'
            );
        }

        self::assertSame(0, $actualExitCode);
    }

    /**
     * Returns the test data.
     *
     * @return array<int, mixed>
     */
    public function dataProvider(): array
    {
        return [
            [
                [],
                'mysql',
                [
                    ['SELECT GET_LOCK(?, -1)', ['migrate__mysql']],
                    ['SELECT RELEASE_LOCK(?)', ['migrate__mysql']]
                ]
            ],
            [
                [],
                'pgsql',
                [
                    ['SELECT pg_advisory_lock(hashtext(?))', ['migrate__pgsql']],
                    ['SELECT pg_advisory_unlock(hashtext(?))', ['migrate__pgsql']]
                ]
            ],
            [
                ['lock_name_prefix' => 'custom__'],
                'mysql',
                [
                    ['SELECT GET_LOCK(?, -1)', ['custom__mysql']],
                    ['SELECT RELEASE_LOCK(?)', ['custom__mysql']]
                ]
            ]
        ];
    }
}
