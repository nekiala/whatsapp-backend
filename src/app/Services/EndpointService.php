<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EndpointService
{
    private function fetchData(string|null $endpoint): string
    {
        $url = sprintf('https://fakestoreapi.com%s', is_null($endpoint) ? '/' : $endpoint);

        return Http::get($url)->body();
    }

    public function getProductById(string $product_id): string
    {
        return $this->fetchData('/products/' . $product_id);
    }

    public function getAllCategories(): string
    {
        return $this->fetchData('/products/categories?limit=100');
    }

    public function getProductsInCategory(string $category_id): string
    {
        return $this->fetchData('/products/category/' . $category_id . '?limit=10');
    }
}
