<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\MarcaMedicamento;

class MarcaMedicamentoApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_marca_medicamento()
    {
        $marcaMedicamento = factory(MarcaMedicamento::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/marca_medicamentos', $marcaMedicamento
        );

        $this->assertApiResponse($marcaMedicamento);
    }

    /**
     * @test
     */
    public function test_read_marca_medicamento()
    {
        $marcaMedicamento = factory(MarcaMedicamento::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/marca_medicamentos/'.$marcaMedicamento->id
        );

        $this->assertApiResponse($marcaMedicamento->toArray());
    }

    /**
     * @test
     */
    public function test_update_marca_medicamento()
    {
        $marcaMedicamento = factory(MarcaMedicamento::class)->create();
        $editedMarcaMedicamento = factory(MarcaMedicamento::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/marca_medicamentos/'.$marcaMedicamento->id,
            $editedMarcaMedicamento
        );

        $this->assertApiResponse($editedMarcaMedicamento);
    }

    /**
     * @test
     */
    public function test_delete_marca_medicamento()
    {
        $marcaMedicamento = factory(MarcaMedicamento::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/marca_medicamentos/'.$marcaMedicamento->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/marca_medicamentos/'.$marcaMedicamento->id
        );

        $this->response->assertStatus(404);
    }
}
