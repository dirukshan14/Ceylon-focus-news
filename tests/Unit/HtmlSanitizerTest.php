<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_keeps_normal_formatting_and_unicode(): void
    {
        $html = '<p>வணக்கம் <strong>உலகம்</strong></p><ul><li>a</li></ul><a href="https://example.com">x</a>';
        $this->assertSame($html, HtmlSanitizer::clean($html));
    }

    #[DataProvider('attacks')]
    public function test_removes_script_vectors(string $payload, string $mustNotContain): void
    {
        $out = strtolower(HtmlSanitizer::clean($payload));
        $this->assertStringNotContainsString($mustNotContain, $out);
    }

    public static function attacks(): array
    {
        return [
            'script tag' => ['<script>alert(1)</script>', '<script'],
            'inline handler' => ['<p onclick="x()">a</p>', 'onclick'],
            'img onerror' => ['<img src=x onerror=alert(1)>', 'onerror'],
            'js url' => ['<a href="javascript:alert(1)">a</a>', 'javascript:'],
            'js url tab' => ['<a href="java&#x09;script:alert(1)">a</a>', 'script:'],
            'data url img' => ['<img src="data:text/html;base64,AAA">', 'data:'],
            'svg' => ['<svg onload=alert(1)></svg>', '<svg'],
            'foreign iframe' => ['<iframe src="https://evil.example"></iframe>', 'evil.example'],
            'style attr' => ['<div style="x:expression(alert(1))">a</div>', 'style='],
            'style tag' => ['<style>*{}</style>', '<style'],
            'form' => ['<form action="//evil"><input></form>', '<form'],
            'noscript mutation' => ['<noscript><p title="</noscript><img src=x onerror=alert(1)>">', 'onerror'],
        ];
    }

    public function test_allows_youtube_embed_only(): void
    {
        $this->assertStringContainsString('youtube.com/embed/abc', HtmlSanitizer::clean('<iframe src="https://www.youtube.com/embed/abc"></iframe>'));
        $this->assertSame('', HtmlSanitizer::clean('<iframe src="https://evil.example/x"></iframe>'));
    }

    public function test_external_links_get_noopener(): void
    {
        $this->assertStringContainsString('rel="noopener noreferrer"', HtmlSanitizer::clean('<a href="https://a.test" target="_blank">a</a>'));
    }
}
