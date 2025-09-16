<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Tourmind\RoomStaticList;

class UpdateRoomStaticList extends Command
{
    /**
     * Имя и сигнатура команды.
     *
     * artisan tm:room-static --page=1 --size=100
     */
    protected $signature = 'tm:room-static
                            {--page=1 : PageIndex (начиная с 1)}
                            {--size=100 : PageSize (1..500)}';


    /**
     * Описание команды.
     */
    protected $description = 'Импорт статического списка номеров из Tourmind API';

    protected RoomStaticList $roomStaticList;

    public function __construct(RoomStaticList $roomStaticList)
    {
        parent::__construct();
        $this->roomStaticList = $roomStaticList;
    }

    /**
     * Выполнение команды.
     */
    public function handle(): int
    {
        $page = (int) $this->option('page');
        $size = (int) $this->option('size');

        if ($size < 1 || $size > 500) {
            $this->error('❌ Размер страницы (--size) должен быть в диапазоне 1–500');
            return Command::FAILURE;
        }

        $this->info("▶ Импорт статического списка номеров: page={$page}, size={$size}");

        $result = $this->roomStaticList->fetchRoomStaticList($page, $size);

        if (($result['ok'] ?? false) === false) {
            $this->error("Ошибка: {$result['error']}");
            return Command::FAILURE;
        }

        $count = count($result['data']['RoomStaticListResult']['Rooms'] ?? []);
        $this->info("✅ Успешно импортировано номеров: {$count}");

        return Command::SUCCESS;
    }
}