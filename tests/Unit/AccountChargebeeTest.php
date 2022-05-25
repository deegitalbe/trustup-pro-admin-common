<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Mockery\MockInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Carbon as SupportCarbon;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Models\AccountChargebee;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\App\AppClientContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionApiContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\CustomerContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\PlanQueryContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;

class AccountChargebeeTest extends TestCase
{
    
    /** @var MockInterface|AccountChargebeeContract */
    protected $account_chargebee;

    /** @var MockInterface */
    protected $account;

    /** @var MockInterface */
    protected $customer;

    /** @var MockInterface */
    protected $app_model;
    
    /** @var MockInterface */
    protected $subscription;
    
    /** @var MockInterface */
    protected $subscription_plan;
    
    /** @var MockInterface */
    protected $plan_query;

    /** @var PlanContract */
    protected $plan;

    /** @test */
    public function account_chargebee_linking_plan()
    {
        $plan_name = 'test';

        $plan = app()->make(PlanContract::class)
            ->setName($plan_name);

        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(":test")
            ->setId(':dlfkjqlsfjlsdkjfql')
            ->setPlan($plan)
            ->persist();

        $this->assertEquals($plan_name, $chargebee->fresh()->getPlan()->getName());
    }

    /** @test */
    public function account_chargebee_unlinking_plan()
    {
        $plan_name = 'test';

        $plan = app()->make(PlanContract::class)
            ->setName($plan_name)
            ->persist();

        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(":test")
            ->setId(':dlfkjqlsfjlsdkjfql')
            ->setPlan($plan)
            ->persist()
            ->setPlan(null)
            ->persist();

        $this->assertNull($chargebee->fresh()->getPlan());
    }

    /** @test */
    public function account_chargebee_from_subscription_not_finding_plan()
    {
        $this->setupFromSubscriptionTest();
        // EXPECTATIONS
        $this->plan_query->expects()->first()->andReturnNull();
        $this->account_chargebee->expects()->setPlan(null)->andReturnSelf();

        // START
        $this->account_chargebee->fromSubscription($this->subscription);
    }

    /** @test */
    public function account_chargebee_from_subscription_finding_plan()
    {
        // 
        $this->setupFromSubscriptionTest()
            ->mockPlan();

        // EXPECTATIONS
        $this->plan_query->expects()->first()->andReturn($this->plan);
        $this->account_chargebee->expects()->setPlan($this->plan)->andReturnSelf();

        // START
        $this->account_chargebee->fromSubscription($this->subscription);
    }

    /**
     * Method setting up commmon actions for from subscription related tests.
     * 
     * @return self
     */
    protected function setupFromSubscriptionTest()
    {
        // SETUP
        $this->mockSubscriptionPlan()
            ->mockSubscription()
            ->mockPlanQuery()
            ->mockAccountChargebee()
            ->mockAccount()
            ->mockApp()
            ->mockCustomer();

        // EXPECTATIONS
        $this->subscription_plan->expects()->getId()->andReturn('plan_id');
        
        $this->customer->expects()->isChargeable()->andReturnFalse();

        $this->subscription->expects()->getStatus()->andReturn('subscription_status');
        $this->subscription->expects()->getId()->andReturn('subscription_id');
        $this->subscription->expects()->getTrialEndingAt()->andReturnNull();
        $this->subscription->expects()->getCustomer()->andReturn($this->customer);
        $this->subscription->expects()->getPlan()->andReturn($this->subscription_plan);

        $this->plan_query->expects()->whereName('plan_id')->andReturnSelf();
        $this->plan_query->expects()->whereApp($this->app_model)->andReturnSelf();

        $this->account->expects()->getApp()->andReturn($this->app_model);

        $this->account_chargebee->expects()->fromSubscription($this->subscription)->passthru();
        $this->account_chargebee->expects()->getAccount()->andReturn($this->account);
        $this->account_chargebee->expects()->setStatus('subscription_status')->andReturnSelf();
        $this->account_chargebee->expects()->setId('subscription_id')->andReturnSelf();
        $this->account_chargebee->expects()->setTrialEndingAt(null)->andReturnSelf();
        $this->account_chargebee->expects()->setIsChargeable(false)->andReturnSelf();

        return $this;
    }

    /** @test */
    public function account_chargebee_refresh_from_api_not_finding_subscription()
    {
        $subscription_api = $this->mockThis(SubscriptionApiContract::class);
        $account_chargebee = $this->mockThis(AccountChargebee::class);

        $subscription_api->expects()->find("test")->andReturnNull();
        $account_chargebee->expects()->refreshFromApi()->passthru();
        $account_chargebee->expects()->getId()->andReturn("test");

        $account_chargebee->refreshFromApi();
    }

    /** @test */
    public function account_chargebee_refresh_from_api_finding_subscription()
    {
        $subscription_api = $this->mockThis(SubscriptionApiContract::class);
        $account_chargebee = $this->mockThis(AccountChargebee::class);
        $account = $this->mockThis(AccountContract::class);
        $subscription = $this->mockThis(SubscriptionContract::class);

        $subscription_api->expects()->find("test")->andReturn($subscription);
        
        $account_chargebee->expects()->refreshFromApi()->passthru();
        $account_chargebee->expects()->getId()->andReturn("test");
        $account_chargebee->expects()->refreshFromSubscription($subscription, false);

        $account_chargebee->refreshFromApi();
    }

    /** @test */
    public function account_chargebee_refresh_from_subscription_but_not_updating_app_database()
    {
        $subscription = $this->mockThis(SubscriptionContract::class);
        $account_chargebee = $this->mockThis(AccountChargebee::class);
        
        $account_chargebee->expects()->refreshFromSubscription($subscription)->passthru();
        $account_chargebee->expects()->fromSubscription($subscription);
        $account_chargebee->expects()->shouldBeUpdatedInApp()->andReturn(false);
        $account_chargebee->expects()->persist()->andReturnSelf();

        $account_chargebee->refreshFromSubscription($subscription);
    }

    /** @test */
    public function account_chargebee_refresh_from_subscription_but_updating_app_database()
    {
        $subscription = $this->mockThis(SubscriptionContract::class);
        $account_chargebee = $this->mockThis(AccountChargebee::class);
        $account = $this->mockThis(AccountContract::class);
        
        $account_chargebee->expects()->refreshFromSubscription($subscription)->passthru();
        $account_chargebee->expects()->fromSubscription($subscription);
        $account_chargebee->expects()->shouldBeUpdatedInApp()->andReturn(true);
        $account_chargebee->expects()->getAccount()->andReturn($account);
        $account_chargebee->expects()->persist()->andReturnSelf();

        $account->expects()->updateInApp();

        $account_chargebee->refreshFromSubscription($subscription);
    }

    /** @test */
    public function account_chargebee_refresh_from_subscription_but_force_updating_app_database()
    {
        $subscription = $this->mockThis(SubscriptionContract::class);
        $account_chargebee = $this->mockThis(AccountChargebee::class);
        $account = $this->mockThis(AccountContract::class);
        
        $account_chargebee->expects()->refreshFromSubscription($subscription, true)->passthru();
        $account_chargebee->expects()->fromSubscription($subscription);
        $account_chargebee->expects()->getAccount()->andReturn($account);
        $account_chargebee->expects()->persist()->andReturnSelf();

        $account->expects()->updateInApp();

        $account_chargebee->refreshFromSubscription($subscription, true);
    }

    /** @test */
    public function account_chargebee_should_be_updated_in_app()
    {
        $account_chargebee = $this->mockThis(AccountChargebee::class);
        
        $account_chargebee->expects()->shouldBeUpdatedInApp()->passthru();
        $account_chargebee->expects()->isDifferentConcerningAppDatabase($account_chargebee);
        $account_chargebee->expects()->fresh()->andReturn($account_chargebee);

        $account_chargebee->shouldBeUpdatedInApp();
    }

    /** @test */
    public function account_chargebee_is_different_concerning_app_database_returning_false_if_both_same()
    {
        $account_chargebee = $this->mockThis(AccountChargebee::class);
        $account_chargebee_2 = $this->mockThis(AccountChargebee::class);
        
        $account_chargebee->expects()->isDifferentConcerningAppDatabase($account_chargebee_2)->passthru();
        
        $account_chargebee->expects()->getStatus()->andReturn("status");
        $account_chargebee->expects()->getId()->andReturn("id");

        $account_chargebee_2->expects()->getStatus()->andReturn("status");
        $account_chargebee_2->expects()->getId()->andReturn("id");


        $this->assertFalse($account_chargebee->isDifferentConcerningAppDatabase($account_chargebee_2));
    }

    /** @test */
    public function account_chargebee_is_different_concerning_app_database_returning_true_if_ids_different()
    {
        $account_chargebee = $this->mockThis(AccountChargebee::class);
        $account_chargebee_2 = $this->mockThis(AccountChargebee::class);
        
        $account_chargebee->expects()->isDifferentConcerningAppDatabase($account_chargebee_2)->passthru();
        
        // $account_chargebee->expects()->getStatus()->andReturn("status");
        $account_chargebee->expects()->getId()->andReturn("id");

        // $account_chargebee_2->expects()->getStatus()->andReturn("status");
        $account_chargebee_2->expects()->getId()->andReturn("id_2");


        $this->assertTrue($account_chargebee->isDifferentConcerningAppDatabase($account_chargebee_2));
    }

    /** @test */
    public function account_chargebee_is_different_concerning_app_database_returning_true_if_statuses_different()
    {
        $account_chargebee = $this->mockThis(AccountChargebee::class);
        $account_chargebee_2 = $this->mockThis(AccountChargebee::class);
        
        $account_chargebee->expects()->isDifferentConcerningAppDatabase($account_chargebee_2)->passthru();
        
        $account_chargebee->expects()->getStatus()->andReturn("status");
        $account_chargebee->expects()->getId()->andReturn("id");

        $account_chargebee_2->expects()->getStatus()->andReturn("status_2");
        $account_chargebee_2->expects()->getId()->andReturn("id");


        $this->assertTrue($account_chargebee->isDifferentConcerningAppDatabase($account_chargebee_2));
    }

    /** @test */
    public function account_chargebee_refresh_from_api_real_chargebee_call()
    {
        $account_chargebee = $this->app->make(AccountChargebeeContract::class)
            ->setStatus('active')
            ->setId('AzyuGGSrasBzJnDM')
            ->persist();

        $plan = $this->app->make(PlanContract::class)
            ->setName('trustup-pro-todo')
            ->setTrialDuration(14)
            ->setIsDefault(true)
            ->setPriceInCent(4000)
            ->persist();

        $app = $this->app->make(AppContract::class)
            ->setKey('todo')
            ->persist()
            ->addPlan($plan)
            ->persist();

        $account = $this->app->make(AccountContract::class)
            ->persist()
            ->setChargebee($account_chargebee)
            ->setApp($app);

        $mocked_app_client = $this->mockThis(AppClientContract::class);

        $mocked_app_client->expects()
            ->updateAccount()
            ->withArgs(function($real_account) use ($account) { return $real_account->_id === $account->_id; })
            ->andReturnNull();
        $mocked_app_client->expects()->setApp()
            ->withArgs(function($real_app) use ($app) { return $real_app->getKey() === $app->getKey(); })
            ->andReturnSelf();

        $refreshed = $account_chargebee->refreshFromApi();

        $this->assertTrue($refreshed->isTrial());
        $this->assertNotNull($refreshed->getTrialEndingAt());
    }

    /** @test */
    public function account_chargebee_setting_account()
    {
        $this->mockAccount()
            ->mockAccountChargebee();

        $this->account_chargebee->expects()->setAccount($this->account)->passthru();
        $this->account->expects()->setChargebee($this->account_chargebee);

        $this->assertInstanceOf(AccountChargebeeContract::class, $this->account_chargebee->setAccount($this->account));
    }

    /** @test */
    public function account_chargebee_close_to_be_cancelled_saying_false_if_not_having_unpaid_invoices()
    {
        $this->mockAccountChargebee();

        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(false);
        $this->account_chargebee->expects()->isCloseToBeCancelled()->passthru();

        $this->assertFalse($this->account_chargebee->isCloseToBeCancelled());
    }

    /** @test */
    public function account_chargebee_close_to_be_cancelled_saying_false_if_being_cancelled_invoices()
    {
        $this->mockAccountChargebee();

        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(true);
        $this->account_chargebee->expects()->isCancelled()->andReturn(true);
        $this->account_chargebee->expects()->isCloseToBeCancelled()->passthru();

        $this->assertFalse($this->account_chargebee->isCloseToBeCancelled());
    }

    /** @test */
    public function account_chargebee_close_to_be_cancelled_saying_false_if_threshold_not_reached()
    {
        $this->mockAccountChargebee()
            ->mockCarbonNow(new Carbon('2020-01-12'));

        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(true);
        $this->account_chargebee->expects()->isCancelled()->andReturn(false);
        $this->account_chargebee->expects()->getLastUnpaidInvoiceAt()->andReturn(new Carbon('2020-01-01'));
        $this->account_chargebee->expects()->getCancelAlertThreshold()->andReturn(14);
        $this->account_chargebee->expects()->isCloseToBeCancelled()->passthru();

        $this->assertFalse($this->account_chargebee->isCloseToBeCancelled());
    }

    /** @test */
    public function account_chargebee_close_to_be_cancelled_saying_false_if_before_threshold_but_should_be_cancelled()
    {
        $this->mockAccountChargebee();
            
        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(true);
        $this->account_chargebee->expects()->isCancelled()->andReturn(false);
        $this->account_chargebee->expects()->getLastUnpaidInvoiceAt()->andReturn(new Carbon('2020-01-01'));
        $this->account_chargebee->expects()->getCancelAlertThreshold()->andReturn(9);
        $this->account_chargebee->expects()->shouldBeCancelled()->andReturn(true);
        $this->account_chargebee->expects()->isCloseToBeCancelled()->passthru();

        $this->assertFalse($this->account_chargebee->isCloseToBeCancelled());
    }

    /** @test */
    public function account_chargebee_close_to_be_cancelled_saying_true_if_before_threshold_and_should_not_be_cancelled()
    {
        $this->mockAccountChargebee();
            
        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(true);
        $this->account_chargebee->expects()->isCancelled()->andReturn(false);
        $this->account_chargebee->expects()->getLastUnpaidInvoiceAt()->andReturn(new Carbon('2020-01-01'));
        $this->account_chargebee->expects()->getCancelAlertThreshold()->andReturn(9);
        $this->account_chargebee->expects()->shouldBeCancelled()->andReturn(false);
        $this->account_chargebee->expects()->isCloseToBeCancelled()->passthru();

        $this->assertTrue($this->account_chargebee->isCloseToBeCancelled());
    }

    /** @test */
    public function account_chargebee_should_alert_about_cancellation_saying_false_if_not_having_unpaid_invoices()
    {
        $this->mockAccountChargebee();

        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(false);
        $this->account_chargebee->expects()->shouldAlertAboutCancellation()->passthru();

        $this->assertFalse($this->account_chargebee->shouldAlertAboutCancellation());
    }

    /** @test */
    public function account_chargebee_should_alert_about_cancellation_saying_false_if_being_cancelled()
    {
        $this->mockAccountChargebee();

        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(true);
        $this->account_chargebee->expects()->isCancelled()->andReturn(true);
        $this->account_chargebee->expects()->shouldAlertAboutCancellation()->passthru();

        $this->assertFalse($this->account_chargebee->shouldAlertAboutCancellation());
    }

    /** @test */
    public function account_chargebee_should_alert_about_cancellation_saying_false_if_threshold_not_reached()
    {
        $this->mockAccountChargebee()
            ->mockCarbonNow(new Carbon('2020-01-12'));

        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(true);
        $this->account_chargebee->expects()->isCancelled()->andReturn(false);
        $this->account_chargebee->expects()->getLastUnpaidInvoiceAt()->andReturn(new Carbon('2020-01-01'));
        $this->account_chargebee->expects()->getCancelAlertThreshold()->andReturn(14);
        $this->account_chargebee->expects()->shouldAlertAboutCancellation()->passthru();

        $this->assertFalse($this->account_chargebee->shouldAlertAboutCancellation());
    }

    /** @test */
    public function account_chargebee_should_alert_about_cancellation_saying_true_if_same_as_threshold()
    {
        $this->mockAccountChargebee()
            ->mockCarbonNow(new Carbon('2020-01-10'));

        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(true);
        $this->account_chargebee->expects()->isCancelled()->andReturn(false);
        $this->account_chargebee->expects()->getLastUnpaidInvoiceAt()->andReturn(new Carbon('2020-01-01'));
        $this->account_chargebee->expects()->getCancelAlertThreshold()->andReturn(9);
        $this->account_chargebee->expects()->shouldAlertAboutCancellation()->passthru();

        $this->assertTrue($this->account_chargebee->shouldAlertAboutCancellation());
    }

    /** @test */
    public function account_chargebee_should_be_cancelled_saying_false_if_not_having_unpaid_invoices()
    {
        $this->mockAccountChargebee();

        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(false);
        $this->account_chargebee->expects()->shouldBeCancelled()->passthru();

        $this->assertFalse($this->account_chargebee->shouldBeCancelled());
    }

    /** @test */
    public function account_chargebee_should_be_cancelled_saying_false_if_being_cancelled()
    {
        $this->mockAccountChargebee();

        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(true);
        $this->account_chargebee->expects()->isCancelled()->andReturn(true);
        $this->account_chargebee->expects()->shouldBeCancelled()->passthru();

        $this->assertFalse($this->account_chargebee->shouldBeCancelled());
    }

    /** @test */
    public function account_chargebee_should_be_cancelled_saying_false_if_threshold_not_reached()
    {
        $this->mockAccountChargebee()
            ->mockCarbonNow(new Carbon('2020-01-12'));

        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(true);
        $this->account_chargebee->expects()->isCancelled()->andReturn(false);
        $this->account_chargebee->expects()->getLastUnpaidInvoiceAt()->andReturn(new Carbon('2020-01-01'));
        $this->account_chargebee->expects()->getCancelThreshold()->andReturn(14);
        $this->account_chargebee->expects()->shouldBeCancelled()->passthru();

        $this->assertFalse($this->account_chargebee->shouldBeCancelled());
    }

    /** @test */
    public function account_chargebee_should_be_cancelled_saying_false_if_same_as_threshold()
    {
        $this->mockAccountChargebee()
            ->mockCarbonNow(new Carbon('2020-01-10'));

        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(true);
        $this->account_chargebee->expects()->isCancelled()->andReturn(false);
        $this->account_chargebee->expects()->getLastUnpaidInvoiceAt()->andReturn(new Carbon('2020-01-01'));
        $this->account_chargebee->expects()->getCancelThreshold()->andReturn(9);
        $this->account_chargebee->expects()->shouldBeCancelled()->passthru();

        $this->assertTrue($this->account_chargebee->shouldBeCancelled());
    }

    /** @test */
    public function account_chargebee_should_be_cancelled_saying_false_if_after_threshold()
    {
        $this->mockAccountChargebee()
            ->mockCarbonNow(new Carbon('2020-01-11'));

        $this->account_chargebee->expects()->havingLastUnpaidInvoiceAt()->andReturn(true);
        $this->account_chargebee->expects()->isCancelled()->andReturn(false);
        $this->account_chargebee->expects()->getLastUnpaidInvoiceAt()->andReturn(new Carbon('2020-01-01'));
        $this->account_chargebee->expects()->getCancelThreshold()->andReturn(9);
        $this->account_chargebee->expects()->shouldBeCancelled()->passthru();

        $this->assertTrue($this->account_chargebee->shouldBeCancelled());
    }

    /** @test */
    public function account_chargebee_getting_cancel_alert_threshold()
    {
        $threshold = 10;
        $this->mockAccountChargebee(true);

        $this->account_chargebee->expects()->setDefaultCancelAlertThreshold()->andSet('cancel_alert_threshold', $threshold)->andReturnSelf();

        $this->assertEquals($threshold, $this->account_chargebee->getCancelAlertThreshold());
    }

    /** @test */
    public function account_chargebee_setting_cancel_alert_threshold()
    {
        $threshold = 10;
        $accountChargebee = app(AccountChargebeeContract::class);
        $accountChargebee->setCancelAlertThreshold($threshold);

        $this->assertEquals($threshold, $accountChargebee->cancel_alert_threshold);
    }

    /** @test */
    public function account_chargebee_setting_default_cancel_alert_threshold()
    {
        $this->mockAccountChargebee(true);

        $this->account_chargebee->expects()->setCancelAlertThreshold(9)->andReturnSelf();
        $this->account_chargebee->expects()->setDefaultCancelAlertThreshold()->passthru();

        $this->assertInstanceOf(AccountChargebeeContract::class, $this->account_chargebee->setDefaultCancelAlertThreshold());
    }




    /** @test */
    public function account_chargebee_getting_cancel_threshold()
    {
        $threshold = 10;
        $this->mockAccountChargebee(true);

        $this->account_chargebee->expects()->setDefaultCancelThreshold()->andSet('cancel_threshold', $threshold)->andReturnSelf();

        $this->assertEquals($threshold, $this->account_chargebee->getCancelThreshold());
    }

    /** @test */
    public function account_chargebee_setting_cancel_threshold()
    {
        $threshold = 10;
        $accountChargebee = app(AccountChargebeeContract::class);
        $accountChargebee->setCancelThreshold($threshold);

        $this->assertEquals($threshold, $accountChargebee->cancel_threshold);
    }

    /** @test */
    public function account_chargebee_setting_default_cancel_threshold()
    {
        $this->mockAccountChargebee();

        $this->account_chargebee->expects()->setDefaultCancelThreshold()->passthru();
        $this->account_chargebee->expects()->setCancelThreshold(14)->andReturnSelf();

        $this->assertInstanceOf(AccountChargebeeContract::class, $this->account_chargebee->setDefaultCancelThreshold());
    }

    /** @test */
    public function account_chargebee_setting_last_unpaid_invoice_at()
    {

        $date = new Carbon('2020-01-10');
        $accountChargebee = app(AccountChargebeeContract::class);

        $this->assertInstanceOf(AccountChargebeeContract::class, $accountChargebee->setLastUnpaidInvoiceAt($date));
        $accountChargebee->save();
        $this->assertEquals($date->toDateTimeString(), $accountChargebee->fresh()->last_unpaid_invoice_at->toDateTimeString());
    }

    /** @test */
    public function account_chargebee_getting_last_unpaid_invoice_at()
    {
        $this->assertNull(app(AccountChargebeeContract::class)->last_unpaid_invoice_at);
    }

    /** @test */
    public function account_chargebee_telling_false_if_not_having_last_unpaid_invoice_at()
    {
        $this->assertFalse(app(AccountChargebeeContract::class)->havingLastUnpaidInvoiceAt());
    }

    /** @test */
    public function account_chargebee_telling_true_if_having_last_unpaid_invoice_at()
    {
        $chargebee = app(AccountChargebeeContract::class);
        $chargebee->last_unpaid_invoice_at = now();
        
        $this->assertTrue($chargebee->havingLastUnpaidInvoiceAt());
    }

    /**
     * Mocking subscription.
     * 
     * @return self
     */
    protected function mockSubscription(): self
    {
        $this->subscription = $this->mockThis(SubscriptionContract::class);

        return $this;
    }

    /**
     * Mocking subscription plan.
     * 
     * @return self
     */
    protected function mockSubscriptionPlan(): self
    {
        $this->subscription_plan = $this->mockThis(SubscriptionPlanContract::class);

        return $this;
    }
    
    /**
     * Mocking plan query.
     * 
     * @return self
     */
    protected function mockPlanQuery(): self
    {
        $this->plan_query = $this->mockThis(PlanQueryContract::class);

        return $this;
    }

    /**
     * Mocking concrete account chargebee.
     * 
     * @return self
     */
    protected function mockAccountChargebee($is_partial = false): self
    {
        $this->account_chargebee = $this->mockThis(Package::accountChargebee(), $is_partial);

        return $this;
    }

    /**
     * Mocking app plan.
     * 
     * @return self
     */
    protected function mockPlan(): self
    {
        $this->plan = $this->mockThis(PlanContract::class);

        return $this;
    }

    /**
     * Mocking app.
     * 
     * @return self
     */
    protected function mockApp(): self
    {
        $this->app_model = $this->mockThis(AppContract::class);

        return $this;
    }

    /**
     * Mocking customer.
     * 
     * @return self
     */
    protected function mockCustomer(): self
    {
        $this->customer = $this->mockThis(CustomerContract::class);

        return $this;
    }


    /**
     * Mocking account.
     * 
     * @return self
     */
    protected function mockAccount(): self
    {
        $this->account = $this->mockThis(AccountContract::class);

        return $this;
    }
}