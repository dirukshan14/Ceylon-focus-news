<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Article;
use App\Models\BreakingNews;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'author@ceylonfocus.test')->first() ?? User::first();
        $categories = Category::whereNotNull('parent_id')->orWhereDoesntHave('children')->get();

        if ($categories->isEmpty() || ! $author) {
            return; // categories/users seeders didn't run yet
        }

        $tamilArticles = [
            [
                'title' => 'நாடு முழுவதும் புதிய பொருளாதார கொள்கை அறிவிப்பு',
                'excerpt' => 'அரசாங்கம் இன்று புதிய பொருளாதார சீர்திருத்த திட்டத்தை அறிமுகப்படுத்தியது, இது நாட்டின் வளர்ச்சி விகிதத்தை மேம்படுத்தும் என எதிர்பார்க்கப்படுகிறது.',
                'body' => '<p>அரசாங்கம் இன்று நாடாளுமன்றத்தில் புதிய பொருளாதார சீர்திருத்தத் திட்டத்தை அறிமுகப்படுத்தியது. இந்த திட்டம் விவசாயம், தொழில்துறை மற்றும் சிறு வணிகங்களுக்கு நேரடி பலனளிக்கும் என அமைச்சர் தெரிவித்தார்.</p><p>இந்த திட்டத்தின் கீழ், சிறு மற்றும் நடுத்தர வணிகங்களுக்கு குறைந்த வட்டி விகிதத்தில் கடன் வழங்கப்படும். மேலும், ஏற்றுமதி தொழில்களுக்கு புதிய ஊக்கத் திட்டங்களும் அறிவிக்கப்பட்டுள்ளன.</p><p>எதிர்க்கட்சிகள் இந்த திட்டம் குறித்து கலந்துரையாடலுக்கு அழைப்பு விடுத்துள்ளன.</p>',
                'featured' => true, 'breaking' => true,
            ],
            [
                'title' => 'கொழும்பில் புதிய போக்குவரத்து திட்டம் தொடக்கம்',
                'excerpt' => 'நகர போக்குவரத்து நெரிசலைக் குறைக்கும் நோக்கில் புதிய பேருந்து வழித்தட திட்டம் இன்று அறிமுகமானது.',
                'body' => '<p>கொழும்பு நகர சபை இன்று புதிய வேக பேருந்து வழித்தடத்தை (BRT) அறிமுகப்படுத்தியது. இது நகரின் முக்கிய பகுதிகளை இணைக்கும் என அதிகாரிகள் தெரிவித்தனர்.</p><p>இந்த திட்டத்தின் முதற்கட்டம் அடுத்த மாதம் முழுமையாக செயல்பாட்டுக்கு வரும் என எதிர்பார்க்கப்படுகிறது.</p>',
                'featured' => true, 'breaking' => false,
            ],
            [
                'title' => 'இலங்கை கிரிக்கெட் அணி இறுதிப் போட்டிக்கு தகுதி',
                'excerpt' => 'தொடர் இறுதிப் போட்டியில் இடம்பெற இலங்கை அணி இன்று வெற்றி பெற்றது.',
                'body' => '<p>இன்று நடைபெற்ற அரையிறுதி போட்டியில் இலங்கை அணி எதிராளி அணியை வீழ்த்தி இறுதிப் போட்டிக்கு தகுதி பெற்றது. கேப்டன் அணியின் சிறந்த ஆட்டத்தைப் பாராட்டினார்.</p><p>இறுதிப் போட்டி இந்த வார இறுதியில் நடைபெறவுள்ளது.</p>',
                'featured' => true, 'breaking' => true,
            ],
            [
                'title' => 'மாணவர்களுக்கான புதிய கல்வி உதவித்தொகை திட்டம்',
                'excerpt' => 'உயர் கல்வி அமைச்சு புதிய உதவித்தொகை திட்டத்தை அறிவித்துள்ளது.',
                'body' => '<p>உயர்கல்வி அமைச்சு, குறைந்த வருமானம் கொண்ட குடும்பங்களைச் சேர்ந்த மாணவர்களுக்காக புதிய உதவித்தொகை திட்டத்தை அறிமுகப்படுத்தியுள்ளது.</p><p>விண்ணப்பங்கள் அடுத்த வாரம் முதல் ஏற்றுக்கொள்ளப்படும்.</p>',
                'featured' => false, 'breaking' => false,
            ],
            [
                'title' => 'தொழில்நுட்பத் துறையில் புதிய முதலீடு அறிவிப்பு',
                'excerpt' => 'வெளிநாட்டு நிறுவனம் ஒன்று நாட்டின் தகவல் தொழில்நுட்பத் துறையில் பெரும் முதலீடு செய்ய திட்டமிட்டுள்ளது.',
                'body' => '<p>முன்னணி தொழில்நுட்ப நிறுவனம் ஒன்று, அடுத்த மூன்று ஆண்டுகளில் நாட்டில் புதிய தொழில்நுட்ப மையத்தை அமைக்க திட்டமிட்டுள்ளதாக அறிவித்துள்ளது.</p><p>இது ஆயிரக்கணக்கான புதிய வேலைவாய்ப்புகளை உருவாக்கும் என எதிர்பார்க்கப்படுகிறது.</p>',
                'featured' => false, 'breaking' => false,
            ],
            [
                'title' => 'சுகாதார அமைச்சு புதிய தடுப்பூசி திட்டம் அறிவிப்பு',
                'excerpt' => 'குழந்தைகளுக்கான புதிய தடுப்பூசி திட்டம் அடுத்த மாதம் தொடங்கவுள்ளது.',
                'body' => '<p>சுகாதார அமைச்சு, நாடு தழுவிய தடுப்பூசி வழங்கல் திட்டத்தை அடுத்த மாதம் தொடங்க திட்டமிட்டுள்ளது. அனைத்து பொது சுகாதார நிலையங்களிலும் இந்த சேவை கிடைக்கும்.</p>',
                'featured' => false, 'breaking' => false,
            ],
        ];

        foreach ($tamilArticles as $i => $data) {
            $category = $categories->random();

            Article::updateOrCreate(
                ['title' => $data['title']],
                [
                    'excerpt' => $data['excerpt'],
                    'body' => $data['body'],
                    'category_id' => $category->parent_id ?? $category->id,
                    'subcategory_id' => $category->parent_id ? $category->id : null,
                    'author_id' => $author->id,
                    'status' => 'published',
                    'is_featured' => $data['featured'],
                    'is_breaking' => $data['breaking'],
                    'is_trending' => $i % 2 === 0,
                    'published_at' => now()->subHours($i + 1),
                    'views_count' => rand(200, 8000),
                ]
            );
        }

        $breakingItems = [
            'நாடு முழுவதும் புதிய பொருளாதார கொள்கை அறிவிப்பு',
            'இலங்கை கிரிக்கெட் அணி இறுதிப் போட்டிக்கு தகுதி',
            'வானிலை எச்சரிக்கை: வடக்கு மற்றும் கிழக்கு மாகாணங்களில் கனமழை',
        ];
        foreach ($breakingItems as $priority => $text) {
            $article = Article::where('title', $text)->first();
            BreakingNews::updateOrCreate(
                ['text' => $text],
                [
                    'article_id' => $article?->id,
                    'priority' => 10 - $priority,
                    'is_active' => true,
                ]
            );
        }

        $tamilVideos = [
            ['title' => 'இன்றைய முக்கிய செய்திகள் - வீடியோ சுருக்கம்', 'description' => 'இன்றைய நாட்டின் முக்கிய செய்திகளின் வீடியோ சுருக்கம்.'],
            ['title' => 'விளையாட்டு உலகம்: இன்றைய சிறப்பம்சங்கள்', 'description' => 'இன்றைய விளையாட்டு போட்டிகளின் சிறப்பம்சங்கள்.'],
        ];

        foreach ($tamilVideos as $i => $data) {
            Video::updateOrCreate(
                ['title' => $data['title']],
                [
                    'description' => $data['description'],
                    'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'embed_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    'provider' => 'youtube',
                    'category_id' => $categories->random()->id,
                    'author_id' => $author->id,
                    'status' => 'published',
                    'is_featured' => true,
                    'published_at' => now()->subHours($i),
                ]
            );
        }

        $adPositions = ['header', 'homepage_top', 'homepage_middle', 'sidebar', 'article_top', 'footer'];
        foreach ($adPositions as $position) {
            Advertisement::updateOrCreate(
                ['name' => 'Demo Ad - '.ucwords(str_replace('_', ' ', $position))],
                [
                    'type' => 'html',
                    'html_code' => '<div style="padding:20px;text-align:center;background:#eee;">விளம்பரம் - '.$position.'</div>',
                    'position' => $position,
                    'device_target' => 'all',
                    'is_active' => true,
                    'priority' => 1,
                ]
            );
        }

        $defaults = [
            'site_name' => ['Ceylon Focus News', 'general'],
            'site_tagline' => ['உண்மையை மையமாகக் கொண்டது', 'general'],
            'site_description' => ['நம்பகமான தமிழ் செய்தி ஊடகம் - சமீபத்திய செய்திகள், வீடியோக்கள் மற்றும் புதுப்பிப்புகள்.', 'general'],
            'contact_email' => ['info@ceylonfocus.test', 'general'],
            'meta_title' => ['Ceylon Focus News - சமீபத்திய தமிழ் செய்திகள்', 'seo'],
            'meta_description' => ['அரசியல், வணிகம், விளையாட்டு, தொழில்நுட்பம் மற்றும் பலவற்றின் சமீபத்திய தமிழ் செய்திகள்.', 'seo'],
            'primary_color' => ['#1e3a8a', 'appearance'],
            'secondary_color' => ['#dc2626', 'appearance'],
            'footer_text' => ['© '.date('Y').' Ceylon Focus News. அனைத்து உரிமைகளும் பாதுகாக்கப்பட்டவை.', 'appearance'],
            'live_score_title' => ['நேரடி ஸ்கோர்', 'live_score'],
            'live_score_content' => ["இலங்கை 187/4 (32.2 ஓவர்)\nvs இந்தியா 210/8 (50 ஓவர்)", 'live_score'],
        ];

        foreach ($defaults as $key => [$value, $group]) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }
    }
}
