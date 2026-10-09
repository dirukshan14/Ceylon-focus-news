<?php

namespace App\Services;

class VideoEmbedResolver
{
    /**
     * Given a raw URL an admin pasted in (YouTube watch link, Facebook video
     * link, Instagram post, TikTok video, Vimeo, or a direct .mp4), detect
     * the provider and return a safe, embeddable URL.
     *
     * @return array{provider: string, embed_url: ?string}
     */
    public function resolve(string $url): array
    {
        $url = trim($url);

        if ($id = $this->matchYoutube($url)) {
            return ['provider' => 'youtube', 'embed_url' => "https://www.youtube.com/embed/{$id}"];
        }

        if ($this->isFacebook($url)) {
            $encoded = urlencode($url);
            return [
                'provider' => 'facebook',
                'embed_url' => "https://www.facebook.com/plugins/video.php?href={$encoded}&show_text=false",
            ];
        }

        if ($this->isInstagram($url)) {
            $clean = rtrim(strtok($url, '?#'), '/');
            return ['provider' => 'instagram', 'embed_url' => "{$clean}/embed"];
        }

        if ($this->isTiktok($url)) {
            // TikTok requires their oEmbed endpoint for a true embed; store the
            // canonical URL and let the blade view load the TikTok embed.js
            // widget with this as the blockquote cite.
            return ['provider' => 'tiktok', 'embed_url' => $url];
        }

        if ($id = $this->matchVimeo($url)) {
            return ['provider' => 'vimeo', 'embed_url' => "https://player.vimeo.com/video/{$id}"];
        }

        if ($this->isDirectVideoFile($url)) {
            return ['provider' => 'mp4', 'embed_url' => $url];
        }

        // Fallback: store as-is, admin/theme can render a generic iframe.
        return ['provider' => 'other', 'embed_url' => $url];
    }

    private function matchYoutube(string $url): ?string
    {
        $patterns = [
            '~youtube\.com/watch\?v=([a-zA-Z0-9_-]{11})~',
            '~youtu\.be/([a-zA-Z0-9_-]{11})~',
            '~youtube\.com/shorts/([a-zA-Z0-9_-]{11})~',
            '~youtube\.com/embed/([a-zA-Z0-9_-]{11})~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    private function matchVimeo(string $url): ?string
    {
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    private function isFacebook(string $url): bool
    {
        return (bool) preg_match('~^https?://([a-z0-9-]+\.)?(facebook\.com|fb\.watch)/~i', $url);
    }

    private function isInstagram(string $url): bool
    {
        // Anchored to the real host so "https://evil.example/?x=instagram.com/p/" can't match.
        return (bool) preg_match('~^https?://(www\.)?instagram\.com/(p|reel|tv)/[A-Za-z0-9_-]+~i', $url);
    }

    private function isTiktok(string $url): bool
    {
        return (bool) preg_match('~^https?://([a-z0-9-]+\.)?tiktok\.com/~i', $url);
    }

    private function isDirectVideoFile(string $url): bool
    {
        return (bool) preg_match('~\.(mp4|webm|ogg)(\?.*)?$~i', $url);
    }
}
