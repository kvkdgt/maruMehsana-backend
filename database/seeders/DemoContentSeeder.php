<?php

namespace Database\Seeders;

use App\Models\AppUser;
use App\Models\Business;
use App\Models\Category;
use App\Models\JobVacancy;
use App\Models\NewsAgency;
use App\Models\NewsArticle;
use App\Models\TouristPlace;
use App\Models\TouristPlaceImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Demo content for local development: businesses, job vacancies, news and tourist places.
 *
 * Safe to run more than once: rows are matched by name/title and updated.
 * Placeholder images are generated with GD into storage/app/public, so run
 * `php artisan storage:link` for them to show in the browser.
 *
 * Run: php artisan db:seed --class=DemoContentSeeder
 */
class DemoContentSeeder extends Seeder
{
    private array $palette = [
        [231, 111, 81], [42, 157, 143], [38, 70, 83], [233, 196, 106],
        [244, 162, 97], [69, 123, 157], [130, 88, 159], [87, 117, 144],
    ];

    public function run(): void
    {
        $admin = User::query()->orderBy('id')->first();

        $poster = AppUser::firstOrCreate(
            ['email' => 'demo.employer@marumehsana.test'],
            [
                'name' => 'Demo Employer',
                'phone' => '9000000001',
                'password' => Hash::make('password'),
            ]
        );

        $this->seedBusinesses($admin?->id);
        $this->seedJobs($poster->id);
        $this->seedNews();

        if ($admin) {
            $this->seedTouristPlaces($admin->id);
        }
    }

    private function seedTouristPlaces(int $adminId): void
    {
        $places = [
            ['Modhera Sun Temple', 'Modhera, Mehsana', 23.5835, 72.1332, 'An 11th-century Sun temple built by King Bhimdev I of the Chalukya dynasty, famous for its stepwell and carvings.'],
            ['Bahucharaji Temple', 'Becharaji, Mehsana', 23.4947, 72.0396, 'A revered Shaktipeeth dedicated to Bahuchara Mata, visited by lakhs of devotees every year.'],
            ['Thol Bird Sanctuary', 'Thol, Kadi', 23.1437, 72.3789, 'A freshwater lake sanctuary with more than 150 species of birds, best visited in winter.'],
            ['Taranga Jain Temple', 'Taranga Hills, Kheralu', 23.9781, 72.7547, 'A 12th-century Jain temple on Taranga hill, built by King Kumarapala.'],
            ['Rudra Mahalaya', 'Siddhpur', 23.9200, 72.3758, 'Ruins of a grand 12th-century Shiva temple on the bank of the Saraswati river.'],
            ['Shankus Water Park', 'Ahmedabad-Mehsana Highway', 23.4740, 72.3860, 'A large water and amusement park, popular for family weekend trips.'],
            ['Umiya Mata Temple', 'Unjha', 23.8040, 72.3940, 'Main temple of Umiya Mata, the family deity of the Kadva Patidar community.'],
            ['Hatkeshwar Mahadev Temple', 'Vadnagar', 23.7860, 72.6400, 'An ancient Shiva temple in the historic town of Vadnagar, known for its stone carvings.'],
        ];

        foreach ($places as $i => [$name, $location, $lat, $lng, $description]) {
            $place = TouristPlace::updateOrCreate(['name' => $name], [
                'description' => $description,
                'location' => $location,
                'latitude' => $lat,
                'longitude' => $lng,
                'thumbnail' => $this->placeholder("demo/place-{$i}.png", $name, $i + 1),
                'visitors' => random_int(200, 8000),
                'created_by' => $adminId,
            ]);

            foreach ([1, 2] as $n) {
                $path = $this->placeholder("demo/place-{$i}-{$n}.png", "{$name} ({$n})", $i + $n + 2);
                if ($path) {
                    TouristPlaceImage::firstOrCreate(['tourist_place_id' => $place->id, 'image' => $path]);
                }
            }
        }
    }

    private function seedBusinesses(?int $adminId): void
    {
        $businesses = [
            ['Shree Radhe Restaurant', 'Restaurant and Cafe', 'Authentic Gujarati thali and Punjabi dishes near Modhera Circle.', 'Gujarati Thali, Punjabi, Chinese', 'Party Packs, Tiffin'],
            ['Mehsana Sweets & Farsan', 'Sweets & Namkeen', 'Fresh sweets and farsan made daily since 1985.', 'Bulk Orders, Festival Hampers', 'Kaju Katli, Fafda, Jalebi'],
            ['City Care Pharmacy', 'Pharmacy & Medical', 'Open 7 days with home delivery of medicines across Mehsana.', 'Home Delivery, Prescription Refill', 'Medicines, Surgical Items'],
            ['TechZone Computers', 'Computer & Hardware', 'Laptops, desktops and accessories with on-site repair service.', 'Repair, AMC, Networking', 'Laptops, Printers, CCTV'],
            ['Sai Grocery Mart', 'Grocery', 'Daily groceries, fruits and household items at fair prices.', 'Home Delivery', 'Grains, Spices, Dairy'],
            ['Glamour Beauty Salon', 'Beauty Salon', 'Bridal makeup, hair styling and skin care by trained experts.', 'Bridal Makeup, Hair Spa, Facial', 'Beauty Products'],
            ['Patel Real Estate', 'Real Estate', 'Residential and commercial properties in Mehsana and nearby towns.', 'Buy, Sell, Rent, Legal Help', '2BHK, 3BHK, Shops, Plots'],
            ['Bright Future Classes', 'Tution Classes', 'Coaching for std 8 to 12 science and commerce students.', 'Science, Commerce, JEE/NEET Foundation', 'Study Material'],
            ['Shiv Car Garage', 'Car Garages', 'Car service, denting, painting and genuine spare parts.', 'Servicing, Denting, Painting', 'Spare Parts, Batteries'],
            ['Click Studio', 'Photography & Videography', 'Wedding, pre-wedding and event photography with drone shoots.', 'Wedding, Events, Product Shoots', 'Albums, Frames'],
            ['Cool Air Services', 'Air Conditioning Services', 'AC installation, gas filling and repair for all brands.', 'Installation, Repair, AMC', 'Split AC, Window AC'],
            ['Royal Bakery', 'Bakery & Cakes', 'Custom cakes, cookies and fresh bread baked every morning.', 'Custom Cakes, Party Orders', 'Cakes, Pastries, Bread'],
        ];

        $categories = Category::query()->pluck('id', 'name');
        $fallbackCategory = $categories->first();

        foreach ($businesses as $i => [$name, $categoryName, $description, $services, $products]) {
            $phone = '98250' . str_pad((string) (10000 + $i * 731), 5, '0', STR_PAD_LEFT);

            Business::updateOrCreate(['name' => $name], [
                'description' => $description,
                'thumbnail' => $this->placeholder("demo/business-{$i}.png", $name, $i),
                'category_id' => $categories[$categoryName] ?? $fallbackCategory,
                'mobile_no' => $phone,
                'whatsapp_no' => $phone,
                'email_id' => Str::slug($name) . '@example.com',
                'website_url' => null,
                'services' => $services,
                'products' => $products,
                'visitors' => random_int(50, 2500),
                'created_by' => $adminId,
            ]);
        }
    }

    private function seedJobs(int $posterId): void
    {
        $jobs = [
            ['Sales Executive', 'Mehsana Mall', 'full_time', 'Mehsana', 12000, 18000, 'monthly', '1-2 years', '12th Pass', 'call'],
            ['Accountant', 'Shah & Associates', 'full_time', 'Mehsana', 15000, 25000, 'monthly', '2+ years', 'B.Com / M.Com', 'email'],
            ['Delivery Boy', 'Sai Grocery Mart', 'part_time', 'Mehsana', 8000, 10000, 'monthly', 'Fresher', '10th Pass', 'whatsapp'],
            ['Computer Teacher', 'Bright Future Classes', 'part_time', 'Visnagar', 200, 300, 'hourly', '1 year', 'BCA / MCA', 'walk_in'],
            ['Receptionist', 'Apex Healthcare Hospital', 'full_time', 'Mehsana', 10000, 14000, 'monthly', 'Fresher', 'Graduate', 'walk_in'],
            ['Graphic Designer', 'Click Studio', 'freelance', 'Remote / Mehsana', null, null, 'not_disclosed', '1-3 years', 'Any', 'whatsapp'],
            ['Car Mechanic', 'Shiv Car Garage', 'full_time', 'Unjha', 14000, 20000, 'monthly', '2+ years', 'ITI', 'call'],
            ['Marketing Intern', 'TechZone Computers', 'internship', 'Mehsana', 5000, 7000, 'monthly', 'Fresher', 'MBA / BBA (pursuing)', 'email'],
            ['Pharmacist', 'City Care Pharmacy', 'full_time', 'Mehsana', 18000, 24000, 'monthly', '1+ years', 'B.Pharm / D.Pharm', 'call'],
            ['Site Supervisor', 'Patel Real Estate', 'contract', 'Kadi', 250000, 360000, 'yearly', '3+ years', 'Diploma Civil', 'call'],
        ];

        foreach ($jobs as $i => [$title, $company, $type, $location, $min, $max, $salaryType, $exp, $edu, $applyVia]) {
            JobVacancy::updateOrCreate(['title' => $title, 'company_name' => $company], [
                'posted_by' => $posterId,
                'job_type' => $type,
                'location' => $location,
                'salary_min' => $min,
                'salary_max' => $max,
                'salary_type' => $salaryType,
                'description' => "{$company} is hiring a {$title} in {$location}. Good working environment and timely salary.",
                'requirements' => "Experience: {$exp}. Education: {$edu}. Good communication skills.",
                'vacancies_count' => random_int(1, 5),
                'experience_required' => $exp,
                'education_required' => $edu,
                'gender_preference' => 'any',
                'contact_name' => 'HR Desk',
                'contact_mobile' => '98790' . str_pad((string) (20000 + $i * 913), 5, '0', STR_PAD_LEFT),
                'contact_email' => 'hr@' . Str::slug($company) . '.example.com',
                'apply_via' => $applyVia,
                'thumbnail' => $this->placeholder("demo/job-{$i}.png", $title, $i + 3),
                'expires_at' => now()->addDays(30 + $i * 3)->toDateString(),
                'status' => 'open',
                'is_active' => 1,
                'views_count' => random_int(0, 400),
            ]);
        }
    }

    private function seedNews(): void
    {
        $agencies = collect([
            ['name' => 'Mehsana Times', 'email' => 'news@mehsanatimes.example.com', 'username' => 'mehsanatimes'],
            ['name' => 'Uttar Gujarat Express', 'email' => 'desk@ugexpress.example.com', 'username' => 'ugexpress'],
        ])->map(fn ($a) => NewsAgency::updateOrCreate(['username' => $a['username']], $a + ['status' => true]));

        $articles = [
            ['Smart City work speeds up on Modhera Road', 'New roads, LED street lights and drainage lines are being laid on Modhera Road.', true, true],
            ['Navratri preparations in full swing across Mehsana', 'Garba organisers finalise safety plans and parking arrangements for the festival.', true, false],
            ['Dudhsagar Dairy announces higher milk procurement price', 'Farmers will get a higher price per kg fat from next month.', true, true],
            ['New bus routes connect Mehsana with nearby villages', 'GSRTC adds six new routes to improve daily travel for students and workers.', true, false],
            ['District hospital opens new dialysis unit', 'The unit has ten machines and offers free treatment under government schemes.', true, false],
            ['Mehsana students shine in state board results', 'Several schools report over 90 percent pass results this year.', true, true],
            ['Monsoon update: moderate rain expected this week', 'The weather department forecasts moderate rain in north Gujarat districts.', false, false],
            ['Gujarat startup summit announces new grants', 'Young founders can apply for seed funding of up to Rs 10 lakh.', false, false],
            ['Modhera Sun Temple sees record tourist footfall', 'Weekend visitors cross 20,000 after the light and sound show restarts.', true, false],
            ['Free health check-up camp on Sunday at Town Hall', 'Doctors will offer free BP, sugar and eye check-ups for all age groups.', true, false],
        ];

        foreach ($articles as $i => [$title, $excerpt, $forMehsana, $featured]) {
            $file = "demo-{$i}.png";
            $this->placeholder("news/{$file}", $title, $i + 5);

            NewsArticle::updateOrCreate(['slug' => Str::slug($title)], [
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => "<p>{$excerpt}</p><p>This is demo content for testing the news section. Replace it with real articles before going live.</p>",
                'image' => $file,
                'is_active' => true,
                'is_featured' => $featured,
                'is_for_mehsana' => $forMehsana,
                'visitor' => random_int(20, 3000),
                'agency_id' => $agencies[$i % $agencies->count()]->id,
            ]);
        }
    }

    /**
     * Write a simple coloured PNG with a label to the public disk and return its path.
     * Returns null when GD is not available.
     */
    private function placeholder(string $path, string $label, int $colorIndex): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        $img = imagecreatetruecolor(600, 400);
        [$r, $g, $b] = $this->palette[$colorIndex % count($this->palette)];
        imagefill($img, 0, 0, imagecolorallocate($img, $r, $g, $b));
        $white = imagecolorallocate($img, 255, 255, 255);

        $lines = explode("\n", wordwrap($label, 28, "\n", true));
        $font = 5;
        $lineHeight = imagefontheight($font) + 6;
        $y = (int) ((400 - count($lines) * $lineHeight) / 2);
        foreach ($lines as $line) {
            $x = (int) ((600 - imagefontwidth($font) * strlen($line)) / 2);
            imagestring($img, $font, $x, $y, $line, $white);
            $y += $lineHeight;
        }

        ob_start();
        imagepng($img);
        Storage::disk('public')->put($path, ob_get_clean());
        imagedestroy($img);

        return $path;
    }
}
