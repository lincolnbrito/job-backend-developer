<?php

namespace App\Services\ExternalStoreApi;

class Product {
    public readonly int $id;
    public readonly string $title;
    public readonly string $description;
    public readonly float $price;
    public readonly string $category;
    public readonly string $image_url;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->price = $data['price'];
        $this->category = $data['category'];
        $this->image_url = $data['image'];
    }
}