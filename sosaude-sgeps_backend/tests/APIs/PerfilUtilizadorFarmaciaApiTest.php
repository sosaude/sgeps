<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\PerfilUtilizadorFarmacia;

class PerfilUtilizadorFarmaciaApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_perfil_utilizador_farmacia()
    {
        $perfilUtilizadorFarmacia = factory(PerfilUtilizadorFarmacia::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/perfil_utilizador_farmacias', $perfilUtilizadorFarmacia
        );

        $this->assertApiResponse($perfilUtilizadorFarmacia);
    }

    /**
     * @test
     */
    public function test_read_perfil_utilizador_farmacia()
    {
        $perfilUtilizadorFarmacia = factory(PerfilUtilizadorFarmacia::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/perfil_utilizador_farmacias/'.$perfilUtilizadorFarmacia->id
        );

        $this->assertApiResponse($perfilUtilizadorFarmacia->toArray());
    }

    /**
     * @test
     */
    public function test_update_perfil_utilizador_farmacia()
    {
        $perfilUtilizadorFarmacia = factory(PerfilUtilizadorFarmacia::class)->create();
        $editedPerfilUtilizadorFarmacia = factory(PerfilUtilizadorFarmacia::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/perfil_utilizador_farmacias/'.$perfilUtilizadorFarmacia->id,
            $editedPerfilUtilizadorFarmacia
        );

        $this->assertApiResponse($editedPerfilUtilizadorFarmacia);
    }

    /**
     * @test
     */
    public function test_delete_perfil_utilizador_farmacia()
    {
        $perfilUtilizadorFarmacia = factory(PerfilUtilizadorFarmacia::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/perfil_utilizador_farmacias/'.$perfilUtilizadorFarmacia->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/perfil_utilizador_farmacias/'.$perfilUtilizadorFarmacia->id
        );

        $this->response->assertStatus(404);
    }
}
