<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\CategoriaEmpresa;

class CategoriaEmpresaApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_categoria_empresa()
    {
        $categoriaEmpresa = factory(CategoriaEmpresa::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/categoria_empresas', $categoriaEmpresa
        );

        $this->assertApiResponse($categoriaEmpresa);
    }

    /**
     * @test
     */
    public function test_read_categoria_empresa()
    {
        $categoriaEmpresa = factory(CategoriaEmpresa::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/categoria_empresas/'.$categoriaEmpresa->id
        );

        $this->assertApiResponse($categoriaEmpresa->toArray());
    }

    /**
     * @test
     */
    public function test_update_categoria_empresa()
    {
        $categoriaEmpresa = factory(CategoriaEmpresa::class)->create();
        $editedCategoriaEmpresa = factory(CategoriaEmpresa::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/categoria_empresas/'.$categoriaEmpresa->id,
            $editedCategoriaEmpresa
        );

        $this->assertApiResponse($editedCategoriaEmpresa);
    }

    /**
     * @test
     */
    public function test_delete_categoria_empresa()
    {
        $categoriaEmpresa = factory(CategoriaEmpresa::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/categoria_empresas/'.$categoriaEmpresa->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/categoria_empresas/'.$categoriaEmpresa->id
        );

        $this->response->assertStatus(404);
    }
}
