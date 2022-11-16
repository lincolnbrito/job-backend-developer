<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class ProductBuilder extends Builder
{
    public function search(string $search): self
    {
        return $this->where('name', 'like', "%$search%")
            ->orWhere('category', 'like', "%$search%");
    }

    public function whereCategory(string $category): self
    {
        return $this->where('category', '=', $category);
    }

    public function hasImage(bool $hasImage): self 
    {
        if($hasImage) {
            return $this->whereNotNull('image_url');
        }

        return $this->whereNull('image_url');
    }
}