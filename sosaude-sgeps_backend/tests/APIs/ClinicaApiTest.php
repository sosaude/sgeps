<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Clinica;

class ClinicaApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_clinica()
    {
        $clinica = factory(Clinica::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/clinicas', $clinica
        );

        $this->assertApiResponse($clinica);
    }

    /**
     * @test
     */
    public function test_read_clinica()
    {
        $clinica = factory(Clinica::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/clinicas/'.$clinica->id
        );

        $this->assertApiResponse($clinica->toArray());
    }

    /**
     * @test
     */
    public function test_update_clinica()
    {
        $clinica = factory(Clinica::class)->create();
        $editedClinica = factory(Clinica::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/clinicas/'.$clinica->id,
            $editedClinica
        );

        $this->assertApiResponse($editedClinica);
    }

    /**
     * @test
     */
    public function test_delete_clinica()
    {
        $clinica = factory(Clinica::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/clinicas/'.$clinica->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/clinicas/'.$clinica->id
        );

        $this->response->assertStatus(404);
    }
}
