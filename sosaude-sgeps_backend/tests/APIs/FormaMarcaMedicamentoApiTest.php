<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\FormaMarcaMedicamento;

class FormaMarcaMedicamentoApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_forma_marca_medicamento()
    {
        $formaMarcaMedicamento = factory(FormaMarcaMedicamento::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/forma_marca_medicamentos', $formaMarcaMedicamento
        );

        $this->assertApiResponse($formaMarcaMedicamento);
    }

    /**
     * @test
     */
    public function test_read_forma_marca_medicamento()
    {
        $formaMarcaMedicamento = factory(FormaMarcaMedicamento::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/forma_marca_medicamentos/'.$formaMarcaMedicamento->id
        );

        $this->assertApiResponse($formaMarcaMedicamento->toArray());
    }

    /**
     * @test
     */
    public function test_update_forma_marca_medicamento()
    {
        $formaMarcaMedicamento = factory(FormaMarcaMedicamento::class)->create();
        $editedFormaMarcaMedicamento = factory(FormaMarcaMedicamento::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/forma_marca_medicamentos/'.$formaMarcaMedicamento->id,
            $editedFormaMarcaMedicamento
        );

        $this->assertApiResponse($editedFormaMarcaMedicamento);
    }

    /**
     * @test
     */
    public function test_delete_forma_marca_medicamento()
    {
        $formaMarcaMedicamento = factory(FormaMarcaMedicamento::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/forma_marca_medicamentos/'.$formaMarcaMedicamento->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/forma_marca_medicamentos/'.$formaMarcaMedicamento->id
        );

        $this->response->assertStatus(404);
    }
}
