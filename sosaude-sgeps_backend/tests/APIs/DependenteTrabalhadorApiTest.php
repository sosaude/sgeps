<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\DependenteTrabalhador;

class DependenteTrabalhadorApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_dependente_trabalhador()
    {
        $dependenteTrabalhador = factory(DependenteTrabalhador::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/dependente_trabalhadors', $dependenteTrabalhador
        );

        $this->assertApiResponse($dependenteTrabalhador);
    }

    /**
     * @test
     */
    public function test_read_dependente_trabalhador()
    {
        $dependenteTrabalhador = factory(DependenteTrabalhador::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/dependente_trabalhadors/'.$dependenteTrabalhador->id
        );

        $this->assertApiResponse($dependenteTrabalhador->toArray());
    }

    /**
     * @test
     */
    public function test_update_dependente_trabalhador()
    {
        $dependenteTrabalhador = factory(DependenteTrabalhador::class)->create();
        $editedDependenteTrabalhador = factory(DependenteTrabalhador::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/dependente_trabalhadors/'.$dependenteTrabalhador->id,
            $editedDependenteTrabalhador
        );

        $this->assertApiResponse($editedDependenteTrabalhador);
    }

    /**
     * @test
     */
    public function test_delete_dependente_trabalhador()
    {
        $dependenteTrabalhador = factory(DependenteTrabalhador::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/dependente_trabalhadors/'.$dependenteTrabalhador->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/dependente_trabalhadors/'.$dependenteTrabalhador->id
        );

        $this->response->assertStatus(404);
    }
}
