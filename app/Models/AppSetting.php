<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppSetting extends Model
{
    protected $fillable = [
        'company_name',
        'base_country',
        'allow_super_admin_permanent_delete',
        'pdf_sponsor_image',
        'mail_host',
        'mail_port',
        'mail_scheme',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
        'mail_cc',
    ];

    protected $hidden = ['mail_password'];

    protected function casts(): array
    {
        return [
            'allow_super_admin_permanent_delete' => 'boolean',
            'mail_port' => 'integer',
            'mail_password' => 'encrypted',
        ];
    }

    public function applyMailConfiguration(): void
    {
        $values = array_filter([
            'mail.mailers.smtp.host' => $this->mail_host,
            'mail.mailers.smtp.port' => $this->mail_port,
            'mail.mailers.smtp.scheme' => $this->mail_scheme,
            'mail.mailers.smtp.username' => $this->mail_username,
            'mail.mailers.smtp.password' => $this->mail_password,
            'mail.from.address' => $this->mail_from_address,
            'mail.from.name' => $this->mail_from_name,
            'mail.cc.address' => $this->mail_cc,
        ], fn ($value) => filled($value));

        if ($values !== []) {
            config($values);
        }
    }

    public function sponsorImageDataUri(): ?string
    {
        if (! $this->pdf_sponsor_image || ! Storage::disk('public')->exists($this->pdf_sponsor_image)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($this->pdf_sponsor_image) ?: 'image/png';

        return 'data:'.$mime.';base64,'
            .base64_encode(Storage::disk('public')->get($this->pdf_sponsor_image));
    }
}
