<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Business;
use App\Models\TouristPlace;
use App\Models\NewsAgency;
use App\Models\NewsArticle;
use App\Models\Fact;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // Create an admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@marumehsana.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );

        // 1. Categories
        $categories = [
            ['name' => 'Restaurants', 'description' => 'Find the best places to eat in Mehsana.', 'image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1000', 'category_visitors' => 1200],
            ['name' => 'Real Estate', 'description' => 'Property and housing listings in the area.', 'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=1000', 'category_visitors' => 850],
            ['name' => 'Education', 'description' => 'Schools, colleges, and coaching centers.', 'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=1000', 'category_visitors' => 950],
            ['name' => 'Healthcare', 'description' => 'Hospitals, clinics, and pharmacies.', 'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?q=80&w=1000', 'category_visitors' => 1100],
            ['name' => 'Shopping', 'description' => 'Malls, markets, and retail stores.', 'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=1000', 'category_visitors' => 1400],
            ['name' => 'Automobiles', 'description' => 'Showrooms and repair services.', 'image' => 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?q=80&w=1000', 'category_visitors' => 600],
            ['name' => 'Beauty & Spa', 'description' => 'Salons and wellness centers.', 'image' => 'https://images.unsplash.com/photo-1560750588-73207b1ef5b8?q=80&w=1000', 'category_visitors' => 400],
            ['name' => 'Services', 'description' => 'Legal, plumbing, electrical, and more.', 'image' => 'https://images.unsplash.com/photo-1581578731548-c64695ce6958?q=80&w=1000', 'category_visitors' => 750],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['name' => $cat['name']], array_merge($cat, ['created_by' => $admin->id]));
        }

        $allCategories = Category::all();

        // 2. Businesses
        $businesses = [
            [
                'name' => 'Shree Radhe Restaurant',
                'description' => 'Best authentic Gujarati Thali in Mehsana with a touch of traditional home-made flavor.',
                'thumbnail' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?q=80&w=1000',
                'category_id' => $allCategories->where('name', 'Restaurants')->first()->id,
                'mobile_no' => '9876543210',
                'whatsapp_no' => '9876543210',
                'website_url' => 'https://radherestaurant.com',
                'email_id' => 'contact@radhe.com',
                'services' => 'Gujarati Thali, Punjabi, Chinese',
                'products' => 'Customized Meals, Party Packs',
                'visitors' => 500,
                'created_by' => $admin->id
            ],
            [
                'name' => 'Mehsana Mall',
                'description' => 'One stop shopping destination for all your needs in the heart of Mehsana city.',
                'thumbnail' => 'https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?q=80&w=1000',
                'category_id' => $allCategories->where('name', 'Shopping')->first()->id,
                'mobile_no' => '9898989898',
                'whatsapp_no' => '9898989898',
                'website_url' => 'https://mehsanamall.com',
                'email_id' => 'info@mehsanamall.com',
                'services' => 'Retail Shopping, Cinema, Food Court',
                'products' => 'Clothing, Electronics, Grocery',
                'visitors' => 2000,
                'created_by' => $admin->id
            ],
            [
                'name' => 'Apex Healthcare Hospital',
                'description' => 'Advanced multi-specialty hospital providing 24/7 emergency care and specialized medical services.',
                'thumbnail' => 'https://images.unsplash.com/photo-1586773860418-d3b97998c63f?q=80&w=1000',
                'category_id' => $allCategories->where('name', 'Healthcare')->first()->id,
                'mobile_no' => '9123456789',
                'email_id' => 'care@apexhealth.com',
                'services' => 'Emergency, Surgery, ICU, Pharmacy',
                'visitors' => 1200,
                'created_by' => $admin->id
            ]
        ];

        foreach ($businesses as $biz) {
            Business::updateOrCreate(['name' => $biz['name']], $biz);
        }

        // 3. Tourist Places
        $places = [
            [
                'name' => 'Modhera Sun Temple',
                'description' => 'A unique temple dedicated to the Sun God, Surya, built in 1026-27 CE by King Bhimdev I of the Chalukya dynasty.',
                'thumbnail' => 'https://images.unsplash.com/photo-1596422846543-75c6fc183f27?q=80&w=1000',
                'location' => 'Modhera, Mehsana district',
                'latitude' => 23.5835,
                'longitude' => 72.1332,
                'visitors' => 5000,
                'created_by' => $admin->id
            ],
            [
                'name' => 'Bahucharaji Temple',
                'description' => 'A highly revered Shakthipith temple dedicated to Mother Bahuchara, located in the town of Bahucharaji.',
                'thumbnail' => 'https://images.unsplash.com/photo-1621252178351-409c9197c36d?q=80&w=1000',
                'location' => 'Becharaji, Mehsana',
                'latitude' => 23.4947,
                'longitude' => 72.0396,
                'visitors' => 8000,
                'created_by' => $admin->id
            ],
            [
                'name' => 'Thol Bird Sanctuary',
                'description' => 'A shallow freshwater lake and bird sanctuary featuring more than 150 species of birds, perfect for nature lovers.',
                'thumbnail' => 'https://images.unsplash.com/photo-1444464666168-49d633b867ad?q=80&w=1000',
                'location' => 'Thol, Kadi Mehsana',
                'latitude' => 23.1437,
                'longitude' => 72.3789,
                'visitors' => 3000,
                'created_by' => $admin->id
            ]
        ];

        foreach ($places as $place) {
            TouristPlace::updateOrCreate(['name' => $place['name']], array_merge($place));
        }

        // 4. News Agencies
        $agencies = [
            ['name' => 'Mehsana Times', 'email' => 'news@mehsanatimes.com', 'username' => 'mehsanatimes', 'status' => true],
            ['name' => 'Gujarat Samachar Mehsana', 'email' => 'mehsana@gujarat.com', 'username' => 'gujaratsamachar', 'status' => true]
        ];

        foreach ($agencies as $agency) {
            NewsAgency::updateOrCreate(['name' => $agency['name']], $agency);
        }

        $allAgencies = NewsAgency::all();

        // 5. News Articles
        $articles = [
            [
                'title' => 'Mehsana Smart City Project Updates',
                'excerpt' => 'Municipal corporation announces new progress milestones in the Smart City infrastructure project.',
                'content' => 'Full details about the smart city project including new roads, lighting and drainage systems being implemented across Mehsana.',
                'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1000',
                'is_active' => true,
                'is_featured' => true,
                'is_for_mehsana' => true,
                'visitor' => 1500,
                'agency_id' => $allAgencies->first()->id
            ],
            [
                'title' => 'Local Festival Preparations in Full Swing',
                'excerpt' => 'Mehsana gears up for the upcoming Navratri celebrations with organized city-wide cultural events.',
                'content' => 'Committees have been formed to ensure safety and quality of the traditional Garba events happening throughout the district.',
                'image' => 'https://images.unsplash.com/photo-1543007630-9710e4a00a20?q=80&w=1000',
                'is_active' => true,
                'is_featured' => false,
                'is_for_mehsana' => true,
                'visitor' => 800,
                'agency_id' => $allAgencies->last()->id
            ]
        ];

        foreach ($articles as $art) {
            // Need to handle the image URL vs path in the model accessor
            NewsArticle::updateOrCreate(['title' => $art['title']], [
                'excerpt' => $art['excerpt'],
                'content' => $art['content'],
                'image' => $art['image'],
                'is_active' => $art['is_active'],
                'is_featured' => $art['is_featured'],
                'is_for_mehsana' => $art['is_for_mehsana'],
                'visitor' => $art['visitor'],
                'agency_id' => $art['agency_id']
            ]);
        }

        // 6. Facts
        $facts = [
            ['fact' => 'Mehsana is home to the first Dudhsagar Dairy, founded in 1963.'],
            ['fact' => 'The Modhera Sun Temple in Mehsana is designed so that the sun shines directly on the deity during equinoxes.'],
            ['fact' => 'Mehsana is known for being one of the largest oil and gas producing regions in India through ONGC.'],
            ['fact' => 'The Bahucharaji Temple is one of the three principal Shakthipiths in Gujarat.'],
        ];

        foreach ($facts as $fact) {
            Fact::updateOrCreate(['fact' => $fact['fact']], $fact);
        }
    }
}
