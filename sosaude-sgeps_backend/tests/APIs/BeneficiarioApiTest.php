<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Beneficiario;

class BeneficiarioApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_beneficiario()
    {
        $beneficiario = factory(Beneficiario::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/beneficiarios', $beneficiario
        );

        $this->assertApiResponse($beneficiario);
    }

    /**
     * @test
     */
    public function test_read_beneficiario()
    {
        $beneficiario = factory(Beneficiario::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/beneficiarios/'.$beneficiario->id
        );

        $this->assertApiResponse($beneficiario->toArray());
    }

    /**
     * @test
     */
    public function test_update_beneficiario()
    {
        $beneficiario = factory(Beneficiario::class)->create();
        $editedBeneficiario = factory(Beneficiario::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/beneficiarios/'.$beneficiario->id,
            $editedBeneficiario
        );

        $this->assertApiResponse($editedBeneficiario);
    }

    /**
     * @test
     */
    public function test_delete_beneficiario()
    {
        $beneficiario = factory(Beneficiario::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/beneficiarios/'.$beneficiario->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/beneficiarios/'.$beneficiario->id
        );

        $this->response->assertStatus(404);
    }
}
