<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['slug' => 'discover', 'title' => 'Discover', 'sort_order' => 10, 'content' => '<p>Discover our platform. Find trusted handymen and service providers for repairs, maintenance, and home improvement.</p><p>Browse by category, compare reviews, and book in minutes.</p>'],
            ['slug' => 'about-us', 'title' => 'About us', 'sort_order' => 20, 'content' => '<p>We connect homeowners with skilled professionals for repairs, maintenance, and improvements.</p><p>Our mission is to make booking a pro simple, transparent, and reliable.</p>'],
            ['slug' => 'investors', 'title' => 'Investors', 'sort_order' => 30, 'content' => '<p>Interested in partnering with us? We are building the future of local home services.</p><p>Contact us for investor relations and opportunities.</p>'],
            ['slug' => 'careers', 'title' => 'Careers', 'sort_order' => 40, 'content' => '<p>Join our team. We are always looking for talented people to help us grow.</p><p>Open roles in engineering, operations, and customer success.</p>'],
            ['slug' => 'partnership', 'title' => 'Partnership', 'sort_order' => 50, 'content' => '<p>Partner with us to reach more customers and grow your business.</p><p>We work with service providers, brands, and platforms.</p>'],
        ];

        foreach ($pages as $data) {
            Page::updateOrCreate(
                ['slug' => $data['slug']],
                ['title' => $data['title'], 'content' => $data['content'], 'is_active' => true, 'sort_order' => $data['sort_order']]
            );
        }
    }
}
