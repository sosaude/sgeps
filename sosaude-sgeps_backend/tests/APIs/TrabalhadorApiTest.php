<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Trabalhador;

class TrabalhadorApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_trabalhador()
    {
        $trabalhador = factory(Trabalhador::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/trabalhadors', $trabalhador
        );

        $this->assertApiResponse($trabalhador);
    }

    /**
     * @test
     */
    public function test_read_trabalhador()
    {
        $trabalhador = factory(Trabalhador::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/trabalhadors/'.$trabalhador->id
        );

        $this->assertApiResponse($trabalhador->toArray());
    }

    /**
     * @test
     */
    public function test_update_trabalhador()
    {
        $trabalhador = factory(Trabalhador::class)->create();
        $editedTrabalhador = factory(Trabalhador::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/trabalhadors/'.$trabalhador->id,
            $editedTrabalhador
        );

        $this->assertApiResponse($editedTrabalhador);
    }

    /**
     * @test
     */
    public function test_delete_trabalhador()
    {
        $trabalhador = factory(Trabalhador::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/trabalhadors/'.$trabalhador->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/trabalhadors/'.$trabalhador->id
        );

        $this->response->assertStatus(404);
    }
}
