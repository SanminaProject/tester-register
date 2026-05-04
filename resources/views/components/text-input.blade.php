@props(['disabled' => false])

@php
	$type = $attributes->get('type') ?? 'text';
@endphp

@if($type === 'number')
<div class="number-with-buttons relative">
	<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-light-grey border-none text-black focus:border-none focus:ring-gray-100 rounded-[30px] shadow-sm pr-24']) }}>

	<div class="absolute right-1 top-1/2 -translate-y-1/2 flex space-x-1 items-center">
		<button type="button" data-action="dec" class="w-9 h-9 bg-[#efe6e8] rounded-full flex items-center justify-center text-lg text-gray-800" onclick="(function(btn){const wrap=btn.closest('.number-with-buttons'); if(!wrap) return; const input=wrap.querySelector('input[type=number]'); if(!input) return; const step=Number(input.step) || 1; const min = input.min !== '' ? Number(input.min) : null; let val = (input.value === '' ? 0 : Number(input.value)) - step; if(min !== null && val < min) val = min; input.value = val; input.dispatchEvent(new Event('input')); })(this)">-</button>

		<button type="button" data-action="inc" class="w-9 h-9 bg-[#efe6e8] rounded-full flex items-center justify-center text-lg text-gray-800" style="margin-right: -6px" onclick="(function(btn){const wrap=btn.closest('.number-with-buttons'); if(!wrap) return; const input=wrap.querySelector('input[type=number]'); if(!input) return; const step=Number(input.step) || 1; const max = input.max !== '' ? Number(input.max) : null; let val = (input.value === '' ? 0 : Number(input.value)) + step; if(max !== null && val > max) val = max; input.value = val; input.dispatchEvent(new Event('input')); })(this)">+</button>
	</div>

	<style>
		.number-with-buttons input[type="number"]::-webkit-outer-spin-button,
		.number-with-buttons input[type="number"]::-webkit-inner-spin-button {
			-webkit-appearance: none;
			margin: 0;
		}
		.number-with-buttons input[type="number"] {
			-moz-appearance: textfield;
		}
		.number-with-buttons .w-9.h-9 {
			box-shadow: none;
		}
	</style>
</div>
@else
<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-light-grey border-none text-black focus:border-none focus:ring-gray-100 rounded-[30px] shadow-sm']) }}>
@endif