<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Article;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Security regression tests. Run with: php artisan test
 * (uses the in-memory SQLite database configured in phpunit.xml; requires the pdo_sqlite PHP extension).
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
        RateLimiter::clear('x');
    }

    private function userWithRole(string $slug, array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        $user->roles()->attach(Role::where('slug', $slug)->firstOrFail()->id);

        return $user;
    }

    private function superAdmin(): User
    {
        $u = User::factory()->create();
        $u->forceFill(['is_super_admin' => true])->save();

        return $u;
    }

    private function article(string $status = 'published', string $body = '<p>hi</p>'): Article
    {
        $cat = Category::create(['name' => 'News '.uniqid()]);

        return Article::create([
            'title' => 'T '.uniqid(), 'body' => $body, 'category_id' => $cat->id,
            'author_id' => User::factory()->create()->id, 'status' => $status,
            'published_at' => now()->subMinute(),
        ]);
    }

    /* ---------- Admin access ---------- */

    public function test_guests_are_redirected_from_admin(): void
    {
        foreach (['/admin', '/admin/articles', '/admin/advertisements', '/admin/settings', '/admin/administrators', '/admin/roles'] as $url) {
            $this->get($url)->assertRedirect();
        }
    }

    public function test_reader_without_roles_gets_403_in_admin(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_author_cannot_reach_admin_only_sections(): void
    {
        $author = $this->userWithRole(Role::AUTHOR);

        foreach (['/admin/advertisements', '/admin/settings', '/admin/administrators', '/admin/roles', '/admin/videos'] as $url) {
            $this->actingAs($author)->get($url)->assertForbidden();
        }
        $ad = Advertisement::create(['name' => 'a', 'type' => 'html', 'html_code' => '<b>x</b>', 'position' => 'sidebar', 'device_target' => 'all']);
        $this->actingAs($author)->delete("/admin/advertisements/{$ad->id}")->assertForbidden();
        $this->assertNotNull($ad->fresh());
    }

    /* ---------- Privilege escalation ---------- */

    public function test_user_cannot_become_super_admin_by_mass_assignment(): void
    {
        $u = User::create(['name' => 'a', 'email' => 'a@x.test', 'password' => 'secret-pass-123', 'is_super_admin' => true]);
        $this->assertFalse($u->fresh()->isSuperAdmin());
    }

    public function test_non_super_admin_cannot_modify_or_reset_a_super_admin(): void
    {
        $admin = $this->userWithRole(Role::ADMINISTRATOR);
        $admin->directPermissions()->attach(Permission::where('slug', Permission::MANAGE_ADMINISTRATORS)->first()->id);
        $super = $this->superAdmin();

        $this->actingAs($admin)->post("/admin/administrators/{$super->id}/reset-password")->assertForbidden();
        $this->actingAs($admin)->delete("/admin/administrators/{$super->id}")->assertForbidden();
    }

    public function test_non_super_admin_cannot_grant_privileged_roles(): void
    {
        $admin = $this->userWithRole(Role::ADMINISTRATOR);
        $admin->directPermissions()->attach(Permission::where('slug', Permission::MANAGE_ADMINISTRATORS)->first()->id);
        $target = $this->userWithRole(Role::AUTHOR);

        $this->actingAs($admin)->put("/admin/administrators/{$target->id}", [
            'name' => 'x', 'email' => $target->email, 'status' => 'active',
            'roles' => [Role::where('slug', Role::SUPER_ADMIN)->first()->id],
        ])->assertForbidden();
    }

    public function test_non_super_admin_cannot_grant_role_management_permission(): void
    {
        $admin = $this->userWithRole(Role::ADMINISTRATOR);
        $admin->directPermissions()->attach(Permission::where('slug', Permission::MANAGE_ROLES)->first()->id);
        $role = Role::where('slug', Role::AUTHOR)->first();

        $this->actingAs($admin)->put("/admin/roles/{$role->id}", [
            'name' => 'Author', 'permissions' => [Permission::where('slug', Permission::MANAGE_ADMINISTRATORS)->first()->id],
        ])->assertForbidden();
    }

    /* ---------- XSS ---------- */

    public function test_article_body_is_sanitized_on_save_and_render(): void
    {
        $article = $this->article('published', '<p>ok</p><script>alert(1)</script><img src=x onerror=alert(1)>');

        $this->assertStringNotContainsString('<script', $article->fresh()->body);
        $this->get('/news/'.$article->slug)->assertOk()->assertDontSee('onerror', false)->assertDontSee('<script>alert(1)', false);
    }

    public function test_legacy_unsanitized_rows_are_still_cleaned_when_rendered(): void
    {
        $article = $this->article();
        \DB::table('articles')->where('id', $article->id)->update(['body' => '<p>x</p><script>alert(9)</script>']);

        $this->get('/news/'.$article->slug)->assertOk()->assertDontSee('alert(9)', false);
    }

    /* ---------- Draft visibility ---------- */

    public function test_registered_reader_cannot_view_unpublished_article(): void
    {
        $draft = $this->article('draft');

        $this->get('/news/'.$draft->slug)->assertNotFound();
        $this->actingAs(User::factory()->create())->get('/news/'.$draft->slug)->assertNotFound();
        $this->actingAs($this->userWithRole(Role::EDITOR))->get('/news/'.$draft->slug)->assertOk();
    }

    /* ---------- Authentication ---------- */

    public function test_inactive_user_cannot_log_in(): void
    {
        $u = User::factory()->create(['status' => 'inactive']);

        $this->post('/login', ['email' => $u->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_public_login_is_rate_limited(): void
    {
        $u = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $u->email, 'password' => 'wrong']);
        }

        // 6th attempt is blocked even with the CORRECT password.
        $this->post('/login', ['email' => $u->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_login_is_rate_limited(): void
    {
        $u = $this->userWithRole(Role::EDITOR);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => $u->email, 'password' => 'wrong']);
        }

        $this->post('/admin/login', ['email' => $u->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_session_id_changes_after_login(): void
    {
        $u = User::factory()->create();
        $this->get('/login');
        $before = session()->getId();

        $this->post('/login', ['email' => $u->email, 'password' => 'password'])->assertRedirect();

        $this->assertNotSame($before, session()->getId());
    }

    public function test_forms_include_csrf_token(): void
    {
        $this->get('/login')->assertSee('name="_token"', false);
        $this->get('/admin/login')->assertSee('name="_token"', false);
    }

    /* ---------- Ads ---------- */

    public function test_ad_click_never_redirects_to_non_http_urls(): void
    {
        $ad = Advertisement::create([
            'name' => 'a', 'type' => 'html', 'html_code' => '<b>x</b>', 'position' => 'sidebar',
            'device_target' => 'all', 'is_active' => true, 'click_url' => 'javascript:alert(1)',
        ]);

        $res = $this->get("/ads/{$ad->id}/click");
        $res->assertRedirect();
        $this->assertStringStartsWith('http', $res->headers->get('Location'));
        $this->assertStringNotContainsString('javascript', $res->headers->get('Location'));
    }

    public function test_ad_url_validation_rejects_javascript_scheme(): void
    {
        $editor = $this->userWithRole(Role::ADMINISTRATOR);

        $this->actingAs($editor)->post('/admin/advertisements', [
            'name' => 'x', 'type' => 'html', 'html_code' => '<b>x</b>', 'position' => 'sidebar',
            'device_target' => 'all', 'click_url' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('click_url');
    }

    /* ---------- Headers / errors ---------- */

    public function test_security_headers_are_present(): void
    {
        $res = $this->get('/');
        $res->assertHeader('X-Content-Type-Options', 'nosniff');
        $res->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $res->assertHeader('Referrer-Policy');
        $res->assertHeader('Permissions-Policy');
        $this->assertStringContainsString("frame-ancestors 'self'", $res->headers->get('Content-Security-Policy'));
    }

    public function test_admin_pages_are_not_cacheable(): void
    {
        $this->get('/admin/login')->assertHeader('Cache-Control');
        $this->assertStringContainsString('no-store', $this->get('/admin/login')->headers->get('Cache-Control'));
    }

    /* ---------- Search ---------- */

    public function test_search_handles_hostile_input(): void
    {
        $this->get('/search?q='.urlencode("' OR 1=1 -- %_\\"))->assertOk();
        $this->get('/search?q=a&category_id[]=1&sort[]=x')->assertOk();
    }

    /* ---------- Validation ---------- */

    public function test_category_cannot_be_its_own_parent(): void
    {
        $admin = $this->userWithRole(Role::EDITOR);
        $cat = Category::create(['name' => 'Self']);

        $this->actingAs($admin)->put("/admin/categories/{$cat->id}", ['name' => 'Self', 'parent_id' => $cat->id])
            ->assertSessionHasErrors('parent_id');
    }
}
