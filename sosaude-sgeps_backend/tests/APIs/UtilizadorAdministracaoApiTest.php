<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\UtilizadorAdministracao;

class UtilizadorAdministracaoApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_utilizador_administracao()
    {
        $utilizadorAdministracao = factory(UtilizadorAdministracao::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/utilizador_administracaos', $utilizadorAdministracao
        );

        $this->assertApiResponse($utilizadorAdministracao);
    }

    /**
     * @test
     */
    public function test_read_utilizador_administracao()
    {
        $utilizadorAdministracao = factory(UtilizadorAdministracao::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/utilizador_administracaos/'.$utilizadorAdministracao->id
        );

        $this->assertApiResponse($utilizadorAdministracao->toArray());
    }

    /**
     * @test
     */
    public function test_update_utilizador_administracao()
    {
        $utilizadorAdministracao = factory(UtilizadorAdministracao::class)->create();
        $editedUtilizadorAdministracao = factory(UtilizadorAdministracao::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/utilizador_administracaos/'.$utilizadorAdministracao->id,
            $editedUtilizadorAdministracao
        );

        $this->assertApiResponse($editedUtilizadorAdministracao);
    }

    /**
     * @test
     */
    public function test_delete_utilizador_administracao()
    {
        $utilizadorAdministracao = factory(UtilizadorAdministracao::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/utilizador_administracaos/'.$utilizadorAdministracao->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/utilizador_administracaos/'.$utilizadorAdministracao->id
        );

        $this->response->assertStatus(404);
    }
}
