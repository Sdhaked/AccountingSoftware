<!doctype html>
<html><head><meta charset="utf-8"><title>Certificate</title>
<style>
    @page { margin: 24px; }
    body { font-family: DejaVu Sans, sans-serif; color: #172033; }
    .certificate { border: 10px solid #253d4e; padding: 54px; height: 470px; text-align: center; }
    h1 { font-size: 44px; margin: 20px 0 8px; letter-spacing: 2px; }
    h2 { font-size: 26px; margin: 25px 0; color: #6f2cf4; }
    p { font-size: 18px; line-height: 1.6; }
    .meta { margin-top: 55px; width: 100%; }
    .meta td { width: 33%; font-size: 14px; }
    .sponsor { max-width: 170px; max-height: 64px; margin-top: 28px; }
</style></head><body>
<div class="certificate">
    <div>Certificate ID: <strong>{{ $certificate->certificate_number }}</strong></div>
    <h1>CERTIFICATE</h1>
    <p>This certificate is issued to</p>
    <h2>{{ $certificate->customer->name }}</h2>
    <p>by <strong>{{ $certificate->company->name }}</strong></p>
    <table class="meta"><tr>
        <td>Issued<br><strong>{{ $certificate->issued_at->format('d M Y') }}</strong></td>
        <td></td>
        <td>Expires<br><strong>{{ $certificate->expires_at->format('d M Y') }}</strong></td>
    </tr></table>
    @if($sponsorImage)<img class="sponsor" src="{{ $sponsorImage }}" alt="Sponsor">@endif
</div>
</body></html>
