<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $search = '';

    public function render()
    {
        $results = collect();
        $categories = collect();
        $isFuzzy = false;

        $term = trim($this->search);

        if (mb_strlen($term) >= 2) {
            // 1. Direct substring match (fast, exact path) on name column only (indexed)
            $results = Product::where('name', 'like', '%' . $term . '%')
                ->take(5)
                ->get();

            $categories = Category::where('is_active', true)
                ->where('name', 'like', '%' . $term . '%')
                ->take(3)
                ->get();

            // 2. Typo-tolerant fallback when the direct match is thin/empty
            if ($results->count() < 5) {
                $fuzzyProducts = $this->fuzzyProducts($term, $results->pluck('id')->all());
                if ($fuzzyProducts->isNotEmpty()) {
                    // Mark as fuzzy only when the exact path found nothing on its own.
                    $isFuzzy = $results->isEmpty();
                    $results = $results->concat($fuzzyProducts)->take(5);
                }
            }

            if ($categories->isEmpty()) {
                $fuzzyCats = $this->fuzzyCategories($term);
                if ($fuzzyCats->isNotEmpty()) {
                    $categories = $fuzzyCats;
                    $isFuzzy = $results->isEmpty() ? true : $isFuzzy;
                }
            }
        }

        return view('livewire.global-search', [
            'results' => $results,
            'categoryResults' => $categories,
            'isFuzzy' => $isFuzzy,
        ]);
    }

    /**
     * Find products whose name is close to the search term (handles typos)
     * using Levenshtein edit distance. The catalog is small, so scanning it
     * in PHP is cheap and avoids needing DB-specific fuzzy functions.
     *
     * @param array<int> $excludeIds Product ids already returned by the exact search.
     */
    protected function fuzzyProducts(string $term, array $excludeIds = []): Collection
    {
        $limit = 5 - count($excludeIds);
        if ($limit <= 0) {
            return collect();
        }

        // Cache the candidate pool briefly so typing doesn't re-fetch the whole
        // catalog on every keystroke that triggers a fuzzy fallback.
        $candidatesData = \Illuminate\Support\Facades\Cache::remember('search.fuzzy.products', 3600, fn () => Product::select('id', 'name', 'image', 'price', 'rating_avg', 'rating_count')->get()->toArray());
        $candidates = collect($candidatesData)->map(fn ($p) => new Product($p));

        return $candidates
            ->when(!empty($excludeIds), fn ($c) => $c->whereNotIn('id', $excludeIds))
            ->map(function ($product) use ($term) {
                return ['product' => $product, 'score' => $this->fuzzyScore($term, $product->name)];
            })
            ->filter(fn ($row) => $row['score'] !== null)
            ->sortBy('score')
            ->take($limit)
            ->pluck('product')
            ->values();
    }

    protected function fuzzyCategories(string $term): Collection
    {
        $candidatesData = \Illuminate\Support\Facades\Cache::remember('search.fuzzy.categories', 3600, fn () => Category::where('is_active', true)->get()->toArray());
        $candidates = collect($candidatesData)->map(fn ($c) => new Category($c));

        return $candidates
            ->map(function ($category) use ($term) {
                return ['category' => $category, 'score' => $this->fuzzyScore($term, $category->name)];
            })
            ->filter(fn ($row) => $row['score'] !== null)
            ->sortBy('score')
            ->take(3)
            ->pluck('category')
            ->values();
    }

    /**
     * Lowest edit distance between the term and any word (or word-prefix) of
     * the candidate text. Returns null when nothing is within the tolerance,
     * which scales with the term length so short words aren't over-matched.
     */
    protected function fuzzyScore(string $term, string $candidate): ?int
    {
        $term = mb_strtolower(trim($term));
        $candidate = mb_strtolower(trim($candidate));

        if ($term === '' || $candidate === '') {
            return null;
        }

        $termLen = mb_strlen($term);
        $maxDistance = match (true) {
            $termLen <= 3 => 1,
            $termLen <= 5 => 2,
            default => 3,
        };

        // Compare against the whole name and each individual word.
        $words = preg_split('/\s+/', $candidate);
        $words[] = $candidate;

        $best = null;
        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }

            $distance = levenshtein($term, $word);

            // Catch typos at the start of a longer word (e.g. "risol" ~ "risoles").
            if (mb_strlen($word) > $termLen) {
                $distance = min($distance, levenshtein($term, mb_substr($word, 0, $termLen)));
            }

            if ($best === null || $distance < $best) {
                $best = $distance;
            }
        }

        return ($best !== null && $best <= $maxDistance) ? $best : null;
    }
}
