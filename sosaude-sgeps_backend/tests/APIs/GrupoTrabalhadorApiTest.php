<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\GrupoTrabalhador;

class GrupoTrabalhadorApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_grupo_trabalhador()
    {
        $grupoTrabalhador = factory(GrupoTrabalhador::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/grupo_trabalhadors', $grupoTrabalhador
        );

        $this->assertApiResponse($grupoTrabalhador);
    }

    /**
     * @test
     */
    public function test_read_grupo_trabalhador()
    {
        $grupoTrabalhador = factory(GrupoTrabalhador::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/grupo_trabalhadors/'.$grupoTrabalhador->id
        );

        $this->assertApiResponse($grupoTrabalhador->toArray());
    }

    /**
     * @test
     */
    public function test_update_grupo_trabalhador()
    {
        $grupoTrabalhador = factory(GrupoTrabalhador::class)->create();
        $editedGrupoTrabalhador = factory(GrupoTrabalhador::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/grupo_trabalhadors/'.$grupoTrabalhador->id,
            $editedGrupoTrabalhador
        );

        $this->assertApiResponse($editedGrupoTrabalhador);
    }

    /**
     * @test
     */
    public function test_delete_grupo_trabalhador()
    {
        $grupoTrabalhador = factory(GrupoTrabalhador::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/grupo_trabalhadors/'.$grupoTrabalhador->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/grupo_trabalhadors/'.$grupoTrabalhador->id
        );

        $this->response->assertStatus(404);
    }
}
