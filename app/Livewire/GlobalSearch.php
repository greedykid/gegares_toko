<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
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
            $cacheKey = 'global_search:' . md5(mb_strtolower($term));
            $cached = Cache::remember($cacheKey, now()->addMinutes(2), function () use ($term) {
                $results = Product::where(function ($q) use ($term) {
                        $q->where('name', 'like', '%' . $term . '%')
                          ->orWhere('description', 'like', '%' . $term . '%');
                    })
                    ->take(5)
                    ->get();

                $categories = Category::where('is_active', true)
                    ->where('name', 'like', '%' . $term . '%')
                    ->take(3)
                    ->get();

                $isFuzzy = false;

                if ($results->count() < 5) {
                    $fuzzyProducts = $this->fuzzyProducts($term, $results->pluck('id')->all());
                    if ($fuzzyProducts->isNotEmpty()) {
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

                return [
                    'results' => $results,
                    'categories' => $categories,
                    'isFuzzy' => $isFuzzy,
                ];
            });

            $results = $cached['results'];
            $categories = $cached['categories'];
            $isFuzzy = $cached['isFuzzy'];
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

        $candidateLimit = max(25, $limit * 20);

        return Product::query()
            ->select(['id', 'name', 'slug', 'price', 'stock', 'image'])
            ->when(!empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->take($candidateLimit)
            ->get()
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
        return Category::where('is_active', true)
            ->select(['id', 'name', 'slug', 'image', 'is_active'])
            ->get()
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

        $words = preg_split('/\s+/', $candidate);
        $words[] = $candidate;

        $best = null;
        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }

            $distance = levenshtein($term, $word);

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
