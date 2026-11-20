<?php

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\NewCollection;
use App\Notifications\OrderPlaced;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Str;

it('shows the notification bell only to authenticated customers', function () {
    $this->get(route('home'))->assertDontSee('id="customer-notifications"', false);
    $customer = User::factory()->create();
    $this->actingAs($customer)->get(route('home'))->assertSee('id="customer-notifications"', false);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->actingAs($admin)->get(route('home'))->assertDontSee('id="customer-notifications"', false);
});

it('lists only the customers notifications and returns the unread count', function () {
    $customer = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Verão']);
    $customer->notify(new NewCollection($category));
    $other->notify(new NewCollection($category));

    $this->actingAs($customer)->getJson(route('account.notifications.index'))
        ->assertOk()->assertJsonPath('unread_count', 1)
        ->assertJsonCount(1, 'notifications')
        ->assertJsonPath('notifications.0.title', 'Nova coleção: Verão')
        ->assertJsonPath('notifications.0.read', false)
        ->assertJsonPath('notifications.0.url', route('shop.category', $category->slug, absolute: false));
});

it('marks an owned notification as read and preserves the other customers unread notifications', function () {
    $customer = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create();
    $customer->notify(new NewCollection($category));
    $other->notify(new NewCollection($category));
    $notification = $customer->notifications()->sole();

    $this->actingAs($customer)->postJson(route('account.notifications.read', $notification->id))
        ->assertOk()->assertJsonPath('unread_count', 0)->assertJsonPath('notifications.0.read', true);

    expect($notification->fresh()->read_at)->not->toBeNull();
    expect($other->unreadNotifications()->count())->toBe(1);
});

it('prevents customers from marking another customers notifications as read', function () {
    $customer = User::factory()->create();
    $other = User::factory()->create();
    $other->notify(new NewCollection(Category::factory()->create()));
    $notification = $other->notifications()->sole();

    $this->actingAs($customer)->postJson(route('account.notifications.read', $notification->id))->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();
});

it('marks all notifications as read only for the current customer', function () {
    $customer = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create();
    $customer->notify(new NewCollection($category));
    $customer->notify(new NewCollection($category));
    $other->notify(new NewCollection($category));

    $this->actingAs($customer)->postJson(route('account.notifications.read-all'))
        ->assertOk()->assertJsonPath('unread_count', 0);

    expect($customer->unreadNotifications()->count())->toBe(0);
    expect($other->unreadNotifications()->count())->toBe(1);
});

it('requires authentication and denies administrator access to customer notification endpoints', function () {
    $this->getJson(route('account.notifications.index'))->assertUnauthorized();
    $this->postJson(route('account.notifications.read-all'))->assertUnauthorized();
    $this->postJson(route('account.notifications.read', Str::uuid()))->assertUnauthorized();
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->getJson(route('account.notifications.index'))->assertForbidden();
    $this->postJson(route('account.notifications.read-all'))->assertForbidden();
    $this->postJson(route('account.notifications.read', Str::uuid()))->assertForbidden();
});

it('stores order updates for the bell without contacting the mail server', function () {
    $customer = User::factory()->create();
    $order = $customer->orders()->create(['status' => OrderStatus::Cancelled, 'total_cents' => 2500]);

    $customer->notifyNow(new OrderStatusChanged($order, OrderStatus::Paid), ['database']);

    $this->actingAs($customer)->getJson(route('account.notifications.index'))
        ->assertOk()->assertJsonPath('unread_count', 1)
        ->assertJsonPath('notifications.0.title', 'Encomenda Cancelada')
        ->assertJsonPath('notifications.0.body', 'A tua encomenda #'.$order->id.' passou de Paga para Cancelada.')
        ->assertJsonPath('notifications.0.url', route('account.orders.show', $order, absolute: false));
});

it('stores order confirmations in the customers notifications', function () {
    $customer = User::factory()->create();
    $order = $customer->orders()->create(['status' => OrderStatus::Pending, 'total_cents' => 2500]);

    $customer->notifyNow(new OrderPlaced($order), ['database']);

    $this->actingAs($customer)->getJson(route('account.notifications.index'))
        ->assertOk()->assertJsonPath('notifications.0.title', 'Encomenda recebida')
        ->assertJsonPath('notifications.0.kind', 'order');
});

it('makes an order notification available while its email is still queued', function () {
    config(['queue.default' => 'database']);
    $customer = User::factory()->create();
    $address = Address::factory()->for($customer)->create();
    $variant = ProductVariant::factory()->create(['stock' => 5]);

    $this->actingAs($customer)->withSession(['cart' => [$variant->id => 1]])
        ->post(route('checkout.store'), ['address_id' => $address->id])->assertRedirect();

    $this->getJson(route('account.notifications.index'))->assertOk()
        ->assertJsonPath('unread_count', 1)->assertJsonPath('notifications.0.title', 'Encomenda recebida');
    $this->assertDatabaseCount('jobs', 1);
});

it('announces an active collection once to customers and not administrators', function () {
    $customer = User::factory()->create();
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $category = Product::factory()->create(['is_active' => true])->category;

    $this->actingAs($admin)->post(route('admin.categories.announce', $category))
        ->assertRedirect(route('admin.categories.index'))->assertSessionHas('success');
    $this->post(route('admin.categories.announce', $category))->assertSessionHas('error');

    expect($category->fresh()->collection_announced_at)->not->toBeNull();
    expect($customer->notifications()->count())->toBe(1);
    expect($admin->notifications()->count())->toBe(0);
    expect($customer->notifications()->sole()->data['category_id'])->toBe($category->id);
});

it('does not announce an empty or inactive collection', function (bool $withInactiveProduct) {
    $customer = User::factory()->create();
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $category = $withInactiveProduct
        ? Product::factory()->create(['is_active' => false])->category
        : Category::factory()->create();

    $this->actingAs($admin)->post(route('admin.categories.announce', $category))->assertSessionHas('error');

    expect($category->fresh()->collection_announced_at)->toBeNull();
    expect($customer->notifications()->count())->toBe(0);
})->with([false, true]);

it('prevents customers from publishing collection announcements', function () {
    $customer = User::factory()->create();
    $category = Product::factory()->create(['is_active' => true])->category;

    $this->actingAs($customer)->post(route('admin.categories.announce', $category))->assertForbidden();

    expect($category->fresh()->collection_announced_at)->toBeNull();
    expect($customer->notifications()->count())->toBe(0);
});

it('queues one collection email per customer while immediately updating their bells', function () {
    config(['queue.default' => 'database']);
    $customers = User::factory()->count(2)->create();
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $category = Product::factory()->create(['is_active' => true])->category;

    $this->actingAs($admin)->post(route('admin.categories.announce', $category))
        ->assertRedirect(route('admin.categories.index'))->assertSessionHas('success');
    $this->post(route('admin.categories.announce', $category))->assertSessionHas('error');

    $this->assertDatabaseCount('jobs', 2);
    foreach ($customers as $customer) {
        expect($customer->unreadNotifications()->count())->toBe(1);
    }
    expect($admin->notifications()->count())->toBe(0);
});

it('renders a collection email with its name and a link to the announced collection', function () {
    $customer = User::factory()->make(['name' => 'Ana']);
    $category = Category::factory()->create(['name' => 'Verão', 'slug' => 'verao']);
    $notification = new NewCollection($category);
    $category->update(['name' => 'Outro nome']);

    $mail = unserialize(serialize($notification))->toMail($customer);

    expect($mail->subject)->toContain('Nova coleção: Verão');
    expect($mail->greeting)->toBe('Olá, Ana!');
    expect($mail->actionUrl)->toBe(route('shop.category', 'verao'));
    expect((string) $mail->render())->toContain('Descobrir coleção', 'Verão');
});
