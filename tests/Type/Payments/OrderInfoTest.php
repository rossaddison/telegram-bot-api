<?php

declare(strict_types=1);

namespace Type\Payments;

use PHPUnit\Framework\TestCase;
use Vjik\TelegramBot\Api\Type\Payments\OrderInfo;
use Vjik\TelegramBot\Api\Type\Payments\ShippingAddress;

final class OrderInfoTest extends TestCase
{
    public function testOrderInfo(): void
    {
        $orderInfo = new OrderInfo();

        $this->assertNull($orderInfo->name);
        $this->assertNull($orderInfo->phoneNumber);
        $this->assertNull($orderInfo->email);
        $this->assertNull($orderInfo->shippingAddress);
    }

    public function testFromTelegramResult(): void
    {
        $orderInfo = OrderInfo::fromTelegramResult([
            'name' => 'name',
            'phone_number' => 'phone_number',
            'email' => 'email',
            'shipping_address' => [
                'country_code' => 'country_code',
                'state' => 'state',
                'city' => 'city',
                'street_line1' => 'street_line1',
                'street_line2' => 'street_line2',
                'post_code' => 'post_code',
            ],
        ]);

        $this->assertSame('name', $orderInfo->name);
        $this->assertSame('phone_number', $orderInfo->phoneNumber);
        $this->assertSame('email', $orderInfo->email);

        $this->assertInstanceOf(ShippingAddress::class, $orderInfo->shippingAddress);
        $this->assertSame('country_code', $orderInfo->shippingAddress->countryCode);
        $this->assertSame('state', $orderInfo->shippingAddress->state);
        $this->assertSame('city', $orderInfo->shippingAddress->city);
        $this->assertSame('street_line1', $orderInfo->shippingAddress->streetLine1);
        $this->assertSame('street_line2', $orderInfo->shippingAddress->streetLine2);
        $this->assertSame('post_code', $orderInfo->shippingAddress->postCode);
    }
}
