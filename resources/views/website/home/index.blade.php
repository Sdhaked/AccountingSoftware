@extends('layouts.website')

@section('head')
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @if (empty($content?->meta_data))
        <title>Book My Tecket Seat</title>
    @else
        {!! json_decode($content->meta_data, true) !!}
    @endif

    @include('website._partials.head.head-files')

    <link rel="stylesheet" href="{{ asset('website/style/swiper-bundle.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('website/style/jquery.fancybox.css') }}" />
    <link rel="stylesheet" href="{{ asset('website/style/aos.css') }}" />
    @include('website._partials.head.g-css-files')
    <link rel="stylesheet" href="{{ asset('website/style/page-styling/home.css') }}" />

    <script src="https://code.jquery.com/jquery-3.6.1.min.js"
        integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous" defer></script>
    <script src="{{ asset('website/js/swiper-bundle.min.js') }}" defer></script>
    <script src="{{ asset('website/js/aos.js') }}" defer></script>
    <script src="{{ asset('website/js/custom.aos.js') }}" defer></script>
    <script src="{{ asset('website/js/jquery.fancybox.min.js') }}" defer></script>
    @include('website._partials.head.g-js-files')
    <script src="{{ asset('website/js/page-js/home.js') }}" defer></script>
    <script>
        window.HOME_GALLERY = {
            loadMoreUrl: "{{ route('website.home.gallery.load_more') }}",
            initialCount: {{ $content?->gallery->count() ?? 0 }},
            totalCount: {{ $content?->gallery_total ?? 0 }},
            perPage: 8
        };
    </script>
@endsection

@section('body')
    @php
        $hasHeroSlider = $content?->show_what === 'slider' && ($content?->hero_slider?->isNotEmpty() ?? false);
        $hasHeroVideo = $content?->show_what === 'video' && filled($content?->hero_video_path);
    @endphp

    @include('website._partials.preloader')
    @include('website._partials.nav')

    <main>
        @if ($hasHeroSlider || $hasHeroVideo)
            <section style="width:100%">
                @if ($hasHeroSlider)
                    <div class="swiper heroSwiper">
                        <div class="swiper-wrapper">
                            @foreach($content->hero_slider as $slider)
                                <div class="swiper-slide">
                                    <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->alt_text }}"
                                        loading="lazy" decoding="async" />
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-pagination"></div>
                    </div>
                @elseif($hasHeroVideo)
                    <video style="width:100%; height:auto" autoplay muted loop plays-inline>
                        <source src="{{ asset('storage/' . $content->hero_video_path) }}" type="video/mp4" preload="auto"
                            poster="{{ $content->hero_video_poster ? asset('storage/' . $content->hero_video_poster) : '' }}">
                    </video>
                @endif
            </section>
        @endif

        @if ($content?->about_heading_text_1 && $content?->about_processed_description && $content?->about_image_path)
            <section class="container-fluid spc-y bg-devider mini-about-sec">
                <div class="container">
                    <div class="grid-sec-40-60 gap-col">
                        <div>
                            <div class="img-holder">
                                <img src="{{ asset('storage/' . $content->about_image_path) }}"
                                    alt="{{ $content->about_image_alt }}" loading="lazy" decoding="async" />
                                <span></span>
                            </div>
                        </div>
                        <div>
                            <div class="mb-prim">
                                <{{ $content->about_heading_type_1 ?? 'h3' }} class="hd-prim">{{ $content->about_heading_text_1 }}
                                    </{{ $content->about_heading_type_1 ?? 'h3' }}>
                                    <{{ $content->about_heading_type_2 ?? 'h3' }} class="hd-big text-prim">
                                        {{ $content->about_heading_text_2 }}
                                        </{{ $content->about_heading_type_2 ?? 'h3' }}>
                                        {!! $content->about_processed_description !!}

                                        <a href="{{ route('website.about.index') }}" role="button"
                                            class="btn-sm btn-lite-outline hover-prim mt-spc">Know more
                                            <i class="fa-solid fa-arrow-right-long i-ml"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if ($content?->info_slider?->count() > 0)
            <section class="container-fluid spc-y">
                <div class="container">
                    <div class="swiper infoSlider">
                        <div class="swiper-wrapper">
                            @foreach ($content->info_slider as $slide)
                                @php
                                    $slideImage = asset('storage/' . $slide->image);
                                    $slideAltText = $slide->alt_text ?: 'Info slider image';
                                @endphp
                                <div class="swiper-slide">
                                    @if ($slide->url)
                                        <a href="{{ $slide->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $slideAltText }}">
                                            <img src="{{ $slideImage }}" alt="{{ $slideAltText }}" loading="lazy" decoding="async" />
                                        </a>
                                    @else
                                        <img src="{{ $slideImage }}" alt="{{ $slideAltText }}" loading="lazy" decoding="async" />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if ($content?->gallery->count() > 0)
            <section class="container-fluid spc-y">
                <div class="container">
                    <div class="mb-prim all-text-center">
                        <h3 class="hd-prim">Gallery</h3>
                    </div>
                    <div class="grid-archive-4 gap-card" id="homeGalleryGrid" data-aos="fade-up">
                        @include('website.home._partials.gallery-items', ['images' => $content->gallery])
                    </div>

                    @if (($content?->gallery_total ?? 0) > ($content?->gallery->count() ?? 0))
                        <div class="center-btn-box" id="homeGalleryLoadMoreWrap">
                            <button type="button" class="btn-md btn-prim-outline hover-prim" id="homeGalleryLoadMoreBtn">
                                Load More Images
                            </button>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    </main>

    @include('website._partials.Footer')
@endsection
