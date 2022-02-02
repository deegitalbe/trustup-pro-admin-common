<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit\Traits;

use Mockery\MockInterface;
use Deegitalbe\TrustupProAdminCommon\Tests\Models\UserTestModel;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\CustomerContract;
use Deegitalbe\TrustupProAdminCommon\Tests\NotUsingDatabaseTestCase;

class UserModelTest extends NotUsingDatabaseTestCase
{
    /** @test */
    public function user_model_trait_getting_first_name()
    {
        $this->setUser()
            ->assertEquals($this->first_name, $this->user->getFirstName());
    }

    /** @test */
    public function user_model_trait_getting_last_name()
    {
        $this->setUser()
            ->assertEquals($this->last_name, $this->user->getLastName());
    }

    /** @test */
    public function user_model_trait_getting_email()
    {
        $this->setUser()
            ->assertEquals($this->email, $this->user->getEmail());
    }

    /** @test */
    public function user_model_trait_returning_false_if_user_is_not_transformable_to_customer()
    {
        $user = new UserTestModel();
        $this->assertFalse($user->transformableToCustomer());
    }

    /** @test */
    public function user_model_trait_returning_true_if_user_transformable_to_customer()
    {
        $this->setUser()
            ->assertTrue($this->user->transformableToCustomer());
    }

    /** @test */
    public function user_model_trait_to_customer_returning_null_if_not_transformable()
    {
        $this->mockUser();

        $this->mocked_user->expects()->transformableToCustomer()->andReturnFalse();
        $this->mocked_user->expects()->toCustomer()->passthru();
        
        $this->assertNull($this->mocked_user->toCustomer());
    }

    /** @test */
    public function user_model_trait_to_customer_returning_customer_if_transformable()
    {
        $this->mockUser()
            ->mockCustomer();

        $this->mocked_user->expects()->transformableToCustomer()->andReturnTrue();
        $this->mocked_user->expects()->getEmail()->andReturn($this->email);
        $this->mocked_user->expects()->getLastName()->andReturn($this->last_name);
        $this->mocked_user->expects()->getFirstName()->andReturn($this->first_name);
        $this->mocked_user->expects()->toCustomer()->passthru();

        $this->mocked_customer->expects()->setEmail($this->email)->andReturnSelf();
        $this->mocked_customer->expects()->setLastName($this->last_name)->andReturnSelf();
        $this->mocked_customer->expects()->setFirstName($this->first_name)->andReturnSelf();
        
        $this->assertInstanceOf(CustomerContract::class, $this->mocked_user->toCustomer());
    }

    /** @var string */
    protected $first_name = ":first_name";

    /** @var string */
    protected $last_name = ":last_name";
    
    /** @var string */
    protected $email = ":email";

    /** @var UserTestModel */
    protected $user;

    /**
     * Setting up user.
     * 
     * @return self
     */
    protected function setUser(): self
    {
        $this->user = new UserTestModel([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
        ]);

        return $this;
    }

    /** @var UserTestModel */
    protected $mocked_user;

    /**
     * Setting up user.
     * 
     * @return self
     */
    protected function mockUser(): self
    {
        $this->mocked_user = $this->mockThis(UserTestModel::class);

        return $this;
    }

    /** @var CustomerContract|MockInterface */
    protected $mocked_customer;

    /**
     * Setting up user.
     * 
     * @return self
     */
    protected function mockCustomer(): self
    {
        $this->mocked_customer = $this->mockThis(CustomerContract::class);

        return $this;
    }
}