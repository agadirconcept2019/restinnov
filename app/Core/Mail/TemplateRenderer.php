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
            $safe = e((string) $value);
            $subject = str_replace('{'.$name.'}', strip_tags($safe), $subject);
            $body = str_replace('{'.$name.'}', $safe, $body);
        }

        return ['subject' => $subject, 'body_html' => $body];
    }

    public function preview(string $key, ?string $locale = null): array
    {
        return $this->render($key, [
            'property_title' => '<Sample Property>',
            'checkin' => '2031-01-10',
            'checkout' => '2031-01-12',
            'nights' => 2,
            'total' => '1200.00',
            'invoice_number' => 'INV-2031-000001',
            'dashboard_link' => url('/admin'),
        ], $locale);
    }
}
