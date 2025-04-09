<?php
namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;

class ListingController extends Controller
{

    public function store(Request $request)
    {
        // Логируем токен для отладки
        $token = $request->bearerToken();
        \Log::info('Полученный токен: ' . $token);

        // Проверяем авторизацию
        if (!auth()->check()) {
            \Log::warning('Пользователь не авторизован', [
                'token' => $token,
                'user' => auth()->user(),
            ]);
            return response()->json([
                'message' => 'Необходимо авторизоваться для создания объявления',
                'token_received' => $token,
            ], 401);
        }

        $validated = $request->validate([
            'deal_type' => 'required|in:buy,sell,rent',
            'rent_type' => 'nullable|in:long_term,short_term|required_if:deal_type,rent',
            'property_type' => 'required|in:apartment,house',
            'address' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'phone' => 'required|string|max:20',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'floor' => 'nullable|integer|required_if:property_type,apartment',
            'total_floors' => 'nullable|integer|required_if:property_type,apartment',
            'rooms' => 'nullable|string|required_if:property_type,apartment',
            'total_area' => 'nullable|numeric|required_if:property_type,apartment',
            'living_area' => 'nullable|numeric',
            'floors_in_apartment' => 'nullable|integer',
            'guests' => 'nullable|integer',
            'land_area' => 'nullable|numeric|required_if:property_type,house',
            'house_area' => 'nullable|numeric|required_if:property_type,house',
            'bedrooms' => 'nullable|integer|required_if:property_type,house',
            'bathrooms' => 'nullable|integer|required_if:property_type,house',
            'house_floors' => 'nullable|integer|required_if:property_type,house',
            'build_year' => 'nullable|integer|required_if:property_type,house',
            'apartment_number' => 'nullable|string|max:10',
            'balconies' => 'nullable|integer|min:0',
            'loggias' => 'nullable|integer|min:0',
            'view' => 'nullable|string|max:255',
            'bathrooms_combined' => 'nullable|integer|min:0',
            'bathrooms_separate' => 'nullable|integer|min:0',
            'repair' => 'nullable|string|max:255',
            'elevators_cargo' => 'nullable|integer|min:0',
            'elevators_passenger' => 'nullable|integer|min:0',
            'entrance' => 'nullable|array',
            'parking' => 'nullable|string|max:255',
            'furniture' => 'nullable|string|max:255',
            'bathroom_type' => 'nullable|string|max:255',
            'appliances' => 'nullable|array',
            'communication' => 'nullable|array',
            'photos' => 'nullable|array',
            'videos' => 'nullable|array',
            'utilities_payer' => 'nullable|string|max:255',
            'prepayment' => 'nullable|string|max:255',
            'deposit' => 'nullable|numeric|min:0',
            'rent_term' => 'nullable|string|max:255',
            'living_conditions' => 'nullable|array',
            'cadastral_land' => 'nullable|string|max:255',
            'cadastral_house' => 'nullable|string|max:255',
            'land_category' => 'nullable|string|max:255',
            'land_status' => 'nullable|string|max:255',
            'house_type' => 'nullable|string|max:255',
            'bathroom_location' => 'nullable|string|max:255',
            'sewerage' => 'nullable|string|max:255',
            'water_supply' => 'nullable|string|max:255',
            'gas' => 'nullable|string|max:255',
            'heating' => 'nullable|string|max:255',
            'electricity' => 'nullable|string|max:255',
            'extras' => 'nullable|array',
            'mortgage' => 'nullable|string|max:255',
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('public/photos');
                $photos[] = Storage::url($path);
            }
        }

        $videos = [];
        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $video) {
                $path = $video->store('public/videos');
                $videos[] = Storage::url($path);
            }
        }

        $listing = new Listing();
        $listing->user_id = auth()->id();
        $listing->deal_type = $validated['deal_type'];
        $listing->rent_type = $validated['rent_type'] ?? null;
        $listing->property_type = $validated['property_type'];
        $listing->address = $validated['address'];
        $listing->title = $validated['title'];
        $listing->description = $validated['description'];
        $listing->price = $validated['price'];
        $listing->phone = $validated['phone'];
        $listing->latitude = $validated['latitude'];
        $listing->longitude = $validated['longitude'];

        if ($validated['property_type'] === 'apartment') {
            $listing->floor = $validated['floor'];
            $listing->total_floors = $validated['total_floors'];
            $listing->rooms = $validated['rooms'];
            $listing->total_area = $validated['total_area'];
            $listing->living_area = $validated['living_area'] ?? null;
            $listing->floors_in_apartment = $validated['floors_in_apartment'] ?? null;
            $listing->guests = $validated['guests'] ?? null;
            $listing->apartment_number = $validated['apartment_number'] ?? null;
            $listing->balconies = $validated['balconies'] ?? 0;
            $listing->loggias = $validated['loggias'] ?? 0;
            $listing->view = $validated['view'] ?? null;
            $listing->bathrooms_combined = $validated['bathrooms_combined'] ?? 0;
            $listing->bathrooms_separate = $validated['bathrooms_separate'] ?? 0;
            $listing->repair = $validated['repair'] ?? null;
            $listing->elevators_cargo = $validated['elevators_cargo'] ?? 0;
            $listing->elevators_passenger = $validated['elevators_passenger'] ?? 0;
        } elseif ($validated['property_type'] === 'house') {
            $listing->land_area = $validated['land_area'];
            $listing->house_area = $validated['house_area'];
            $listing->bedrooms = $validated['bedrooms'];
            $listing->bathrooms = $validated['bathrooms'];
            $listing->house_floors = $validated['house_floors'];
            $listing->build_year = $validated['build_year'];
            $listing->cadastral_land = $validated['cadastral_land'] ?? null;
            $listing->cadastral_house = $validated['cadastral_house'] ?? null;
            $listing->land_category = $validated['land_category'] ?? null;
            $listing->land_status = $validated['land_status'] ?? null;
            $listing->house_type = $validated['house_type'] ?? null;
            $listing->bathroom_location = $validated['bathroom_location'] ?? null;
            $listing->sewerage = $validated['sewerage'] ?? null;
            $listing->water_supply = $validated['water_supply'] ?? null;
            $listing->gas = $validated['gas'] ?? null;
            $listing->heating = $validated['heating'] ?? null;
            $listing->electricity = $validated['electricity'] ?? null;
        }

        $listing->entrance = json_encode($validated['entrance'] ?? []);
        $listing->appliances = json_encode($validated['appliances'] ?? []);
        $listing->communication = json_encode($validated['communication'] ?? []);
        $listing->photos = json_encode($photos);
        $listing->videos = json_encode($videos);
        $listing->living_conditions = json_encode($validated['living_conditions'] ?? []);
        $listing->extras = json_encode($validated['extras'] ?? []);

        $listing->parking = $validated['parking'] ?? null;
        $listing->furniture = $validated['furniture'] ?? null;
        $listing->bathroom_type = $validated['bathroom_type'] ?? null;
        $listing->utilities_payer = $validated['utilities_payer'] ?? null;
        $listing->prepayment = $validated['prepayment'] ?? null;
        $listing->deposit = $validated['deposit'] ?? null;
        $listing->rent_term = $validated['rent_term'] ?? null;
        $listing->mortgage = $validated['mortgage'] ?? null;

        $listing->save();

        return response()->json([
            'message' => 'Объявление успешно создано',
            'listing' => $listing,
        ], 201);
    }
    public function addressSuggestions(Request $request)
    {
        $query = $request->input('query');
        $types = $request->input('types', 'geo'); // Добавляем поддержку типов
        $geosuggestApiKey = env('YANDEX_GEOSUGGEST_API_KEY', 'b89db728-a057-4008-bcdb-7053678c90f9');

        $url = "https://suggest-maps.yandex.ru/v1/suggest?apikey={$geosuggestApiKey}&text=" . urlencode($query) . "&lang=ru_RU&types={$types}";
        try {
            $response = Http::get($url);
            $data = $response->json();

            if ($response->status() !== 200 || !$data || !isset($data['results'])) {
                return response()->json([]);
            }

            $suggestions = array_map(function ($item) {
                return [
                    'value' => $item['title']['text'],
                    'subtitle' => $item['subtitle']['text'] ?? '',
                ];
            }, $data['results'] ?? []);

            return response()->json($suggestions);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function index(Request $request)
    {
        $query = Listing::query();

        if ($request->has('city') && $request->city) {
            $query->where('address', 'like', '%' . $request->city . '%');
        }

        if ($request->has('address') && $request->address) {
            $query->where('address', 'like', '%' . $request->address . '%');
        }

        if ($request->has('deal_type') && $request->deal_type) {
            $query->where('deal_type', $request->deal_type);
        }

        if ($request->has('rooms') && $request->rooms) {
            if ($request->rooms == '4') {
                $query->where('rooms', '>=', 4);
            } else {
                $query->where('rooms', $request->rooms);
            }
        }

        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('latitude') && $request->has('longitude')) {
            $userLat = $request->latitude;
            $userLon = $request->longitude;

            $query->selectRaw(
                '*,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$userLat, $userLon, $userLat]
            )->orderBy('distance', 'asc');
        } else {
            $query->orderBy('address', 'asc');
        }

        $listings = $query->get();
        return response()->json(['listings' => $listings]);
    }

    public function checkAddress(Request $request)
    {
        $address = $request->input('address');
        if (!$address) {
            return response()->json(['message' => 'Адрес не указан'], 400);
        }

        // Используем ключ, который ты дал
        $geocoderApiKey = env('YANDEX_GEOCODER_API_KEY', 'b9d8623d-cb6e-40e6-b51b-8cc688198a38');
        $url = "https://geocode-maps.yandex.ru/1.x/?format=json&geocode=" . urlencode($address) . "&apikey={$geocoderApiKey}&lang=ru_RU";

        try {
            $response = Http::get($url);
            $data = $response->json();

            // Логируем для отладки
            \Log::info('Yandex Geocoder Response:', ['url' => $url, 'response' => $data]);

            if ($response->status() !== 200 || !$data || !isset($data['response']['GeoObjectCollection']['featureMember'])) {
                return response()->json([
                    'message' => 'Не удалось проверить адрес',
                    'response' => $data, // Добавляем ответ для анализа
                ], 400);
            }

            $geoObject = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'] ?? null;
            if (!$geoObject) {
                return response()->json(['message' => 'Адрес не найден'], 404);
            }


            $coordinates = $geoObject['Point']['pos'] ?? null;
            if (!$coordinates) {
                return response()->json(['message' => 'Координаты не найдены'], 400);
            }

            [$longitude, $latitude] = explode(' ', $coordinates);

            return response()->json([
                'message' => 'Адрес успешно проверен',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'full_address' => $geoObject['metaDataProperty']['GeocoderMetaData']['text'],
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка в checkAddress:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Ошибка проверки адреса: ' . $e->getMessage()], 500);
        }
    }
}
