<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Farmacia;

class FarmaciaApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_farmacia()
    {
        $farmacia = factory(Farmacia::class)->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/farmacias', $farmacia
        );

        $this->assertApiResponse($farmacia);
    }

    /**
     * @test
     */
    public function test_read_farmacia()
    {
        $farmacia = factory(Farmacia::class)->create();

        $this->response = $this->json(
            'GET',
            '/api/farmacias/'.$farmacia->id
        );

        $this->assertApiResponse($farmacia->toArray());
    }

    /**
     * @test
     */
    public function test_update_farmacia()
    {
        $farmacia = factory(Farmacia::class)->create();
        $editedFarmacia = factory(Farmacia::class)->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/farmacias/'.$farmacia->id,
            $editedFarmacia
        );

        $this->assertApiResponse($editedFarmacia);
    }

    /**
     * @test
     */
    public function test_delete_farmacia()
    {
        $farmacia = factory(Farmacia::class)->create();

        $this->response = $this->json(
            'DELETE',
             '/api/farmacias/'.$farmacia->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/farmacias/'.$farmacia->id
        );

        $this->response->assertStatus(404);
    }
}
