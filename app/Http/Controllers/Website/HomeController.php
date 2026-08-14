<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\HomePageContent;
use App\Models\Slider;

class HomeController extends Controller
{
    private const GALLERY_LOAD_COUNT = 8;

    /**
     * index
     */
    public function index()
    {
        $content = HomePageContent::firstOrNew(['id' => 1]);

        $content->hero_slider = Slider::where('type', 1)->get();
        $content->info_slider = Slider::where('type', 2)->get();
        $content->gallery = Gallery::latest('id')->take(self::GALLERY_LOAD_COUNT)->get();
        $content->gallery_total = Gallery::count();

        return view('website.home.index', compact('content'));
    }

    public function loadMoreGallery()
    {
        $offset = max(0, (int) request('offset', 0));
        $images = Gallery::latest('id')
            ->skip($offset)
            ->take(self::GALLERY_LOAD_COUNT)
            ->get();

        return response()->json([
            'html' => view('website.home._partials.gallery-items', compact('images'))->render(),
            'loaded_count' => $images->count(),
            'has_more' => Gallery::count() > ($offset + $images->count()),
            'next_offset' => $offset + $images->count(),
        ]);
    }
}
