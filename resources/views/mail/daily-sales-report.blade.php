<x-mail::message>
# Daily Sales Report — {{ $date->toFormattedDateString() }}

**Total Orders:** {{ $orders->count() }}<br>
**Total Revenue:** ${{ number_format($orders->sum('total_amount'), 2) }}

@if ($orders->isNotEmpty())
## Order Breakdown

<x-mail::table>
| Order ID | Items | Total |
|:---------|------:|------:|
@foreach ($orders as $order)
| #{{ $order->id }} | {{ $order->orderItems->sum('quantity') }} | ${{ number_format($order->total_amount, 2) }} |
@endforeach
</x-mail::table>

## Product Breakdown

<x-mail::table>
| Product | Qty Sold |
|:--------|---------:|
@foreach ($orders->flatMap->orderItems->groupBy('product_id') as $items)
| {{ $items->first()->product->name ?? 'N/A' }} | {{ $items->sum('quantity') }} |
@endforeach
</x-mail::table>
@else
No orders were placed on this date.
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
