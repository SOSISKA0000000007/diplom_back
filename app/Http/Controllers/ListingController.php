<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Listing;
use App\Models\ListingView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

class ListingController extends Controller
{
    public function store(Request $request)
    {
        $token = $request->bearerToken();
        \Log::info('Полученный токен: ' . $token);

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
        $listing->status = 'active';
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

    public function myListings(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Необходимо авторизоваться'], 401);
        }

        $query = Listing::where('user_id', auth()->id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $listings = $query->get();

        return response()->json(['listings' => $listings]);
    }

    public function archive(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Необходимо авторизоваться'], 401);
        }

        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$listing) {
            return response()->json(['message' => 'Объявление не найдено'], 404);
        }

        $listing->status = $listing->status === 'active' ? 'archived' : 'active';
        $listing->save();

        return response()->json(['message' => 'Статус объявления изменен', 'listing' => $listing]);
    }

    public function addressSuggestions(Request $request)
    {
        $query = $request->input('query');
        $types = $request->input('types', 'geo');
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
        $query = Listing::where('status', 'active'); // Фильтруем только активные объявления

        if ($request->has('city') && $request->city) {
            $query->where('address', 'like', '%' . $request->city . '%');
        }

        if ($request->has('address') && $request->address) {
            $query->where('address', 'like', '%' . $request->address . '%');
        }

        if ($request->has('deal_type') && $request->deal_type) {
            $query->where('deal_type', $request->deal_type);
        }
        if ($request->has('property_type') && $request->property_type) {
            $query->where('property_type', $request->property_type); // Фильтр по типу недвижимости
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

        $geocoderApiKey = env('YANDEX_GEOCODER_API_KEY', 'b9d8623d-cb6e-40e6-b51b-8cc688198a38');
        $url = "https://geocode-maps.yandex.ru/1.x/?format=json&geocode=" . urlencode($address) . "&apikey={$geocoderApiKey}&lang=ru_RU";

        try {
            $response = Http::get($url);
            $data = $response->json();

            \Log::info('Yandex Geocoder Response:', ['url' => $url, 'response' => $data]);

            if ($response->status() !== 200 || !$data || !isset($data['response']['GeoObjectCollection']['featureMember'])) {
                return response()->json([
                    'message' => 'Не удалось проверить адрес',
                    'response' => $data,
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

    public function show(Request $request, $id)
    {
        \Log::info('Show listing request:', ['listing_id' => $id, 'user_id' => auth()->id()]);

        try {
            $listing = Listing::where('id', $id)->where('status', 'active')->first();

            if (!$listing) {
                \Log::warning('Listing not found or not active:', ['listing_id' => $id]);
                return response()->json(['message' => 'Объявление не найдено или не активно'], 404);
            }

            // Регистрируем просмотр
            $userId = auth()->check() ? auth()->id() : null;
            $ipAddress = $request->ip();

            $existingView = ListingView::where('listing_id', $id)
                ->where(function ($query) use ($userId, $ipAddress) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('ip_address', $ipAddress);
                    }
                })
                ->where('viewed_at', '>=', now()->subHours(24))
                ->exists();

            if (!$existingView) {
                ListingView::create([
                    'listing_id' => $id,
                    'user_id' => $userId,
                    'ip_address' => $ipAddress,
                    'viewed_at' => now(),
                ]);

                $listing->increment('view_count');
            }

            \Log::info('Listing retrieved successfully:', ['listing_id' => $id]);
            return response()->json(['listing' => $listing]);
        } catch (\Exception $e) {
            \Log::error('Error in show:', ['error' => $e->getMessage(), 'listing_id' => $id]);
            return response()->json(['message' => 'Ошибка при получении объявления: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Необходимо авторизоваться'], 401);
        }

        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->first();
        if (!$listing) {
            return response()->json(['message' => 'Объявление не найдено'], 404);
        }

        $validated = $request->validate([
            'deal_type' => 'sometimes|in:buy,sell,rent',
            'rent_type' => 'nullable|in:long_term,short_term|required_if:deal_type,rent',
            'property_type' => 'sometimes|in:apartment,house',
            'address' => 'sometimes|string|max:255',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'phone' => 'sometimes|string|max:20',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
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

        // Обработка загрузки новых фотографий
        $photos = $listing->photos ? json_decode($listing->photos, true) : [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('public/photos');
                $photos[] = Storage::url($path);
            }
        }
        if ($request->has('photos') && is_array($request->photos)) {
            $photos = array_merge($photos, $request->photos);
        }

        // Обработка загрузки новых видео
        $videos = $listing->videos ? json_decode($listing->videos, true) : [];
        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $video) {
                $path = $video->store('public/videos');
                $videos[] = Storage::url($path);
            }
        }
        if ($request->has('videos') && is_array($request->videos)) {
            $videos = array_merge($videos, $request->videos);
        }

        // Обновление полей
        $listing->fill($validated);
        $listing->photos = json_encode($photos);
        $listing->videos = json_encode($videos);
        $listing->entrance = json_encode($validated['entrance'] ?? ($listing->entrance ? json_decode($listing->entrance, true) : []));
        $listing->appliances = json_encode($validated['appliances'] ?? ($listing->appliances ? json_decode($listing->appliances, true) : []));
        $listing->communication = json_encode($validated['communication'] ?? ($listing->communication ? json_decode($listing->communication, true) : []));
        $listing->living_conditions = json_encode($validated['living_conditions'] ?? ($listing->living_conditions ? json_decode($listing->living_conditions, true) : []));
        $listing->extras = json_encode($validated['extras'] ?? ($listing->extras ? json_decode($listing->extras, true) : []));

        $listing->save();

        return response()->json([
            'message' => 'Объявление успешно обновлено',
            'listing' => $listing,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Необходимо авторизоваться'], 401);
        }

        $listing = Listing::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$listing) {
            return response()->json(['message' => 'Объявление не найдено или вы не являетесь владельцем'], 404);
        }

        try {
            // Удаляем связанные файлы (фото и видео), если они есть
            if ($listing->photos) {
                foreach (json_decode($listing->photos, true) as $photo) {
                    $path = str_replace(Storage::url(''), 'public/', $photo);
                    Storage::delete($path);
                }
            }
            if ($listing->videos) {
                foreach (json_decode($listing->videos, true) as $video) {
                    $path = str_replace(Storage::url(''), 'public/', $video);
                    Storage::delete($path);
                }
            }

            // Удаляем объявление
            $listing->delete();

            return response()->json(['message' => 'Объявление успешно удалено']);
        } catch (\Exception $e) {
            \Log::error('Ошибка при удалении объявления: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка при удалении объявления'], 500);
        }
    }

    public function addToFavorites(Request $request, $id)
    {
        \Log::info('Попытка добавить в избранное', ['listing_id' => $id, 'user_id' => auth()->id()]);

        if (!auth()->check()) {
            \Log::warning('Неавторизованный доступ к addToFavorites');
            return response()->json(['message' => 'Необходимо авторизоваться'], 401);
        }

        $listing = Listing::find($id);
        if (!$listing || $listing->status !== 'active') {
            \Log::error('Объявление не найдено или не активно', ['listing_id' => $id]);
            return response()->json(['message' => 'Объявление не найдено или не активно'], 404);
        }

        $userId = auth()->id();
        $exists = Favorite::where('user_id', $userId)->where('listing_id', $id)->exists();

        if ($exists) {
            \Log::warning('Объявление уже в избранном', ['listing_id' => $id, 'user_id' => $userId]);
            return response()->json(['message' => 'Объявление уже в избранном'], 400);
        }

        try {
            Favorite::create([
                'user_id' => $userId,
                'listing_id' => $id,
            ]);
            \Log::info('Объявление добавлено в избранное', ['listing_id' => $id, 'user_id' => $userId]);
            return response()->json(['message' => 'Объявление добавлено в избранное']);
        } catch (\Exception $e) {
            \Log::error('Ошибка при добавлении в избранное', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Ошибка при добавлении в избранное'], 500);
        }
    }

    public function removeFromFavorites(Request $request, $id)
    {
        \Log::info('Попытка удалить из избранного', ['listing_id' => $id, 'user_id' => auth()->id()]);

        if (!auth()->check()) {
            \Log::warning('Неавторизованный доступ к removeFromFavorites');
            return response()->json(['message' => 'Необходимо авторизоваться'], 401);
        }

        $userId = auth()->id();
        $favorite = Favorite::where('user_id', $userId)->where('listing_id', $id)->first();

        if (!$favorite) {
            \Log::error('Объявление не найдено в избранном', ['listing_id' => $id, 'user_id' => $userId]);
            return response()->json(['message' => 'Объявление не найдено в избранном'], 404);
        }

        try {
            $favorite->delete();
            \Log::info('Объявление удалено из избранного', ['listing_id' => $id, 'user_id' => $userId]);
            return response()->json(['message' => 'Объявление удалено из избранного']);
        } catch (\Exception $e) {
            \Log::error('Ошибка при удалении из избранного', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Ошибка при удалении из избранного'], 500);
        }
    }

    public function getFavorites(Request $request)
    {
        \Log::info('Попытка получить избранное', ['user_id' => auth()->id()]);

        if (!auth()->check()) {
            \Log::warning('Неавторизованный доступ к getFavorites');
            return response()->json(['message' => 'Необходимо авторизоваться'], 401);
        }

        try {
            $favorites = Favorite::where('user_id', auth()->id())
                ->with(['listing' => function ($query) {
                    $query->where('status', 'active');
                }])
                ->get()
                ->pluck('listing')
                ->filter()
                ->values();
            \Log::info('Избранное успешно получено', ['count' => $favorites->count()]);
            return response()->json(['favorites' => $favorites]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении избранного', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Ошибка при получении избранного'], 500);
        }
    }

    public function topListings(Request $request)
    {
        $period = $request->input('period', 'day');
        $limit = $request->input('limit', 10);
        $propertyType = $request->input('property_type');

        $timeFrame = match ($period) {
            'week' => now()->subDays(7),
            'month' => now()->subDays(30),
            default => now()->subDays(1),
        };

        \Log::info('Top listings request:', [
            'period' => $period,
            'limit' => $limit,
            'property_type' => $propertyType,
            'timeFrame' => $timeFrame->toJson(),
        ]);

        $query = Listing::where('status', 'active');
        if ($propertyType) {
            $query->where('property_type', $propertyType);
        }

        $listings = $query->withCount([
            'views' => function ($query) use ($timeFrame) {
                $query->where('viewed_at', '>=', $timeFrame);
            },
            'favorites' => function ($query) use ($timeFrame) {
                $query->where('created_at', '>=', $timeFrame);
            }
        ])->get();

        if ($listings->isEmpty()) {
            \Log::info('No active listings found for top listings.');
            return response()->json(['top_listings' => [], 'message' => 'Нет активных объявлений для топа'], 200);
        }

        $listings = $listings->map(function ($listing) use ($timeFrame, $listings) { // Добавляем $listings в use
            $viewsCount = $listing->views_count ?? 0;
            $favoritesCount = $listing->favorites_count ?? 0;
            $timeSinceCreation = max(1, now()->diffInHours($listing->created_at ?: now()));
            $maxTime = 30 * 24;
            $creationScore = 1 - min($timeSinceCreation / $maxTime, 1);

            $maxViews = $listings->max('views_count') ?: 1; // Теперь $listings доступна
            $maxFavorites = $listings->max('favorites_count') ?: 1;

            $normalizedViews = $viewsCount / $maxViews;
            $normalizedFavorites = $favoritesCount / $maxFavorites;

            $rating = (0.5 * $normalizedViews) + (0.3 * $normalizedFavorites) + (0.2 * $creationScore);
            $listing->rating = round($rating, 3);
            return $listing;
        });

        $topListings = $listings->sortByDesc('rating')->take($limit)->values();

        \Log::info('Top listings result:', [
            'count' => $topListings->count(),
            'listing_ids' => $topListings->pluck('id')->toArray(),
            'ratings' => $topListings->pluck('rating')->toArray(),
        ]);

        return response()->json(['top_listings' => $topListings], 200);
    }
}
