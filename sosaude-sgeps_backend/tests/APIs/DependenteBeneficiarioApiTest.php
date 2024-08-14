<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\DependenteBeneficiario;

class DependenteBeneficiarioApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_dependente_beneficiario()
    {
        $dependenteBeneficiario = factory(DependenteBeneficiario::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/dependente_beneficiarios', $dependenteBeneficiario
        );

        $this->assertApiResponse($dependenteBeneficiario);
    }

    /**
     * @test
     */
    public function test_read_dependente_beneficiario()
    {
        $dependenteBeneficiario = factory(DependenteBeneficiario::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/dependente_beneficiarios/'.$dependenteBeneficiario->id
        );

        $this->assertApiResponse($dependenteBeneficiario->toArray());
    }

    /**
     * @test
     */
    public function test_update_dependente_beneficiario()
    {
        $dependenteBeneficiario = factory(DependenteBeneficiario::class)->create();
        $editedDependenteBeneficiario = factory(DependenteBeneficiario::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/dependente_beneficiarios/'.$dependenteBeneficiario->id,
            $editedDependenteBeneficiario
        );

        $this->assertApiResponse($editedDependenteBeneficiario);
    }

    /**
     * @test
     */
    public function test_delete_dependente_beneficiario()
    {
        $dependenteBeneficiario = factory(DependenteBeneficiario::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/dependente_beneficiarios/'.$dependenteBeneficiario->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/dependente_beneficiarios/'.$dependenteBeneficiario->id
        );

        $this->response->assertStatus(404);
    }
}
