<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\UtilizadorEmpresa;

class UtilizadorEmpresaApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_utilizador_empresa()
    {
        $utilizadorEmpresa = factory(UtilizadorEmpresa::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/utilizador_empresas', $utilizadorEmpresa
        );

        $this->assertApiResponse($utilizadorEmpresa);
    }

    /**
     * @test
     */
    public function test_read_utilizador_empresa()
    {
        $utilizadorEmpresa = factory(UtilizadorEmpresa::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/utilizador_empresas/'.$utilizadorEmpresa->id
        );

        $this->assertApiResponse($utilizadorEmpresa->toArray());
    }

    /**
     * @test
     */
    public function test_update_utilizador_empresa()
    {
        $utilizadorEmpresa = factory(UtilizadorEmpresa::class)->create();
        $editedUtilizadorEmpresa = factory(UtilizadorEmpresa::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/utilizador_empresas/'.$utilizadorEmpresa->id,
            $editedUtilizadorEmpresa
        );

        $this->assertApiResponse($editedUtilizadorEmpresa);
    }

    /**
     * @test
     */
    public function test_delete_utilizador_empresa()
    {
        $utilizadorEmpresa = factory(UtilizadorEmpresa::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/utilizador_empresas/'.$utilizadorEmpresa->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/utilizador_empresas/'.$utilizadorEmpresa->id
        );

        $this->response->assertStatus(404);
    }
}
