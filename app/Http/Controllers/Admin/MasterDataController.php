<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Label;
use App\Models\Product;
use App\Models\Service;
use App\Models\TaxClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    public function index(Request $request, string $entity)
    {
        $definition = $this->definition($entity);
        $query = $definition['model']::query()->with($definition['with']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($subQuery) use ($definition, $search) {
                foreach ($definition['search'] as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $subQuery->{$method}($column, 'like', "%{$search}%");
                }
            });
        }

        $records = $query->latest('id')->paginate(config('constants.pagination.per_page', 10));

        return view('admin.master-data.index', compact('definition', 'entity', 'records'));
    }

    public function create(string $entity)
    {
        $definition = $this->definition($entity);
        $this->loadOptions($definition);

        return view('admin.master-data.form', compact('definition', 'entity'));
    }

    public function store(Request $request, string $entity)
    {
        $definition = $this->definition($entity);
        $validated = Validator::make($request->all(), $this->rules($entity, $request), $this->messages())->validate();
        $storedFiles = [];

        try {
            $validated = $this->storeUploadedFiles($definition, $validated, $request, $storedFiles);
            $definition['model']::create($validated);
        } catch (\Throwable $exception) {
            $this->deleteStoredFiles($storedFiles);

            throw $exception;
        }

        return redirect()->route('admin.master-data.index', $entity)
            ->with('success', "{$definition['singular']} created successfully.");
    }

    public function show(string $entity, int $record)
    {
        $definition = $this->definition($entity);
        $item = $definition['model']::with($definition['with'])->findOrFail($record);

        return view('admin.master-data.show', compact('definition', 'entity', 'item'));
    }

    public function edit(string $entity, int $record)
    {
        $definition = $this->definition($entity);
        $item = $definition['model']::findOrFail($record);
        $this->loadOptions($definition);

        return view('admin.master-data.form', compact('definition', 'entity', 'item'));
    }

    public function update(Request $request, string $entity, int $record)
    {
        $definition = $this->definition($entity);
        $item = $definition['model']::findOrFail($record);
        $validated = Validator::make($request->all(), $this->rules($entity, $request, $record), $this->messages())->validate();
        $storedFiles = [];
        $oldFiles = [];

        try {
            $validated = $this->storeUploadedFiles($definition, $validated, $request, $storedFiles, $item, $oldFiles);
            $item->update($validated);
        } catch (\Throwable $exception) {
            $this->deleteStoredFiles($storedFiles);

            throw $exception;
        }

        $this->deleteStoredFiles($oldFiles);

        return redirect()->route('admin.master-data.index', $entity)
            ->with('success', "{$definition['singular']} updated successfully.");
    }

    public function destroy(string $entity, int $record)
    {
        $definition = $this->definition($entity);
        $item = $definition['model']::findOrFail($record);
        $storedFiles = $this->storedUploadFiles($definition, $item);

        try {
            $item->delete();
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23503'], true)) {
                return back()->with('error', "This {$definition['singular']} is in use and cannot be deleted.");
            }

            throw $exception;
        }

        $this->deleteStoredFiles($storedFiles);

        return back()->with('success', "{$definition['singular']} deleted successfully.");
    }

    private function definition(string $entity): array
    {
        $definitions = [
            'companies' => [
                'title' => 'Company List', 'singular' => 'Company', 'model' => Company::class,
                'with' => [], 'search' => ['name', 'email', 'phone'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Company Name', 'type' => 'text', 'required' => true],
                    ['name' => 'address', 'label' => 'Address', 'type' => 'textarea', 'required' => true],
                    ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'tel', 'digits_only' => true, 'maxlength' => 15],
                    ['name' => 'email', 'label' => 'Email Address', 'type' => 'email'],
                    ['name' => 'logo_path', 'label' => 'Company Logo', 'type' => 'file', 'format' => 'image',
                        'accept' => 'image/jpeg,image/png,image/webp', 'directory' => 'company-logos',
                        'help' => 'JPG, PNG or WebP, maximum 4 MB.', 'hide_on_index' => true],
                ],
            ],
            'customers' => [
                'title' => 'Customer List', 'singular' => 'Customer', 'model' => Customer::class,
                'with' => ['company'], 'search' => ['name', 'email', 'phone'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Customer Name', 'type' => 'text', 'required' => true],
                    ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'tel', 'digits_only' => true, 'maxlength' => 15],
                    ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true],
                    ['name' => 'billing_address', 'label' => 'Billing Address', 'type' => 'textarea', 'required' => true],
                    ['name' => 'company_id', 'label' => 'Company', 'type' => 'select', 'required' => true,
                        'relation' => 'company', 'option_model' => Company::class],
                ],
            ],
            'services' => [
                'title' => 'Service List', 'singular' => 'Service', 'model' => Service::class,
                'with' => ['company'], 'search' => ['name'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Service Name', 'type' => 'text', 'required' => true],
                    ['name' => 'default_rate', 'label' => 'Default Rate', 'type' => 'number', 'step' => '0.01',
                        'required' => true, 'format' => 'money'],
                    ['name' => 'company_id', 'label' => 'Company', 'type' => 'select', 'required' => true,
                        'relation' => 'company', 'option_model' => Company::class],
                ],
            ],
            'products' => [
                'title' => 'Product List', 'singular' => 'Product', 'model' => Product::class,
                'with' => ['company', 'taxClass'], 'search' => ['name'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Product Name', 'type' => 'text', 'required' => true],
                    ['name' => 'price', 'label' => 'Price', 'type' => 'number', 'step' => '0.01',
                        'required' => true, 'format' => 'money'],
                    ['name' => 'tax_class_id', 'label' => 'Tax Class', 'type' => 'select', 'required' => true,
                        'relation' => 'taxClass', 'option_model' => TaxClass::class, 'option_suffix' => 'percentage'],
                    ['name' => 'company_id', 'label' => 'Company', 'type' => 'select', 'required' => true,
                        'relation' => 'company', 'option_model' => Company::class],
                ],
            ],
            'tax-classes' => [
                'title' => 'Tax Classes List', 'singular' => 'Tax Class', 'model' => TaxClass::class,
                'with' => [], 'search' => ['name'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Class Name', 'type' => 'text', 'required' => true],
                    ['name' => 'percentage', 'label' => 'Tax Percentage', 'type' => 'number', 'step' => '0.001',
                        'required' => true, 'format' => 'percentage'],
                ],
            ],
            'labels' => [
                'title' => 'Label Master', 'singular' => 'Label', 'model' => Label::class,
                'with' => [], 'search' => ['name'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Label Name', 'type' => 'text', 'required' => true],
                ],
            ],
        ];

        abort_unless(isset($definitions[$entity]), 404);

        return $definitions[$entity];
    }

    private function loadOptions(array &$definition): void
    {
        foreach ($definition['fields'] as &$field) {
            if (isset($field['option_model'])) {
                $field['options'] = $field['option_model']::query()->orderBy('name')->get();
            }
        }
    }

    private function rules(string $entity, Request $request, ?int $record = null): array
    {
        $uniqueName = fn (string $table) => Rule::unique($table, 'name')->ignore($record);

        $phoneRules = ['nullable', 'digits_between:1,15'];

        return match ($entity) {
            'companies' => [
                'name' => ['required', 'string', 'max:255', $uniqueName('companies')],
                'address' => ['required', 'string', 'max:5000'],
                'phone' => $phoneRules,
                'email' => ['nullable', 'email', 'max:255'],
                'logo_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            ],
            'customers' => [
                'name' => ['required', 'string', 'max:255'],
                'phone' => $phoneRules,
                'email' => ['required', 'email', 'max:255'],
                'billing_address' => ['required', 'string', 'max:5000'],
                'company_id' => ['required', 'integer', 'exists:companies,id'],
            ],
            'services' => [
                'name' => ['required', 'string', 'max:255', Rule::unique('services', 'name')
                    ->where(fn ($query) => $query->where('company_id', $request->input('company_id')))->ignore($record)],
                'default_rate' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
                'company_id' => ['required', 'integer', 'exists:companies,id'],
            ],
            'products' => [
                'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')
                    ->where(fn ($query) => $query->where('company_id', $request->input('company_id')))->ignore($record)],
                'price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
                'tax_class_id' => ['required', 'integer', 'exists:tax_classes,id'],
                'company_id' => ['required', 'integer', 'exists:companies,id'],
            ],
            'tax-classes' => [
                'name' => ['required', 'string', 'max:255', $uniqueName('tax_classes')],
                'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            ],
            'labels' => [
                'name' => ['required', 'string', 'max:255', $uniqueName('labels')],
            ],
            default => abort(404),
        };
    }

    private function messages(): array
    {
        return [
            'phone.digits_between' => 'Phone number must contain only digits and be 1 to 15 digits long.',
        ];
    }

    private function storeUploadedFiles(
        array $definition,
        array $validated,
        Request $request,
        array &$storedFiles,
        ?Model $item = null,
        array &$oldFiles = []
    ): array {
        foreach ($this->fileFields($definition) as $field) {
            $name = $field['name'];

            if (! $request->hasFile($name)) {
                unset($validated[$name]);
                continue;
            }

            $disk = $field['disk'] ?? 'public';
            $path = $request->file($name)->store($field['directory'] ?? $name, $disk);
            $validated[$name] = $path;
            $storedFiles[] = [$disk, $path];

            $oldPath = $item?->getAttribute($name);
            if ($oldPath && $oldPath !== $path) {
                $oldFiles[] = [$disk, $oldPath];
            }
        }

        return $validated;
    }

    private function storedUploadFiles(array $definition, Model $item): array
    {
        $files = [];

        foreach ($this->fileFields($definition) as $field) {
            $path = $item->getAttribute($field['name']);
            if ($path) {
                $files[] = [$field['disk'] ?? 'public', $path];
            }
        }

        return $files;
    }

    private function deleteStoredFiles(array $files): void
    {
        foreach ($files as [$disk, $path]) {
            Storage::disk($disk)->delete($path);
        }
    }

    private function fileFields(array $definition): array
    {
        return array_values(array_filter(
            $definition['fields'],
            fn (array $field) => ($field['type'] ?? null) === 'file'
        ));
    }
}
