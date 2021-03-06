<?php

namespace Tests\Feature;

use Tests\TestCase;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\NewsletterSubscription;

class NewsletterSubscriptionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * it stores a new newsletter subscription
     * @return void
     */
    public function testStore()
    {
        $params = $this->validParams();

        $response = $this->actingAs($this->user())
                         ->post('/newsletter-subscriptions', $params);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Email ajouté à la newsletter avec succès');
        $this->assertDatabaseHas('newsletter_subscriptions', $params);
    }

    /**
     * it does not store duplicate email in newsletter subscription
     * @return void
     */
    public function testStoreFail()
    {
        $params = $this->validParams();
        $newsletter = factory(NewsletterSubscription::class)->create($params);

        $response = $this->actingAs($this->user())
                         ->post('/newsletter-subscriptions', $params);

        $response->assertStatus(302);
        $response->assertSessionHas('errors');
        $this->assertEquals(session('errors')->first(), 'La valeur du champ Adresse e-mail est déjà utilisée.');
    }

    /**
     * it unsubscribes requested email from newsletter
     * @return void
     */
    public function testUnsubscribe()
    {
        $params = $this->validParams();
        $newsletter = factory(NewsletterSubscription::class)->create($params);

        $response = $this->actingAs($this->user())
                        ->get("newsletter-subscriptions/unsubscribe?email={$newsletter->email}");

        $response->assertStatus(200);
        $response->assertSessionHas('success', 'La demande de désabonnement a bien été prise en compte.');
        $this->assertDatabaseMissing('newsletter_subscriptions', $newsletter->toArray());
    }

    /**
     * it unsubscribes requested email from newsletter only if exists
     * @return void
     */
    public function testUnsubscribeFail()
    {
        $params = $this->validParams();

        $response = $this->actingAs($this->user())
                        ->get("newsletter-subscriptions/unsubscribe?email={$params['email']}");

        $response->assertStatus(302)
                 ->assertRedirect('/')
                 ->assertSessionHas('errors');
        $this->assertEquals(session('errors')->first(), 'Le champ Adresse e-mail sélectionné est invalide.');
    }

    /**
     * Valid params for updating or creating a resource
     * @param  array $overrides new params
     * @return array Valid params for updating or creating a resource
     */
    private function validParams($overrides = [])
    {
        return array_merge([
            'email' => 'darthvader@deathstar.ds'
        ], $overrides);
    }
}
