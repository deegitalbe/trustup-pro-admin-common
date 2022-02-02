<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit\Traits;

use Carbon\Carbon;
use Mockery\MockInterface;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\CustomerApiContract;
use Deegitalbe\TrustupProAdminCommon\Tests\Models\ProfessionalTestModel;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\CustomerContract;
use Deegitalbe\TrustupProAdminCommon\Tests\NotUsingDatabaseTestCase;

class ProfessionalModelTest extends NotUsingDatabaseTestCase
{
    /** @test */
    public function professional_model_trait_getting_customer_id()
    {
        $this->setProfessional()
            ->assertEquals($this->chargebee_customer_id, $this->professional->getCustomerId());
    }

    /** @test */
    public function professional_model_trait_get_customer_returning_null_if_not_having_id()
    {
        $this->mockProfessional();

        $this->mocked_professional->expects()->getCustomerId()->andReturn(null);
        $this->mocked_professional->expects()->getCustomer()->passthru();

        $this->assertNull($this->mocked_professional->getCustomer());
    }

    /** @test */
    public function professional_model_trait_get_customer_returning_null_if_not_finding_customer_through_api()
    {
        $this->mockProfessional()
            ->mockCustomerApi();

        $this->mocked_professional->expects()->getCustomerId()->andReturn($this->chargebee_customer_id)->times(2);
        $this->mocked_professional->expects()->getCustomer()->passthru();

        $this->mocked_customer_api->expects()->find($this->chargebee_customer_id)->andReturnNull();

        $this->assertNull($this->mocked_professional->getCustomer());
    }

    /** @test */
    public function professional_model_trait_setting_customer_if_customer_given()
    {
        $this->setProfessional()
            ->mockCustomer();

        $this->mocked_customer->expects()->getId()->andReturn($this->id);
        $this->professional->setCustomer($this->mocked_customer);

        $this->assertEquals($this->id, $this->professional->getCustomerId());
    }

    /** @test */
    public function professional_model_trait_setting_customer_if_null_given()
    {
        $this->setProfessional();

        $this->professional->setCustomer(null);

        $this->assertNull($this->professional->getCustomerId());
    }

    /** @var string */
    protected $authorization_key = ":authorization_key";

    /** @var Carbon|null */
    protected $created_at = null;
    
    /** @var string */
    protected $chargebee_customer_id = ":chargebee_customer_id";

    /** @var int */
    protected $id = 5;

    /** @var ProfessionalTestModel */
    protected $professional;

    /**
     * Setting up Professional.
     * 
     * @return self
     */
    protected function setProfessional(array $attributes = null): self
    {
        $defaults = [
            'authorization_key' => $this->authorization_key,
            'id' => $this->id,
            'created_at' => $this->created_at,
            'chargebee_customer_id' => $this->chargebee_customer_id
        ];

        $this->professional = new ProfessionalTestModel($attributes ?? $defaults);

        return $this;
    }

    /** @var ProfessionalTestModel|MockInterface */
    protected $mocked_professional;

    /**
     * Setting up professional.
     * 
     * @return self
     */
    protected function mockProfessional(): self
    {
        $this->mocked_professional = $this->mockThis(ProfessionalTestModel::class);

        return $this;
    }

    /** @var CustomerApiContract|MockInterface */
    protected $mocked_customer_api;

    /**
     * Setting up customer api.
     * 
     * @return self
     */
    protected function mockCustomerApi(): self
    {
        $this->mocked_customer_api = $this->mockThis(CustomerApiContract::class);

        return $this;
    }

    /** @var CustomerContract|MockInterface */
    protected $mocked_customer;

    /**
     * Setting up customer.
     * 
     * @return self
     */
    protected function mockCustomer(): self
    {
        $this->mocked_customer = $this->mockThis(CustomerContract::class);

        return $this;
    }
}