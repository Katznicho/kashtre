<?php

namespace App\Livewire;

use App\Models\Item;
use App\Imports\ItemsImport;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ItemManager extends Component
{
    use WithPagination, WithFileUploads;

    public $name;
    public $price;
    public $search = '';
    public $file;
    public $isEditing = false;
    public $editingItemId;
    public $showModal = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'file' => 'nullable|file|mimes:xlsx,csv|max:5120', // 5MB max
    ];

    public function render()
    {
        $items = Item::query()
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.item-manager', [
            'items' => $items
        ]);
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'price']);
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        Item::create([
            'name' => $this->name,
            'price' => $this->price,
        ]);

        $this->reset();
        $this->dispatch('item-saved');
    }

    public function edit(Item $item)
    {
        $this->isEditing = true;
        $this->editingItemId = $item->id;
        $this->name = $item->name;
        $this->price = $item->price;
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate();

        Item::find($this->editingItemId)->update([
            'name' => $this->name,
            'price' => $this->price,
        ]);

        $this->reset();
        $this->dispatch('item-updated');
    }

    public function delete(Item $item)
    {
        $item->delete();
        $this->dispatch('item-deleted');
    }

    public function uploadFile()
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:5120',
        ]);

        try {
            $import = new ItemsImport;
            Excel::import($import, $this->file);

            $this->reset('file');
            $this->dispatch('upload-success', [
                'message' => 'File uploaded successfully!',
                'count' => $import->getRowCount()
            ]);
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errors = collect($failures)->map(function ($failure) {
                return "Row {$failure->row()}: {$failure->errors()[0]}";
            })->toArray();
            
            $this->dispatch('upload-failed', [
                'message' => 'Validation failed',
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            $this->dispatch('upload-failed', [
                'message' => 'Upload failed',
                'errors' => [$e->getMessage()]
            ]);

        } catch (\Exception $e) {
            $this->dispatch('upload-failed', ['errors' => [$e->getMessage()]]);
        }
    }

    public function downloadTemplate($type = 'xlsx')
    {
        $file = $type === 'csv' ? 'items_template.csv' : 'items_template.xlsx';
        $path = public_path('templates/' . $file);
        if (!file_exists($path)) {
            abort(404, 'Template not found.');
        }
        return response()->download($path);
    }
}
