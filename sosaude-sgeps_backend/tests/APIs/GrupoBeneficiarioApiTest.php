<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\GrupoBeneficiario;

class GrupoBeneficiarioApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_grupo_beneficiario()
    {
        $grupoBeneficiario = factory(GrupoBeneficiario::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/grupo_beneficiarios', $grupoBeneficiario
        );

        $this->assertApiResponse($grupoBeneficiario);
    }

    /**
     * @test
     */
    public function test_read_grupo_beneficiario()
    {
        $grupoBeneficiario = factory(GrupoBeneficiario::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/grupo_beneficiarios/'.$grupoBeneficiario->id
        );

        $this->assertApiResponse($grupoBeneficiario->toArray());
    }

    /**
     * @test
     */
    public function test_update_grupo_beneficiario()
    {
        $grupoBeneficiario = factory(GrupoBeneficiario::class)->create();
        $editedGrupoBeneficiario = factory(GrupoBeneficiario::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/grupo_beneficiarios/'.$grupoBeneficiario->id,
            $editedGrupoBeneficiario
        );

        $this->assertApiResponse($editedGrupoBeneficiario);
    }

    /**
     * @test
     */
    public function test_delete_grupo_beneficiario()
    {
        $grupoBeneficiario = factory(GrupoBeneficiario::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/grupo_beneficiarios/'.$grupoBeneficiario->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/grupo_beneficiarios/'.$grupoBeneficiario->id
        );

        $this->response->assertStatus(404);
    }
}
