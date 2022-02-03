<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit\Services\Account;

use Mockery\MockInterface;
use Illuminate\Support\Facades\Log;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\UserContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionApiContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\CustomerContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\AccountSubscriber;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountSubscriberContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\AppBeingFree;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\NotFindingCustomer;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\PlanNotBelongingToApp;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\AccountNotLinkedToAnyApp;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\AppNotHavingAnyDefaultPlan;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\SubscriptionCreationFailed;
use Deegitalbe\TrustupProAdminCommon\Tests\NotUsingDatabaseTestCase;

class AccountSubscriberTest extends NotUsingDatabaseTestCase
{
    /** @test */
    public function account_subscriber_instanciable_using_contract()
    {
        $this->assertInstanceOfSubscriber($this->app->make(AccountSubscriberContract::class));
    }

    /** @test */
    public function account_subscriber_reporting_account_not_linked_to_any_app()
    {
        $this->assertSubscriberMethodLoggedException('accountNotLinkedToAnyApp', AccountNotLinkedToAnyApp::class);
    }

    /** @test */
    public function account_subscriber_reporting_app_being_free()
    {
        $this->assertSubscriberMethodLoggedException('appBeingFree', AppBeingFree::class);
    }

    /** @test */
    public function account_subscriber_reporting_app_not_having_any_default_plan()
    {
        $this->assertSubscriberMethodLoggedException('appNotHavingAnyDefaultPlan', AppNotHavingAnyDefaultPlan::class);
    }

    /** @test */
    public function account_subscriber_reporting_subscription_creation_failed()
    {
        $this->assertSubscriberMethodLoggedException('subscriptionCreationFailed', SubscriptionCreationFailed::class);
    }

    /** @test */
    public function account_subscriber_reporting_not_finding_customer()
    {
        $this->assertSubscriberMethodLoggedException('notFindingCustomer', NotFindingCustomer::class);
    }

    /** @test */
    public function account_subscriber_reporting_plan_not_belonging_to_app()
    {
        $this->assertSubscriberMethodLoggedException('planNotBelongingToApp', PlanNotBelongingToApp::class);
    }

    /** @test */
    public function account_subscriber_having_context()
    {
        $this->mockSubscriber()
            ->mockSubscriberAccount();

        $this->mocked_subscriber->expects()->context()->passthru();

        $this->mocked_account->shouldReceive('getProfessional')->andReturn(null);
        $this->mocked_account->shouldReceive('getApp')->andReturn(null);
        
        $this->assertEquals([
            "account" => $this->mocked_account,
            "professional" => null,
            "app" => null,
            "plan" => null,
            "user" => null,
            "customer" => null,
            "subscription" => null,
            "subscription_plan" => null
        ], $this->mocked_subscriber->context());
    }

    /** @test */
    public function account_subscriber_getting_account()
    {
        $this->setSubscriber()
            ->mockAccount();
        $this->setPrivateProperty('account', $this->mocked_account, $this->subscriber);
        
        $this->assertEquals($this->mocked_account, $this->subscriber->getAccount());
    }

    /** @test */
    public function account_subscriber_getting_user()
    {
        $this->setSubscriber()
            ->mockUser();
        $this->setPrivateProperty('user', $this->mocked_user, $this->subscriber);
        
        $this->assertEquals($this->mocked_user, $this->subscriber->getUser());
    }

    /** @test */
    public function account_subscriber_getting_plan()
    {
        $this->assertSubscriberNullableGetter('getPlan');
    }

    /** @test */
    public function account_subscriber_getting_customer()
    {
        $this->assertSubscriberNullableGetter('getCustomer');
    }

    /** @test */
    public function account_subscriber_getting_subscription_plan()
    {
        $this->assertSubscriberNullableGetter('getSubscriptionPlan');
    }

    /** @test */
    public function account_subscriber_getting_subscription()
    {
        $this->assertSubscriberNullableGetter('getSubscription');
    }

    /** @test */
    public function account_subscriber_setting_account()
    {
        $this->mockAccount()
            ->assertSubscriberSetter('account', $this->mocked_account);
    }

    /** @test */
    public function account_subscriber_setting_user()
    {
        $this->mockUser()
            ->assertSubscriberSetter('user', $this->mocked_user);
    }

    /** @test */
    public function account_subscriber_setting_plan()
    {
        $this->mockSubscriber()
            ->mockPlan()
            ->mockSubscriptionPlan();

        $this->mocked_plan->expects()->toSubscriptionPlan()->andReturn($this->mocked_subscription_plan);
        $this->mocked_subscriber->expects()->setSubscriptionPlan($this->mocked_subscription_plan)->andReturnSelf();
        $this->mocked_subscriber->expects()->setPlan($this->mocked_plan)->passthru();
        
        $response = $this->callPrivateMethod('setPlan', $this->mocked_subscriber, $this->mocked_plan);
        
        $this->assertInstanceOfSubscriber($response);
        $this->assertEquals($this->mocked_plan, $this->getPrivateProperty('plan', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_setting_subscription_plan()
    {
        $this->mockSubscriptionPlan()
            ->assertSubscriberSetter('subscription_plan', $this->mocked_subscription_plan, 'setSubscriptionPlan');
    }

    /** @test */
    public function account_subscriber_setting_customer()
    {
        $this->mockCustomer()
            ->assertSubscriberSetter('customer', $this->mocked_customer);
    }

    /** @test */
    public function account_subscriber_setting_subscription()
    {
        $this->mockSubscription()
            ->assertSubscriberSetter('subscription', $this->mocked_subscription);
    }

    /** @test */
    public function account_subscriber_fresh_setting_back_values()
    {
        $this->mockSubscriber();

        $this->mocked_subscriber->expects('setPlan')->with(null)->andReturnSelf();
        $this->mocked_subscriber->expects('setCustomer')->with(null)->andReturnSelf();
        $this->mocked_subscriber->expects('setSubscriptionPlan')->with(null)->andReturnSelf();
        $this->mocked_subscriber->expects('setSubscription')->with(null)->andReturnSelf();
        $this->mocked_subscriber->expects('fresh')->with()->passthru();

        $response = $this->callPrivateMethod('fresh', $this->mocked_subscriber);
        $this->assertInstanceOfSubscriber($response);
    }

    /** @test */
    public function account_subscriber_updating_account_status()
    {
        $this->mockAccountChargebee()
            ->mockSubscriber()
            ->mockSubscriberSubscription()
            ->mockSubscriberAccount();

        $this->mocked_account_chargebee->expects()->fromSubscription($this->mocked_subscription)->andReturnSelf();
        $this->mocked_account_chargebee->expects()->persist()->andReturnSelf();

        $this->mocked_account->expects()->setChargebee($this->mocked_account_chargebee);

        $this->mocked_subscriber->expects()->updateAccountStatus()->passthru();

        $this->assertInstanceOfSubscriber($this->callPrivateMethod('updateAccountStatus', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_updating_professional_customer()
    {
        $this->mockProfessional()
            ->mockSubscriber()
            ->mockSubscriberAccount()
            ->mockSubscriberCustomer();

        $this->mocked_account->expects()->getProfessional()->andReturn($this->mocked_professional);
        
        $this->mocked_professional->expects()->setCustomer($this->mocked_customer)->andReturnSelf();
        $this->mocked_professional->expects()->persist()->andReturnSelf();

        $this->mocked_subscriber->expects()->updateProfessionalCustomer()->passthru();

        $this->assertInstanceOfSubscriber($this->callPrivateMethod('updateProfessionalCustomer', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_trying_to_find_a_customer_returning_false_if_customer_not_found()
    {
        $this->mockSubscriber();

        $this->mocked_subscriber->expects()->setCustomerFromAccountOrUser()->andReturnSelf();
        $this->mocked_subscriber->expects()->notFindingCustomer()->andReturn(false);
        $this->mocked_subscriber->expects()->foundACustomer()->passthru();

        $this->assertFalse($this->callPrivateMethod('foundACustomer', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_trying_to_find_a_customer_returning_true_if_customer_found()
    {
        $this->mockSubscriber()
            ->mockCustomer();

        $this->mocked_subscriber->expects()->setCustomerFromAccountOrUser()->andReturnUsing(function(...$args) {
            $this->setPrivateProperty('customer', $this->mocked_customer, $this->mocked_subscriber);
            return $this->mocked_subscriber;
        });
        $this->mocked_subscriber->expects()->foundACustomer()->passthru();

        $this->assertTrue($this->callPrivateMethod('foundACustomer', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_trying_to_create_subscription_returning_false_if_failed()
    {
        $this->mockSubscriber();

        $this->mocked_subscriber->expects()->createSubscription()->andReturnSelf();
        $this->mocked_subscriber->expects()->subscriptionCreationFailed()->andReturn(false);
        $this->mocked_subscriber->expects()->createdASubscription()->passthru();

        $this->assertFalse($this->callPrivateMethod('createdASubscription', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_trying_to_create_subscription_returning_true_if_success()
    {
        $this->mockSubscriber()
            ->mockSubscription();

        $this->mocked_subscriber->expects()->createSubscription()->andReturnUsing(function() {
            $this->setPrivateProperty('subscription', $this->mocked_subscription, $this->mocked_subscriber);
            return $this->mocked_subscriber;
        });
        $this->mocked_subscriber->expects()->createdASubscription()->passthru();

        $this->assertTrue($this->callPrivateMethod('createdASubscription', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_creating_subscription_failed()
    {
        $this->mockSubscriber()
            ->mockSubscriberSubscriptionPlan()
            ->mockSubscriberCustomer();
        
        $this->mocked_subscription_api->expects()->create($this->mocked_subscription_plan, $this->mocked_customer)->andReturnNull();

        $this->mocked_subscriber->expects()->setSubscription(null)->andReturnSelf();
        $this->mocked_subscriber->expects()->createSubscription()->passthru();

        $this->assertInstanceOfSubscriber($this->callPrivateMethod('createSubscription', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_creating_subscription_success()
    {
        $this->mockSubscriber()
            ->mockSubscriberSubscriptionPlan()
            ->mockSubscription()
            ->mockSubscriberCustomer();
        
        $this->mocked_subscription_api->expects()->create($this->mocked_subscription_plan, $this->mocked_customer)
            ->andReturn($this->mocked_subscription);

        $this->mocked_subscription->expects()->getCustomer()->andReturn($this->mocked_customer);

        $this->mocked_subscriber->expects()->setSubscription($this->mocked_subscription)
            ->andReturnUsing(function($subscription) { 
                $this->setPrivateProperty('subscription', $subscription, $this->mocked_subscriber); 
                return $this->mocked_subscriber;
            });
        $this->mocked_subscriber->expects()->setCustomer($this->mocked_customer)->andReturnSelf();
        $this->mocked_subscriber->expects()->createSubscription()->passthru();

        $this->assertInstanceOfSubscriber($this->callPrivateMethod('createSubscription', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_setting_customer_from_account_or_user()
    {
        $this->mockSubscriber()
            ->mockCustomer();

        $this->mocked_subscriber->expects()->getCustomerFromAccountOrUser()->andReturn($this->mocked_customer);
        $this->mocked_subscriber->expects()->setCustomer($this->mocked_customer)->andReturnSelf();
        $this->mocked_subscriber->expects()->setCustomerFromAccountOrUser()->passthru();

        $this->assertInstanceOfSubscriber($this->callPrivateMethod('setCustomerFromAccountOrUser', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_getting_customer_from_account()
    {
        $this->mockSubscriber()
            ->mockCustomer()
            ->mockProfessional()
            ->mockSubscriberAccount();

        $this->mocked_account->expects()->getProfessional()->andReturn($this->mocked_professional);
        $this->mocked_professional->expects()->getCustomer()->andReturn($this->mocked_customer);

        $this->mocked_subscriber->expects()->getCustomerFromAccountOrUser()->passthru();
       
        $this->assertEquals($this->mocked_customer, $this->callPrivateMethod('getCustomerFromAccountOrUser', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_getting_customer_from_user_if_professional_not_having_customer()
    {
        $this->mockSubscriber()
            ->mockCustomer()
            ->mockProfessional()
            ->mockSubscriberAccount()
            ->mockSubscriberUser();

        $this->mocked_account->expects()->getProfessional()->andReturn($this->mocked_professional);
        $this->mocked_professional->expects()->getCustomer()->andReturn(null);

        $this->mocked_user->expects()->toCustomer()->andReturn($this->mocked_customer);

        $this->mocked_subscriber->expects()->getCustomerFromAccountOrUser()->passthru();
       
        $this->assertEquals($this->mocked_customer, $this->callPrivateMethod('getCustomerFromAccountOrUser', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_telling_true_if_account_is_compatible_with_plan()
    {
        $this->mockSubscriber()
            ->mockApp()
            ->mockSubscriberAccount()
            ->mockSubscriberPlan();

        $this->mocked_account->expects()->getApp()->andReturn($this->mocked_app);
        $this->mocked_plan->expects()->getApp()->andReturn($this->mocked_app);

        $this->mocked_app->expects()->getKey()->andReturn('key')->times(2);

        $this->mocked_subscriber->expects()->isAccountCompatibleWithPlan()->passthru();
       
        $this->assertTrue($this->callPrivateMethod('isAccountCompatibleWithPlan', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_telling_false_if_account_is_not_compatible_with_plan()
    {
        $this->mockSubscriber()
            ->mockApp()
            ->mockSubscriberAccount()
            ->mockSubscriberPlan();

        $this->mocked_account->expects()->getApp()->andReturn($this->mocked_app);
        $this->mocked_plan->expects()->getApp()->andReturn($this->mocked_app);

        $this->mocked_app->expects()->getKey()->andReturn('key');
        $this->mocked_app->expects()->getKey()->andReturn('key_2');

        $this->mocked_subscriber->expects()->planNotBelongingToApp()->andReturn(false);
        $this->mocked_subscriber->expects()->isAccountCompatibleWithPlan()->passthru();
       
        $this->assertFalse($this->callPrivateMethod('isAccountCompatibleWithPlan', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_telling_false_if_related_app_is_not_defined()
    {
        $this->mockSubscriber()
            ->mockApp()
            ->mockSubscriberAccount();

        $this->mocked_account->expects()->getApp()->andReturn(null);

        $this->mocked_subscriber->expects()->accountNotLinkedToAnyApp()->andReturn(false);
        $this->mocked_subscriber->expects()->isRelatedAppPaid()->passthru();
       
        $this->assertFalse($this->callPrivateMethod('isRelatedAppPaid', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_telling_false_if_related_app_is_not_paid()
    {
        $this->mockSubscriber()
            ->mockApp()
            ->mockSubscriberAccount();

        $this->mocked_account->expects()->getApp()->andReturn($this->mocked_app);

        $this->mocked_app->expects()->getPaid()->andReturn(false);

        $this->mocked_subscriber->expects()->appBeingFree()->andReturn(false);
        $this->mocked_subscriber->expects()->isRelatedAppPaid()->passthru();
       
        $this->assertFalse($this->callPrivateMethod('isRelatedAppPaid', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_telling_true_if_related_app_is_paid()
    {
        $this->mockSubscriber()
            ->mockApp()
            ->mockSubscriberAccount();

        $this->mocked_account->expects()->getApp()->andReturn($this->mocked_app);

        $this->mocked_app->expects()->getPaid()->andReturn(true);

        $this->mocked_subscriber->expects()->isRelatedAppPaid()->passthru();
       
        $this->assertTrue($this->callPrivateMethod('isRelatedAppPaid', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_successfully_subscribed_account_stopping_on_first_failure()
    {
        $this->mockSubscriber();

        $this->mocked_subscriber->expects()->isRelatedAppPaid()->andReturn(true);
        $this->mocked_subscriber->expects()->isAccountCompatibleWithPlan()->andReturn(true);
        $this->mocked_subscriber->expects()->foundACustomer()->andReturn(false);
        $this->mocked_subscriber->expects()->successfullySubscribedAccount()->passthru();

        $this->assertFalse($this->callPrivateMethod('successfullySubscribedAccount', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_successfully_subscribed_account_returning_true_if_no_failure()
    {
        $this->mockSubscriber();

        $this->mocked_subscriber->expects()->isRelatedAppPaid()->andReturn(true);
        $this->mocked_subscriber->expects()->isAccountCompatibleWithPlan()->andReturn(true);
        $this->mocked_subscriber->expects()->foundACustomer()->andReturn(true);
        $this->mocked_subscriber->expects()->createdASubscription()->andReturn(true);
        $this->mocked_subscriber->expects()->successfullySubscribedAccount()->passthru();

        $this->assertTrue($this->callPrivateMethod('successfullySubscribedAccount', $this->mocked_subscriber));
    }

    /** @test */
    public function account_subscriber_subscribing_to_plan_returning_false_if_case_of_failure()
    {
        $this->accountSubscribeToPlanSetup(false);
    }

    /** @test */
    public function account_subscriber_subscribing_to_plan_returning_true_and_setting_account_and_professional_customer_if_success()
    {
        $this->accountSubscribeToPlanSetup(true, function() {
            $this->mocked_subscriber->expects()->updateAccountStatus()->andReturnSelf();
            $this->mocked_subscriber->expects()->updateProfessionalCustomer()->andReturnSelf();
        });
    }

    protected function accountSubscribeToPlanSetup(bool $expected, callable $before = null)
    {
        $this->mockSubscriber()
            ->mockAccount()
            ->mockUser()
            ->mockPlan();

        $this->mocked_subscriber->expects()->fresh()->andReturnSelf();
        $this->mocked_subscriber->expects()->setUser($this->mocked_user)->andReturnSelf();
        $this->mocked_subscriber->expects()->setAccount($this->mocked_account)->andReturnSelf();
        $this->mocked_subscriber->expects()->setPlan($this->mocked_plan)->andReturnSelf();
        $this->mocked_subscriber->expects()->successfullySubscribedAccount()->andReturn($expected);
        if ($before):
            $before();
        endif;
        $this->mocked_subscriber->expects()->subscribeToPlan($this->mocked_account, $this->mocked_plan, $this->mocked_user)->passthru();
       
        $this->assertEquals(
            $expected,
            $this->callPrivateMethod(
                'subscribeToPlan', 
                $this->mocked_subscriber, 
                $this->mocked_account,
                $this->mocked_plan,
                $this->mocked_user
            )
        );
        
        return $this;
    }

    /**
     * Asserting subscriber setter setting correctly property.
     * 
     * @param mixed $value Value to set.
     * @param string $property Property name set by setter.
     * @return self
     */
    protected function assertSubscriberSetter(string $property, $value, string $setter = null): self
    {
        $this->setSubscriber();

        $response = $this->callPrivateMethod(($setter ?? 'set' . ucfirst($property)), $this->subscriber, $value);

        $this->assertInstanceOfSubscriber($response);
        $this->assertEquals($value, $this->getPrivateProperty($property, $this->subscriber));

        return $this;
    }

    /**
     * Asserting given response is of subscriber type.
     * 
     * @param mixed $response Element we expect to be of subscriber type.
     * @return self
     */
    protected function assertInstanceOfSubscriber($response): self
    {
        $this->assertInstanceOf(AccountSubscriber::class, $response);

        return $this;
    }

    /**
     * Making sure given getter is retrieving property correctly.
     */
    protected function assertSubscriberNullableGetter(string $getter)
    {
        $this->setSubscriber();
        $this->assertNull($this->subscriber->$getter());
    }

    /**
     * Making sure subscriber method logged an exception.
     * 
     * @param string $method Subscriber method
     * @param string $error Error class.
     */
    protected function assertSubscriberMethodLoggedException(string $method, string $error): self
    {
        $this->mockSubscriber();

        $exception = new $error();

        Log::shouldReceive('error')->once()->withArgs(function($message) use ($exception) {
            return $exception->getMessage() === $message;
        });
        
        $this->mocked_subscriber->expects()->context()->andReturn([]);
        $this->mocked_subscriber->expects()->{$method}()->passthru();

        $this->assertFalse($this->callPrivateMethod($method, $this->mocked_subscriber));

        return $this;
    }

    /** @var AccountContract|MockInterface */
    protected $mocked_account;

    /**
     * Mocking account
     * 
     * @return self
     */
    protected function mockAccount(): self
    {
        $this->mocked_account = $this->mockThis(AccountContract::class);

        return $this;
    }

    /** @var AccountChargebeeContract|MockInterface */
    protected $mocked_account_chargebee;

    /**
     * Mocking account chargebee.
     * 
     * @return self
     */
    protected function mockAccountChargebee(): self
    {
        $this->mocked_account_chargebee = $this->mockThis(AccountChargebeeContract::class);

        return $this;
    }

    /** @var UserContract|MockInterface */
    protected $mocked_user;

    /**
     * Mocking user
     * 
     * @return self
     */
    protected function mockUser(): self
    {
        $this->mocked_user = $this->mockThis(UserContract::class);
        
        return $this;
    }

    /** @var ProfessionalContract|MockInterface */
    protected $mocked_professional;

    /**
     * Mocking professional
     * 
     * @return self
     */
    protected function mockProfessional(): self
    {
        $this->mocked_professional = $this->mockThis(ProfessionalContract::class);
        
        return $this;
    }

    /** @var AppContract|MockInterface */
    protected $mocked_app;

    /**
     * Mocking app.
     * 
     * @return self
     */
    protected function mockApp(): self
    {
        $this->mocked_app = $this->mockThis(AppContract::class);
        
        return $this;
    }

    /** @var PlanContract|MockInterface */
    protected $mocked_plan;

    /**
     * Mocking app.
     * 
     * @return self
     */
    protected function mockPlan(): self
    {
        $this->mocked_plan = $this->mockThis(PlanContract::class);
        
        return $this;
    }

    /** @var SubscriptionPlanContract|MockInterface */
    protected $mocked_subscription_plan;

    /**
     * Mocking subscription plan.
     * 
     * @return self
     */
    protected function mockSubscriptionPlan(): self
    {
        $this->mocked_subscription_plan = $this->mockThis(SubscriptionPlanContract::class);
        
        return $this;
    }

    /** @var SubscriptionContract|MockInterface */
    protected $mocked_subscription;

    /**
     * Mocking subscription plan.
     * 
     * @return self
     */
    protected function mockSubscription(): self
    {
        $this->mocked_subscription = $this->mockThis(SubscriptionContract::class);
        
        return $this;
    }

    /** @var CustomerContract|MockInterface */
    protected $mocked_customer;

    /**
     * Mocking customer.
     * 
     * @return self
     */
    protected function mockCustomer(): self
    {
        $this->mocked_customer = $this->mockThis(CustomerContract::class);
        
        return $this;
    }

    /** @var AccountSubscriber|MockInterface */
    protected $mocked_subscriber;

    /**
     * Mocking account
     * 
     * @return self
     */
    protected function mockSubscriber(): self
    {
        $this->mockSubscriptionApi()
            ->mocked_subscriber = $this->mockThis(AccountSubscriber::class, false, [$this->mocked_subscription_api]);

        return $this;
    }

    /** @var AccountSubscriber */
    protected $subscriber;

    /**
     * Setting subscriber
     * 
     * @return self
     */
    protected function setSubscriber(): self
    {
        $this->subscriber = $this->app->make(AccountSubscriber::class);

        return $this;
    }

    /** @var SubscriptionApiContract|MockInterface */
    protected $mocked_subscription_api;

    /**
     * Mocking subscription api.
     * 
     * @return self
     */
    protected function mockSubscriptionApi(): self
    {
        $this->mocked_subscription_api = $this->mockThis(SubscriptionApiContract::class);
        
        return $this;
    }

    /**
     * Mocking subscriber account.
     * 
     * @return self
     */
    protected function mockSubscriberAccount(): self
    {
        $this->mockAccount();
        $this->setPrivateProperty('account', $this->mocked_account, $this->mocked_subscriber);

        return $this;
    }

    /**
     * Mocking subscriber subscription.
     * 
     * @return self
     */
    protected function mockSubscriberSubscription(): self
    {
        $this->mockSubscription();
        $this->setPrivateProperty('subscription', $this->mocked_subscription, $this->mocked_subscriber);

        return $this;
    }

    /**
     * Mocking subscriber customer.
     * 
     * @return self
     */
    protected function mockSubscriberCustomer(): self
    {
        $this->mockCustomer();
        $this->setPrivateProperty('customer', $this->mocked_customer, $this->mocked_subscriber);

        return $this;
    }

    /**
     * Mocking subscriber subscription_plan.
     * 
     * @return self
     */
    protected function mockSubscriberSubscriptionPlan(): self
    {
        $this->mockSubscriptionPlan();
        $this->setPrivateProperty('subscription_plan', $this->mocked_subscription_plan, $this->mocked_subscriber);

        return $this;
    }

    /**
     * Mocking subscriber subscription_plan.
     * 
     * @return self
     */
    protected function mockSubscriberUser(): self
    {
        $this->mockUser();
        $this->setPrivateProperty('user', $this->mocked_user, $this->mocked_subscriber);

        return $this;
    }

    /**
     * Mocking subscriber plan.
     * 
     * @return self
     */
    protected function mockSubscriberPlan(): self
    {
        $this->mockPlan();
        $this->setPrivateProperty('plan', $this->mocked_plan, $this->mocked_subscriber);

        return $this;
    }

}