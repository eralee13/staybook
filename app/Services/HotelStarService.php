<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class HotelStarService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.hotelstar.url');
        $this->token = config('services.hotelstar.token');
    }

    protected function request(string $method, string $endpoint, array $payload = [])
    {
        $response = Http::withHeaders([
            'X-HS-Token' => $this->token,
            'Accept' => 'application/json',
        ])->{$method}("{$this->baseUrl}/{$endpoint}", $payload);

        if ($response->failed()) {
            throw new \Exception("HotelStar error: " . $response->body());
        }

        return $response->json();
    }

    public function search(array $data): array
    {
        return $this->request('post', 'search', $data);
    }

    public function actualize(array $searchData, array $searchItem): array
    {
        return $this->request('post', 'actualize', [
            'search_data' => $searchData,
            'search_item' => [
                'hash' => $searchItem['hash'],
                'provider_id' => $searchItem['provider_id'],
            ],
        ]);
    }

    public function book(array $data): array
    {
        return $this->request('post', 'book', $data);
    }

    public function cancel(array $data): array
    {
        return $this->request('post', 'cancel', $data);
    }

    public function info(array $data): array
    {
        return $this->request('post', 'info', $data);
    }

    public function sendMessage(array $data): array
    {
        return $this->request('post', '../messages', $data);
    }

    public function messageList(array $data): array
    {
        return $this->request('post', '../messages/list', $data);
    }
}