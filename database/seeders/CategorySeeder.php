<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Category names in Tamil - each key is the top-level category name,
        // its array value holds the subcategory names.
        $categories = [
            'அரசியல்' => ['உள்நாட்டு அரசியல்', 'சர்வதேச அரசியல்'],
            'உலகம்' => ['ஆசியா', 'ஐரோப்பா', 'அமெரிக்கா'],
            'உள்ளூர்' => [],
            'வணிகம்' => ['சந்தை', 'தொடக்க நிறுவனங்கள்'],
            'தொழில்நுட்பம்' => ['செயற்கை நுண்ணறிவு', 'கேட்ஜெட்கள்'],
            'விளையாட்டு' => ['கிரிக்கெட்', 'கால்பந்து', 'தடகளம்'],
            'பொழுதுபோக்கு' => ['திரைப்படங்கள்', 'இசை'],
            'சுகாதாரம்' => [],
            'கல்வி' => [],
            'வாழ்க்கை முறை' => [],
            'பயணம்' => [],
        ];

        $order = 0;
        foreach ($categories as $name => $children) {
            // Slugs keep the Tamil script (see Category::getSlugOptions -
            // usingLanguage(null)) so URLs read naturally for Tamil readers,
            // e.g. /category/அரசியல் instead of a stripped/empty slug.
            $parent = Category::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($name, '-', null)],
                ['name' => $name, 'order' => $order++, 'is_active' => true]
            );

            $childOrder = 0;
            foreach ($children as $childName) {
                Category::updateOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($name.'-'.$childName, '-', null)],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->id,
                        'order' => $childOrder++,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
