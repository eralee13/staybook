<?php

namespace App\Providers;

use App\Integrations\Contracts\ChannelAdapterInterface;
use App\Integrations\Exely\ExelyAdapter;
use Illuminate\Support\ServiceProvider;

class IntegrationServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Если для Exely отдельный контракт:
        $this->app->bind(ExelyAdapter::class, function () {
            return new ExelyAdapter();
        });

        // Если хочешь поднимать по умолчанию Exely как "канальный" адаптер:
        // $this->app->bind(ChannelAdapterInterface::class, ExelyAdapter::class);
    }
}