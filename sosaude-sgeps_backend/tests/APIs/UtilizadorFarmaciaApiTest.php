<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\UtilizadorFarmacia;

class UtilizadorFarmaciaApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_utilizador_farmacia()
    {
        $utilizadorFarmacia = factory(UtilizadorFarmacia::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/utilizador_farmacias', $utilizadorFarmacia
        );

        $this->assertApiResponse($utilizadorFarmacia);
    }

    /**
     * @test
     */
    public function test_read_utilizador_farmacia()
    {
        $utilizadorFarmacia = factory(UtilizadorFarmacia::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/utilizador_farmacias/'.$utilizadorFarmacia->id
        );

        $this->assertApiResponse($utilizadorFarmacia->toArray());
    }

    /**
     * @test
     */
    public function test_update_utilizador_farmacia()
    {
        $utilizadorFarmacia = factory(UtilizadorFarmacia::class)->create();
        $editedUtilizadorFarmacia = factory(UtilizadorFarmacia::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/utilizador_farmacias/'.$utilizadorFarmacia->id,
            $editedUtilizadorFarmacia
        );

        $this->assertApiResponse($editedUtilizadorFarmacia);
    }

    /**
     * @test
     */
    public function test_delete_utilizador_farmacia()
    {
        $utilizadorFarmacia = factory(UtilizadorFarmacia::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/utilizador_farmacias/'.$utilizadorFarmacia->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/utilizador_farmacias/'.$utilizadorFarmacia->id
        );

        $this->response->assertStatus(404);
    }
}
