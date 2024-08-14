<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Servico;

class ServicoApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_servico()
    {
        $servico = factory(Servico::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/servicos', $servico
        );

        $this->assertApiResponse($servico);
    }

    /**
     * @test
     */
    public function test_read_servico()
    {
        $servico = factory(Servico::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/servicos/'.$servico->id
        );

        $this->assertApiResponse($servico->toArray());
    }

    /**
     * @test
     */
    public function test_update_servico()
    {
        $servico = factory(Servico::class)->create();
        $editedServico = factory(Servico::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/servicos/'.$servico->id,
            $editedServico
        );

        $this->assertApiResponse($editedServico);
    }

    /**
     * @test
     */
    public function test_delete_servico()
    {
        $servico = factory(Servico::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/servicos/'.$servico->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/servicos/'.$servico->id
        );

        $this->response->assertStatus(404);
    }
}
