<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Seccao;

class SeccaoApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_seccao()
    {
        $seccao = factory(Seccao::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/seccaos', $seccao
        );

        $this->assertApiResponse($seccao);
    }

    /**
     * @test
     */
    public function test_read_seccao()
    {
        $seccao = factory(Seccao::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/seccaos/'.$seccao->id
        );

        $this->assertApiResponse($seccao->toArray());
    }

    /**
     * @test
     */
    public function test_update_seccao()
    {
        $seccao = factory(Seccao::class)->create();
        $editedSeccao = factory(Seccao::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/seccaos/'.$seccao->id,
            $editedSeccao
        );

        $this->assertApiResponse($editedSeccao);
    }

    /**
     * @test
     */
    public function test_delete_seccao()
    {
        $seccao = factory(Seccao::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/seccaos/'.$seccao->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/seccaos/'.$seccao->id
        );

        $this->response->assertStatus(404);
    }
}
