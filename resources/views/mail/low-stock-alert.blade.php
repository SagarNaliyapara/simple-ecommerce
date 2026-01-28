<x-mail::message>
# Low Stock Alert

The following products have fallen below the stock threshold:

<x-mail::table>
| Product | Remaining Stock |
|:--------|----------------:|
@foreach ($products as $product)
| {{ $product->name }} | {{ $product->stockQuantity }} |
@endforeach
</x-mail::table>

Please restock these items as soon as possible.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
