<x-mail::message>
# Daily Sales Report — {{ $date->toFormattedDateString() }}

**Total Orders:** {{ $orders->count() }}<br>
**Total Revenue:** ${{ number_format($orders->sum('totalAmount'), 2) }}

@if ($orders->isNotEmpty())
## Order Breakdown

<x-mail::table>
| Order ID | Items | Total |
|:---------|------:|------:|
@foreach ($orders as $order)
| #{{ $order->id }} | {{ $order->items->sum('quantity') }} | ${{ number_format($order->totalAmount, 2) }} |
@endforeach
</x-mail::table>

## Product Breakdown

<x-mail::table>
| Product | Qty Sold |
|:--------|---------:|
@foreach ($orders->flatMap(fn($o) => $o->items)->groupBy('productId') as $items)
| {{ $items->first()->productName ?? 'N/A' }} | {{ $items->sum('quantity') }} |
@endforeach
</x-mail::table>
@else
No orders were placed on this date.
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
