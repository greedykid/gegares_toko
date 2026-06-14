<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $search = '';

    public function render()
    {
        $results = [];
        $categories = [];

        if (strlen($this->search) >= 2) {
            $results = Product::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%')
                ->take(5)
                ->get();

            $categories = \App\Models\Category::where('name', 'like', '%' . $this->search . '%')
                ->where('is_active', true)
                ->take(3)
                ->get();
        }

        return view('livewire.global-search', [
            'results' => $results,
            'categoryResults' => $categories,
        ]);
    }
}
