<?php

namespace Obokaman\StockForecast\Infrastructure\Http\StocksStats\Cryptocompare;

use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock;
use Obokaman\StockForecast\Domain\Model\Financial\StockStats;
use Obokaman\StockForecast\Infrastructure\Http\StocksStats\Collector as CollectorContract;

class Collector implements CollectorContract
{
    private const API_URL = 'https://min-api.cryptocompare.com/data/histoday?fsym=%s&tsym=%s&limit=%d&aggregate=1';

    public function getStats(
        Currency $a_currency,
        Stock $a_stock,
        int $previous_days_to_collect
    ): array
    {
        $api_url     = sprintf(self::API_URL, $a_stock, $a_currency, $previous_days_to_collect - 1);
        $response    = $this->collectStockInformationFromRemoteApi($api_url);
        $stats_array = [];

        foreach ($response['Data'] as $stats)
        {
            $stats_array[] = new StockStats(
                $a_currency,
                $a_stock,
                (new \DateTimeImmutable())->setTimestamp($stats['time']),
                $stats['close'],
                $stats['high'],
                $stats['low'],
                $stats['open'],
                $stats['volumefrom'],
                $stats['volumeto']
            );
        }

        return $stats_array;
    }

    protected function collectStockInformationFromRemoteApi(string $api_url): array
    {
        $response = json_decode(file_get_contents($api_url), true);

        return $response;
    }
}