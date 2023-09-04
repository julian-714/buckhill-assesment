<?php

namespace Tests\Feature\Product;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Category;
use App\Models\File;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $userData = [
            'email' => 'admin@buckhill.co.uk',
            'password' => 'admin',
        ];

        $reqLogin = $this->json('POST', 'api/v1/admin/login', $userData)
            ->assertStatus(200);
        $jsonData = $reqLogin->json();
        $this->assertArrayHasKey('data', $jsonData);
        $this->assertNotEmpty($jsonData['data']);

        $token = $jsonData['data']['token'];
        $this->session(['token' => $token]);
    }

    /** Get all products list test case */
    public function testGetAllProducts()
    {
        $getProducts = $this->get('api/v1/products');

        $getProducts->assertStatus(200);

        $jsonData = $getProducts->json();
        $this->assertArrayHasKey('data', $jsonData);
        $this->assertNotEmpty($jsonData['data']);
    }

    /** Products create test case */
    public function testProductsCreate()
    {
        $token = $this->app['session']->get('token');
        $categoryUuid = Category::get()->random()->uuid;
        $imageUuid = File::get();
        $selectImageUuid = isset($imageUuid) && isset($imageUuid->uuid) ? $imageUuid->random()->uuid : Str::orderedUuid();
        $brandUuid = Brand::get()->random()->uuid;
        $productData = [
            'uuid' => Str::orderedUuid(),
            'category_uuid' => $categoryUuid,
            'title' => fake()->sentence(2),
            'price' => fake()->randomDigit,
            'description' => fake()->paragraph(1),
            'metadata' => json_encode(['brand' => $brandUuid, 'image' =>  $selectImageUuid])
        ];

        $createProduct = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->json('POST', 'api/v1/product/create', $productData);

        $createProduct->assertStatus(200);

        $jsonData = $createProduct->json();
        $this->assertArrayHasKey('data', $jsonData);
        $this->assertNotEmpty($jsonData['data']);
    }

    /** Get product test case */
    public function testGetProduct()
    {
        $uuid = Product::get()->random()->uuid;
        $getProduct = $this->json('GET', 'api/v1/product/' . $uuid);

        $getProduct->assertStatus(200);

        $jsonData = $getProduct->json();
        $this->assertArrayHasKey('data', $jsonData);
        $this->assertNotEmpty($jsonData['data']);
    }

    /** Products update test case */
    public function testProductsUpdate()
    {
        $token = $this->app['session']->get('token');
        $uuid = Product::get()->random()->uuid;
        $categoryUuid = Category::get()->random()->uuid;
        $imageUuid = File::get();
        $selectImageUuid = isset($imageUuid) && isset($imageUuid->uuid) ? $imageUuid->random()->uuid : Str::orderedUuid();
        $brandUuid = Brand::get()->random()->uuid;
        $productData = [
            'uuid' => Str::orderedUuid(),
            'category_uuid' => $categoryUuid,
            'title' => fake()->sentence(2),
            'price' => fake()->randomDigit,
            'description' => fake()->paragraph(1),
            'metadata' => json_encode(['brand' => $brandUuid, 'image' =>  $selectImageUuid])
        ];

        $updateProduct = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->json('PUT', 'api/v1/product/' . $uuid, $productData);

        $updateProduct->assertStatus(200);

        $jsonData = $updateProduct->json();
        $this->assertArrayHasKey('data', $jsonData);
        $this->assertNotEmpty($jsonData['data']);
    }

    /** Delete product test case */
    public function testDeleteProduct()
    {
        $token = $this->app['session']->get('token');
        $uuid = Product::get()->random()->uuid;

        $deleteProduct = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->json('DELETE', 'api/v1/product/' . $uuid);

        $deleteProduct->assertStatus(200);

        $jsonData = $deleteProduct->json();
        $this->assertArrayHasKey('message', $jsonData);
        $this->assertNotEmpty($jsonData['message']);
    }
}
