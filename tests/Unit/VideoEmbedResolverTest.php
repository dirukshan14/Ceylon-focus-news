<?php

namespace Tests\Unit;

use App\Services\VideoEmbedResolver;
use PHPUnit\Framework\TestCase;

class VideoEmbedResolverTest extends TestCase
{
    public function test_provider_detection_is_host_anchored(): void
    {
        $r = new VideoEmbedResolver();

        $this->assertSame('youtube', $r->resolve('https://youtu.be/dQw4w9WgXcQ')['provider']);
        $this->assertSame('instagram', $r->resolve('https://www.instagram.com/reel/Cabc123/')['provider']);
        $this->assertSame('tiktok', $r->resolve('https://www.tiktok.com/@a/video/1')['provider']);

        // A hostile host that merely *mentions* a trusted domain must not be treated as that provider.
        $this->assertSame('other', $r->resolve('https://evil.example/?x=instagram.com/p/abc')['provider']);
        $this->assertSame('other', $r->resolve('https://evil.example/?tiktok.com/')['provider']);
    }
}
