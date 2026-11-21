<?php

namespace App\Services;

use App\Models\WatotoVideo;
use DOMElement;
use DOMXPath;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Masterminds\HTML5;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class MobileLessonTopicMedia
{
    public function contentHtml(?string $content): ?string
    {
        $content = html_entity_decode($content ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (! preg_match('/<[a-z][a-z0-9]*(?=\s|>|\/)/i', $content)) {
            return null;
        }

        $config = (new HtmlSanitizerConfig)
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->allowLinkSchemes(['https'])
            ->allowMediaSchemes(['https'])
            ->withMaxInputLength(-1)
            ->allowElement('a', ['href', 'title'])
            ->allowElement('img', ['src', 'alt', 'title'])
            ->allowElement('audio', ['src'])
            ->allowElement('video', ['src'])
            ->allowElement('source', ['src'])
            ->allowElement('iframe', ['src', 'title']);

        foreach ([
            'p', 'br', 'div', 'span', 'section', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'b', 'strong', 'i', 'em', 'u', 's', 'del', 'sup', 'sub', 'hr',
            'ol', 'ul', 'li', 'blockquote', 'pre', 'code', 'figure', 'figcaption',
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
        ] as $element) {
            $config = $config->allowElement($element);
        }

        $sanitized = (new HtmlSanitizer($config))->sanitize($content);
        $parser = new HTML5(['disable_html_ns' => true]);
        $fragment = $parser->loadHTMLFragment($sanitized);
        $xpath = new DOMXPath($fragment->ownerDocument);

        foreach ($xpath->query('.//a', $fragment) as $link) {
            $url = $this->resolveUrl($link->getAttribute('href'));
            $link->removeAttribute('href');
            if ($url !== null) {
                $link->setAttribute('href', $url);
            }
        }

        foreach ($xpath->query('.//img | .//audio | .//video | .//iframe', $fragment) as $element) {
            $url = $this->mediaSource($element);
            if ($url === null) {
                $element->remove();
            } else {
                $element->setAttribute('src', $url);
            }
        }

        foreach ($xpath->query('.//source', $fragment) as $source) {
            $source->remove();
        }

        return $parser->saveHTML($fragment);
    }

    public function videoUrl(?string $value): ?string
    {
        return $this->httpsUrl(trim($value ?? ''));
    }

    public function publicFileUrl(?string $path): ?string
    {
        $path = trim($path ?? '');
        if ($path === '') {
            return null;
        }

        if (parse_url($path, PHP_URL_SCHEME) !== null || str_starts_with($path, '//')) {
            return $this->httpsUrl($path);
        }

        // The public disk stores uploaded media as paths. Its URL can use the
        // app's configured HTTP scheme even when the requests arrive through HTTPS.
        $url = Storage::disk('public')->url($path);

        return $this->resolveUrl(preg_replace('~^http://~i', 'https://', $url));
    }

    private function mediaSource(DOMElement $element): ?string
    {
        $url = $this->resolveUrl($element->getAttribute('src'));

        if ($element->tagName === 'iframe') {
            $id = WatotoVideo::youtubeVideoId($url);

            return $id === null ? null : 'https://www.youtube-nocookie.com/embed/'.$id;
        }

        if (in_array($element->tagName, ['audio', 'video'], true)) {
            foreach ($element->childNodes as $child) {
                if ($url === null && $child instanceof DOMElement && $child->tagName === 'source') {
                    $url = $this->resolveUrl($child->getAttribute('src'));
                }
            }
        }

        return $url;
    }

    private function resolveUrl(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || preg_match('/[\x00-\x1f\x7f\\\\]/', $value)) {
            return null;
        }

        try {
            $url = UriResolver::resolve(new Uri(secure_url('/')), new Uri($value));
        } catch (InvalidArgumentException) {
            return null;
        }

        return $this->httpsUrl((string) $url);
    }

    private function httpsUrl(string $value): ?string
    {
        if (! filter_var($value, FILTER_VALIDATE_URL) || str_contains($value, '\\')) {
            return null;
        }

        $parts = parse_url($value);

        return strtolower($parts['scheme'] ?? '') === 'https' && ! isset($parts['user']) && ! isset($parts['pass'])
            ? $value
            : null;
    }
}
