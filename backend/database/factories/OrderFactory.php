<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\SellerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10, 500);
        $shippingFee = fake()->randomFloat(2, 1, 20);
        $tax = round($subtotal * 0.1, 2);
        $total = round($subtotal + $shippingFee + $tax, 2);

        return [
            'order_number' => 'ORD-' . now()->timestamp . '-' . fake()->numerify('####'),
            'buyer_id' => User::factory(),
            'seller_id' => SellerProfile::factory(),
            'status' => fake()->randomElement(['pending', 'accepted', 'shipped', 'delivered']),
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'tax' => $tax,
            'discount' => 0,
            'total' => $total,
            'payment_method' => fake()->randomElement(['cod', 'aba_payway']),
            'payment_status' => fake()->randomElement(['pending', 'paid']),
            'shipping_address' => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_phone' => fake()->phoneNumber(),
        ];
    }
}
