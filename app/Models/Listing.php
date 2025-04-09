<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'user_id', 'deal_type', 'rent_type', 'property_type', 'address', 'apartment_number', 'floor',
        'total_floors', 'rooms', 'total_area', 'living_area', 'floors_in_apartment', 'guests',
        'balconies', 'loggias', 'view', 'bathrooms_combined', 'bathrooms_separate', 'repair',
        'elevators_cargo', 'elevators_passenger', 'entrance', 'parking', 'furniture', 'bathroom_type',
        'appliances', 'communication', 'cadastral_land', 'cadastral_house', 'land_area',
        'land_category', 'land_status', 'house_area', 'bedrooms', 'bathrooms', 'house_floors',
        'build_year', 'house_type', 'bathroom_location', 'sewerage', 'water_supply', 'gas',
        'heating', 'electricity', 'extras', 'title', 'description', 'photos', 'videos', 'price',
        'utilities_payer', 'prepayment', 'deposit', 'rent_term', 'living_conditions', 'phone', 'mortgage'
    ];

    protected $casts = [
        'entrance' => 'array',
        'appliances' => 'array',
        'communication' => 'array',
        'extras' => 'array',
        'photos' => 'array',
        'videos' => 'array',
        'living_conditions' => 'array'
    ];
}
