<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\CommandHandler;

use App\Application\CommandHandler\WeatherCommandHandler;
use App\Application\Repository\WeatherRepositoryInterface;
use App\Application\Repository\WeatherReportCollectionRepositoryInterface;
use App\Domain\Model\WeatherReportCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class WeatherCommandHandlerTest extends MockeryTestCase
{
    /**
     * @test
     */
    public function shouldStoreWeatherData(): void
    {
        $weatherDtoCollection = new WeatherReportCollection([]);

        $weatherFetchService = Mockery::mock(WeatherRepositoryInterface::class);
        $weatherFetchService
            ->shouldReceive('fetchWeather')
            ->andReturn($weatherDtoCollection)
            ->once();

        $weatherStorageService = Mockery::mock(WeatherReportCollectionRepositoryInterface::class);
        $weatherStorageService
            ->shouldReceive('store')
            ->once();

        $commandHandler = new WeatherCommandHandler(
            $weatherFetchService,
            $weatherStorageService
        );

        $commandHandler->storeWeatherData();
    }
}
