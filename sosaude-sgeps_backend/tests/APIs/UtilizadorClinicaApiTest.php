<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\UtilizadorClinica;

class UtilizadorClinicaApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_utilizador_clinica()
    {
        $utilizadorClinica = factory(UtilizadorClinica::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/utilizador_clinicas', $utilizadorClinica
        );

        $this->assertApiResponse($utilizadorClinica);
    }

    /**
     * @test
     */
    public function test_read_utilizador_clinica()
    {
        $utilizadorClinica = factory(UtilizadorClinica::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/utilizador_clinicas/'.$utilizadorClinica->id
        );

        $this->assertApiResponse($utilizadorClinica->toArray());
    }

    /**
     * @test
     */
    public function test_update_utilizador_clinica()
    {
        $utilizadorClinica = factory(UtilizadorClinica::class)->create();
        $editedUtilizadorClinica = factory(UtilizadorClinica::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/utilizador_clinicas/'.$utilizadorClinica->id,
            $editedUtilizadorClinica
        );

        $this->assertApiResponse($editedUtilizadorClinica);
    }

    /**
     * @test
     */
    public function test_delete_utilizador_clinica()
    {
        $utilizadorClinica = factory(UtilizadorClinica::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/utilizador_clinicas/'.$utilizadorClinica->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/utilizador_clinicas/'.$utilizadorClinica->id
        );

        $this->response->assertStatus(404);
    }
}
