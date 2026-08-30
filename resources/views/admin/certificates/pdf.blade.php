@php
    $asset = static function (string $filename): string {
        $path = public_path('images/pdf/santrains/'.$filename);

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
    };
    $logo = ($brandLogo ?? null) ?: ($sponsorImage ?? null) ?: $asset('logo.png');
    $fontData = 'data:font/truetype;base64,'.base64_encode((string) file_get_contents(
        public_path('fonts/santrains/GreatVibes-Regular.ttf')
    ));
    $recipientClass = strlen($certificate->customer->name) > 28 ? 'recipient recipient-small' : 'recipient';
    $courseClass = strlen($certificate->course_name) > 46 ? 'course-name course-name-small' : 'course-name';
    $catalog = implode(' | ', config('santrains.course_catalog'));
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate {{ $certificate->certificate_number }}</title>
    <style>
        @font-face { font-family: 'Great Vibes'; font-style: normal; font-weight: 400; src: url('{{ $fontData }}') format('truetype'); }
        @page { size: A4 portrait; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; width: 595pt; height: 842pt; }
        body { position: relative; overflow: hidden; font-family: DejaVu Sans, sans-serif; color: #050505; }
        .art { position: absolute; }
        .frame { z-index: 0; left: 37.4pt; top: 44.4pt; width: 520.54pt; height: 753.12pt; }
        .ribbon-top { z-index: 5; left: 220.74pt; top: -2.04pt; width: 373.98pt; height: 226.86pt; }
        .ribbon-bottom { z-index: 5; left: 0; top: 619.26pt; width: 361.74pt; height: 222.72pt; }
        .brand-logo { z-index: 1; left: 138.9pt; top: 67.8pt; width: 295.86pt; height: 170.34pt; }
        .content { position: absolute; z-index: 2; text-align: center; }
        .certificate-title { left: 72pt; top: 229pt; width: 451pt; color: #ef2027; font-size: 23pt; font-weight: bold; line-height: 1.1; }
        .certify { left: 110pt; top: 282pt; width: 375pt; font-size: 14pt; font-weight: bold; }
        .recipient { left: 93pt; top: 292.5pt; width: 445pt; color: #ef2027; font-family: 'Great Vibes', DejaVu Serif, serif; font-size: 33pt; line-height: 1.2; transform: scaleX(1.24); }
        .recipient-small { top: 311pt; font-size: 26pt; }
        .completed { left: 80pt; top: 368pt; width: 435pt; font-size: 14pt; }
        .course-name { left: 72pt; top: 387.4pt; width: 451pt; font-size: 16pt; font-weight: bold; line-height: 1.25; }
        .course-name-small { top: 388pt; font-size: 13pt; }
        .training-copy { left: 66pt; top: 421pt; width: 463pt; font-size: 12pt; line-height: 1.55; }
        .training-copy .standard { color: #0756e8; text-decoration: underline; }
        .verify { left: 68pt; top: 481.3pt; width: 459pt; font-size: 13.5pt; font-weight: bold; line-height: 1.64; }
        .verify-email { display: block; }
        .meta { position: absolute; z-index: 2; left: 84pt; top: 544.5pt; width: 220pt; font-size: 13.7pt; line-height: 1.78; }
        .instructor-label { left: 365pt; top: 525.7pt; width: 155pt; font-size: 15pt; font-weight: bold; }
        .instructor-name { left: 351.4pt; top: 551pt; width: 175pt; color: #ef2027; font-family: 'Great Vibes', DejaVu Serif, serif; font-size: 21pt; line-height: 1.2; transform: scaleX(1.24); }
        .signature { z-index: 2; left: 384.48pt; top: 567.36pt; width: 139.5pt; height: 75.12pt; }
        .catalog { left: 69pt; top: 636pt; width: 457pt; font-size: 12.2pt; line-height: 1.85; }
        .website { left: 160pt; top: 726pt; width: 275pt; font-size: 14pt; font-weight: bold; }
    </style>
</head>
<body>
    <img class="art frame" src="{{ $asset('certificate-frame.png') }}" alt="">
    <img class="art ribbon-top" src="{{ $asset('certificate-ribbon-top.png') }}" alt="">
    <img class="art ribbon-bottom" src="{{ $asset('certificate-ribbon-bottom.png') }}" alt="">
    <img class="art brand-logo" src="{{ $logo }}" alt="San Trains">

    <div class="content certificate-title">CERTIFICATE OF ATTENDANCE</div>
    <div class="content certify">THIS IS TO CERTIFY THAT</div>
    <div class="content {{ $recipientClass }}">{{ $certificate->customer->name }}</div>
    <div class="content completed">Has successfully completed the following course</div>
    <div class="content {{ $courseClass }}">{{ $certificate->course_name }}</div>
    <div class="content training-copy">
        The Training covered both theoretical part under
        <span class="standard">{{ config('santrains.training_standard_url') }}</span> and the<br>
        practical components following the Health &amp; Safety Authority Guidelines.
    </div>
    <div class="content verify">
        To Verify this certificate, kindly reach out to us via email at
        <span class="verify-email">{{ config('santrains.email') }}</span>
    </div>

    <div class="meta">
        <div>Issue Date:- {{ $certificate->issued_at->format('d-m-Y') }}</div>
        <div>Expiry Date:-{{ $certificate->expires_at->format('d-m-Y') }}</div>
        <div>Certificate No:-{{ $certificate->certificate_number }}</div>
    </div>
    <div class="content instructor-label">INSTRUCTOR</div>
    <div class="content instructor-name">{{ $certificate->instructor_name }}</div>
    @if(strcasecmp($certificate->instructor_name, config('santrains.instructor_name')) === 0)
        <img class="art signature" src="{{ $asset('signature.png') }}" alt="Instructor signature">
    @endif

    <div class="content catalog">{{ $catalog }}</div>
    <div class="content website">{{ config('santrains.website') }}</div>
</body>
</html>
