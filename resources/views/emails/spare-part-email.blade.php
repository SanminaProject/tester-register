<p>{{ $messageBody }}</p>

<hr>

<p><strong>Spare Part:</strong> {{ $sparePart->name }}</p>
<p><strong>Manufacturer Part Number:</strong> {{ $sparePart->manufacturer_part_number }}</p>
<p><strong>Quantity In Stock:</strong> {{ $sparePart->quantity_in_stock }}</p>
<p><strong>Reorder Level:</strong> {{ $sparePart->reorder_level }}</p>
<p><strong>Unit Price:</strong> {{ $sparePart->unit_price }}</p>
<p><strong>Last Order Date:</strong> {{ $sparePart->last_order_date }}</p>
<p><strong>Tester:</strong> {{ $sparePart->tester?->name ?? 'N/A' }}</p>
<p><strong>Supplier:</strong> {{ $sparePart->supplier?->supplier_name ?? 'N/A' }}</p>