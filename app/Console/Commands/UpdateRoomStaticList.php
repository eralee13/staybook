<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateRoomStaticList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tm-room-static-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(RoomStaticListController::class);

        $request = new Request(); // Создаём пустой запрос
        $controller->fetchRoomsTypes($request); // Передаём в метод
        $this->info('Список типов номеров обновлён.');
    }
}
