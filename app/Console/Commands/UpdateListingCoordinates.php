<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;

class UpdateListingCoordinates extends Command
{
    protected $signature = 'listings:update-coordinates';
    protected $description = 'Update latitude and longitude for existing listings using Yandex Geocoder API';

    public function handle()
    {
        $this->info('Starting to update coordinates for listings...');

        // Получаем все записи, у которых нет координат
        $listings = Listing::whereNull('latitude')->orWhereNull('longitude')->get();
        $apiKey = 'b9d8623d-cb6e-40e6-b51b-8cc688198a38'; // Твой API-ключ Yandex Geocoder

        if ($listings->isEmpty()) {
            $this->info('No listings need coordinate updates.');
            return;
        }

        foreach ($listings as $listing) {
            $address = $listing->address;
            if (!$address) {
                $this->warn("Listing ID {$listing->id} has no address, skipping...");
                continue;
            }

            // Формируем запрос к Yandex Geocoder API
            $url = "https://geocode-maps.yandex.ru/1.x/?format=json&geocode=" . urlencode($address) . "&apikey=" . $apiKey;

            // Выполняем запрос
            $response = @file_get_contents($url);
            if ($response === false) {
                $this->error("Failed to fetch coordinates for address: {$address}");
                continue;
            }

            $geoData = json_decode($response, true);
            $pos = $geoData['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'] ?? null;

            if ($pos) {
                list($longitude, $latitude) = explode(' ', $pos);
                $listing->latitude = $latitude;
                $listing->longitude = $longitude;
                $listing->save();
                $this->info("Updated coordinates for listing ID {$listing->id}: {$latitude}, {$longitude}");
            } else {
                $this->warn("Could not find coordinates for address: {$address}");
            }

            // Задержка, чтобы не превысить лимит запросов к API
            sleep(1);
        }

        $this->info('Finished updating coordinates.');
    }
}
