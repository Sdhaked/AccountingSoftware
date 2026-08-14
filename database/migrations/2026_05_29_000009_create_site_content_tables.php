<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_page_content', function (Blueprint $table) {
            $table->id();
            $table->string('show_what')->nullable()->default('slider');
            $table->string('hero_video_path')->nullable();
            $table->string('hero_video_poster')->nullable();
            $table->string('about_image_path')->nullable();
            $table->string('about_image_alt')->nullable();
            $table->string('about_heading_type_1')->nullable();
            $table->string('about_heading_text_1')->nullable();
            $table->string('about_heading_type_2')->nullable();
            $table->string('about_heading_text_2')->nullable();
            $table->longText('about_description')->nullable();
            $table->longText('about_processed_description')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });

        Schema::create('about_page_content', function (Blueprint $table) {
            $table->id();
            $this->breadcrumbColumns($table);
            $table->string('about_featured_image_path')->nullable();
            $table->string('about_featured_image_alt')->nullable();
            $table->string('about_heading_type')->nullable();
            $table->string('about_heading_text')->nullable();
            $table->longText('about_description')->nullable();
            $table->longText('about_processed_description')->nullable();
            $table->string('owner_image_1_path')->nullable();
            $table->string('owner_image_1_alt')->nullable();
            $table->string('owner_image_2_path')->nullable();
            $table->string('owner_image_2_alt')->nullable();
            $table->string('owner_heading_1_type')->nullable();
            $table->string('owner_heading_1_text')->nullable();
            $table->string('owner_heading_2_type')->nullable();
            $table->string('owner_heading_2_text')->nullable();
            $table->longText('owner_description')->nullable();
            $table->longText('owner_processed_description')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_page_content', function (Blueprint $table) {
            $table->id();
            $this->breadcrumbColumns($table);
            $table->string('phone_prefix_1', 10)->nullable();
            $table->string('phone_number_1', 20)->nullable();
            $table->string('phone_prefix_2', 10)->nullable();
            $table->string('phone_number_2', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('map_link')->nullable();
            $table->longText('map_embed_link')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });

        Schema::create('event_archive_page_content', function (Blueprint $table) {
            $table->id();
            $this->breadcrumbColumns($table);
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });

        Schema::create('tickets_page_content', function (Blueprint $table) {
            $table->id();
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });

        Schema::create('terms_page_content', function (Blueprint $table) {
            $table->id();
            $this->breadcrumbColumns($table);
            $table->longText('main_content')->nullable();
            $table->longText('processed_main_content')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });

        Schema::create('policy_page_content', function (Blueprint $table) {
            $table->id();
            $this->breadcrumbColumns($table);
            $table->longText('main_content')->nullable();
            $table->longText('processed_main_content')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->unique();
            $table->text('url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_social_links');
        Schema::dropIfExists('policy_page_content');
        Schema::dropIfExists('terms_page_content');
        Schema::dropIfExists('tickets_page_content');
        Schema::dropIfExists('event_archive_page_content');
        Schema::dropIfExists('contact_page_content');
        Schema::dropIfExists('about_page_content');
        Schema::dropIfExists('home_page_content');
    }

    private function breadcrumbColumns(Blueprint $table): void
    {
        $table->string('breadcrumb_image_path')->nullable();
        $table->string('breadcrumb_image_alt')->nullable();
        $table->string('breadcrumb_heading_type')->nullable();
        $table->string('breadcrumb_heading_text')->nullable();
        $table->text('breadcrumb_description')->nullable();
    }
};
