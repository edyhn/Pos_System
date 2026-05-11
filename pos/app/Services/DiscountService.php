<?php

namespace App\Services;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Collection;

class DiscountService
{
    public function getApplicableDiscounts(int $storeId, array $cart): Collection
    {
        $discounts = Discount::active()->byStore($storeId)
            ->orderBy('priority')
            ->orderBy('stackable')
            ->get();

        $total = array_sum(array_column($cart, 'subtotal'));

        return $discounts->filter(function (Discount $discount) use ($cart, $total) {
            if ($discount->min_purchase && $total < $discount->min_purchase) {
                return false;
            }

            return true;
        })->values();
    }

    public function calculateItemDiscount(array $item, Collection $applicableDiscounts): array
    {
        $discountAmount = 0;
        $appliedDiscounts = [];

        foreach ($applicableDiscounts as $discount) {
            if (!$discount->appliesToProduct((object) ['id' => $item['product_id']])) {
                continue;
            }

            $itemDiscount = $discount->calculateDiscount($item['subtotal'], $item['quantity']);

            if ($itemDiscount > 0) {
                $discountAmount += $itemDiscount;
                $appliedDiscounts[] = [
                    'discount_id' => $discount->id,
                    'discount_name' => $discount->name,
                    'amount' => $itemDiscount,
                ];

                if (!$discount->stackable) {
                    break;
                }
            }
        }

        $discountAmount = min($discountAmount, $item['subtotal']);

        return [
            'discount_amount' => $discountAmount,
            'discounted_subtotal' => $item['subtotal'] - $discountAmount,
            'applied_discounts' => $appliedDiscounts,
        ];
    }

    public function applyDiscountsToCart(array $cart, int $storeId): array
    {
        $applicableDiscounts = $this->getApplicableDiscounts($storeId, $cart);

        if ($applicableDiscounts->isEmpty()) {
            return $cart;
        }

        return array_map(function ($item) use ($applicableDiscounts) {
            $discountInfo = $this->calculateItemDiscount($item, $applicableDiscounts);
            $item['discount_amount'] = $discountInfo['discount_amount'];
            $item['discounted_subtotal'] = $discountInfo['discounted_subtotal'];
            $item['applied_discounts'] = $discountInfo['applied_discounts'];
            return $item;
        }, $cart);
    }
}
