<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Sugestao;

class SugestaoApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_sugestao()
    {
        $sugestao = factory(Sugestao::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/sugestaos', $sugestao
        );

        $this->assertApiResponse($sugestao);
    }

    /**
     * @test
     */
    public function test_read_sugestao()
    {
        $sugestao = factory(Sugestao::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/sugestaos/'.$sugestao->id
        );

        $this->assertApiResponse($sugestao->toArray());
    }

    /**
     * @test
     */
    public function test_update_sugestao()
    {
        $sugestao = factory(Sugestao::class)->create();
        $editedSugestao = factory(Sugestao::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/sugestaos/'.$sugestao->id,
            $editedSugestao
        );

        $this->assertApiResponse($editedSugestao);
    }

    /**
     * @test
     */
    public function test_delete_sugestao()
    {
        $sugestao = factory(Sugestao::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/sugestaos/'.$sugestao->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/sugestaos/'.$sugestao->id
        );

        $this->response->assertStatus(404);
    }
}
