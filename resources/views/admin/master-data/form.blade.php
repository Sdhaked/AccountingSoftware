@extends('layouts.admin')

@section('head')
    <title>{{ isset($item) ? 'Edit' : 'Create' }} {{ $definition['singular'] }}</title>
    <meta name="description" content="{{ isset($item) ? 'Edit' : 'Create' }} {{ strtolower($definition['singular']) }}.">
    @include('admin._partials.head.g-links')
    @include('admin._partials.head.g-css-files')
    @include('admin._partials.head.g-js-files')
@endsection

@section('body')
    @include('admin._partials.preloader')
    @include('admin._partials.sidebar')
    @include('admin._partials.header')
    <section class="wrapper">
        <main class="dash-content">
            @include('admin._partials.breadcrumb')
            <h4 class="hd-lg">{{ isset($item) ? 'Edit' : 'Create' }} {{ $definition['singular'] }}</h4>

            <form action="{{ isset($item)
                ? route('admin.master-data.update', [$entity, $item->id])
                : route('admin.master-data.store', $entity) }}" method="POST" enctype="multipart/form-data" class="grid-1 gap-card">
                @csrf
                @isset($item) @method('PUT') @endisset
                <div class="grid-2 grid-sm-1 gap-card">
                    @foreach($definition['fields'] as $field)
                        @php
                            $isFile = ($field['type'] ?? null) === 'file';
                            $value = $isFile
                                ? data_get($item ?? null, $field['name'])
                                : old($field['name'], data_get($item ?? null, $field['name']));
                        @endphp
                        @if($isFile)
                            <div class="label-spc upload-box">
                                <div class="previewBox mt-2">
                                    <img src="{{ filled($value) ? asset('storage/'.$value) : asset('images/uploadimg.svg') }}"
                                         class="preview thumb-img x3" alt="{{ $field['label'] }} preview">
                                </div>
                                <div class="mt-4">
                                    <label for="field_{{ $field['name'] }}">{{ $field['label'] }}{{ ($field['required'] ?? false) ? '*' : '' }}</label>
                                    <input type="file" name="{{ $field['name'] }}" id="field_{{ $field['name'] }}"
                                           accept="{{ $field['accept'] ?? 'image/*' }}"
                                           class="form-control mt-1 @error($field['name']) is-invalid @enderror"
                                           @required($field['required'] ?? false)>
                                    @isset($field['help'])<small class="search-base">{{ $field['help'] }}</small>@endisset
                                    @if(filled($value))<small class="search-base d-block">Upload a new image to replace the current logo.</small>@endif
                                    @error($field['name'])<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        @else
                            <div class="form-floating">
                                @if($field['type'] === 'textarea')
                                    <textarea name="{{ $field['name'] }}" id="field_{{ $field['name'] }}" style="height:120px"
                                              class="form-control @error($field['name']) is-invalid @enderror"
                                              @required($field['required'] ?? false)>{{ $value }}</textarea>
                                @elseif($field['type'] === 'select')
                                    <select name="{{ $field['name'] }}" id="field_{{ $field['name'] }}"
                                            class="form-select @error($field['name']) is-invalid @enderror"
                                            @required($field['required'] ?? false)>
                                        <option value="">Select {{ $field['label'] }}</option>
                                        @if(isset($field['choices']))
                                            @foreach($field['choices'] as $choiceValue => $choiceLabel)
                                                <option value="{{ $choiceValue }}" @selected((string) $value === (string) $choiceValue)>{{ $choiceLabel }}</option>
                                            @endforeach
                                        @else
                                            @foreach($field['options'] as $option)
                                                <option value="{{ $option->id }}" @selected((int) $value === $option->id)>
                                                    {{ $option->name }}
                                                    @if(isset($field['option_suffix']))
                                                        ({{ rtrim(rtrim(number_format((float) data_get($option, $field['option_suffix']), 3), '0'), '.') }}%)
                                                    @endif
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                @else
                                    <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" id="field_{{ $field['name'] }}"
                                           value="{{ $value }}" step="{{ $field['step'] ?? '' }}" min="{{ $field['type'] === 'number' ? 0 : '' }}"
                                           @isset($field['maxlength']) maxlength="{{ $field['maxlength'] }}" @endisset
                                           @if($field['digits_only'] ?? false) inputmode="numeric" pattern="[0-9]*" data-digits-only="true" @endif
                                           class="form-control @error($field['name']) is-invalid @enderror"
                                           @required($field['required'] ?? false)>
                                @endif
                                <label for="field_{{ $field['name'] }}">{{ $field['label'] }}{{ ($field['required'] ?? false) ? '*' : '' }}</label>
                                @error($field['name'])<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endif
                    @endforeach
                </div>
                <div>
                    <button class="btn-md btn-sec" type="submit">Submit</button>
                    <a class="btn-md btn-sec-outline" href="{{ route('admin.master-data.index', $entity) }}">Cancel</a>
                </div>
            </form>
        </main>
    </section>

    <script>
        document.querySelectorAll('[data-digits-only="true"]').forEach(function (input) {
            input.addEventListener('input', function () {
                input.value = input.value.replace(/\D/g, '').slice(0, input.maxLength || undefined);
            });
        });
    </script>
@endsection
