<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $categories = Category::all();

        if ($categories->isEmpty() || ! $admin) {
            return;
        }

        foreach (range(1, 15) as $i) {
            $category = $categories->random();

            News::create([
                'title'        => "Sample News Article {$i}",
                'excerpt'      => 'This is a short summary of the sample news article used for demonstration purposes.',
                'content'      => '<p>This is placeholder body content for a sample news article. Replace this with real content from the admin panel.</p>',
                'category_id'  => $category->id,
                'user_id'      => $admin->id,
                'status'       => 'published',
                'published_at' => now()->subHours($i),
            ]);
        }
    }
}
