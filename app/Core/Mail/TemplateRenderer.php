<?php

namespace App\Core\Mail;

use App\Models\Core\EmailTemplate;

class TemplateRenderer
{
    public function render(string $key, array $variables = [], ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        $template = EmailTemplate::query()
            ->where('key', $key)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first()
            ?? EmailTemplate::query()->where('key', $key)->where('locale', config('locales.default', 'en'))->where('is_active', true)->first();

        $subject = $template?->subject ?? ucfirst(str_replace('.', ' ', $key));
        $body = $template?->body_html ?? '<p>No template configured.</p>';

        foreach ($variables as $name => $value) {
            $subject = str_replace('{'.$name.'}', (string) $value, $subject);
            $body = str_replace('{'.$name.'}', (string) $value, $body);
        }

        return ['subject' => $subject, 'body_html' => $body];
    }
}
