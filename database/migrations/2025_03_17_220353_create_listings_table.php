<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListingsTable extends Migration
{
    public function up()
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('deal_type'); // "rent" или "sale"
            $table->string('rent_type')->nullable(); // "long_term" или "short_term"
            $table->string('property_type'); // "apartment" или "house"
            $table->string('address');
            // Квартира
            $table->string('apartment_number')->nullable();
            $table->integer('floor')->nullable();
            $table->integer('total_floors')->nullable();
            $table->string('rooms')->nullable();
            $table->float('total_area')->nullable();
            $table->float('living_area')->nullable();
            $table->integer('floors_in_apartment')->nullable();
            $table->integer('guests')->nullable();
            $table->integer('balconies')->default(0);
            $table->integer('loggias')->default(0);
            $table->string('view')->nullable();
            $table->integer('bathrooms_combined')->default(0);
            $table->integer('bathrooms_separate')->default(0);
            $table->string('repair')->nullable();
            $table->integer('elevators_cargo')->default(0);
            $table->integer('elevators_passenger')->default(0);
            $table->json('entrance')->nullable();
            $table->string('parking')->nullable();
            $table->string('furniture')->nullable();
            $table->string('bathroom_type')->nullable();
            $table->json('appliances')->nullable();
            $table->json('communication')->nullable();
            // Дом
            $table->string('cadastral_land')->nullable();
            $table->string('cadastral_house')->nullable();
            $table->float('land_area')->nullable();
            $table->string('land_category')->nullable();
            $table->string('land_status')->nullable();
            $table->float('house_area')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('house_floors')->nullable();
            $table->integer('build_year')->nullable();
            $table->string('house_type')->nullable();
            $table->string('bathroom_location')->nullable();
            $table->string('sewerage')->nullable();
            $table->string('water_supply')->nullable();
            $table->string('gas')->nullable();
            $table->string('heating')->nullable();
            $table->string('electricity')->nullable();
            $table->json('extras')->nullable();
            // Общие
            $table->string('title');
            $table->text('description');
            $table->json('photos')->nullable();
            $table->json('videos')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('utilities_payer')->nullable();
            $table->string('prepayment')->nullable();
            $table->decimal('deposit', 15, 2)->nullable();
            $table->string('rent_term')->nullable();
            $table->json('living_conditions')->nullable();
            $table->string('phone');
            $table->string('mortgage')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('listings');
    }
}
