<?php

namespace App\Integrations\Contracts;

use Carbon\Carbon;

interface ChannelAdapterInterface
{
    /**
     * Поиск отелей по критериям.
     */
    public function searchHotels(array $criteria = []): array;

    /**
     * Получить один отель по внешнему ID.
     */
    public function getHotel(string $externalId): ?array;

    /**
     * Доступность по датам.
     */
    public function getAvailability(string $externalId, Carbon $from, Carbon $to): array;

    /**
     * Тарифы / цены.
     */
    public function getRates(string $externalId, Carbon $from, Carbon $to): array;

    /**
     * Импорт/получение бронирований за период.
     */
    public function pullReservations(Carbon $from, Carbon $to): array;
}