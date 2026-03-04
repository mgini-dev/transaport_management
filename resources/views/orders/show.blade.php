<x-app-layout>
    @php
        $statusClasses = [
            'created' => 'bg-slate-100 text-slate-700',
            'processing' => 'bg-amber-100 text-amber-700',
            'assigned' => 'bg-blue-100 text-blue-700',
            'transportation' => 'bg-indigo-100 text-indigo-700',
            'incomplete' => 'bg-rose-100 text-rose-700',
            'completed' => 'bg-emerald-100 text-emerald-700',
        ];
        $statusClass = $statusClasses[$order->status] ?? 'bg-slate-100 text-slate-700';

        $documentSize = null;
        if (is_int($completionDocumentPreview['size_bytes'] ?? null)) {
            $bytes = (int) $completionDocumentPreview['size_bytes'];
            if ($bytes >= 1024 * 1024) {
                $documentSize = number_format($bytes / (1024 * 1024), 2).' MB';
            } elseif ($bytes >= 1024) {
                $documentSize = number_format($bytes / 1024, 2).' KB';
            } else {
                $documentSize = $bytes.' bytes';
            }
        }
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Order {{ $order->order_number }}</h2>
                <p class="mt-2 text-sm text-slate-500">Full order details, delivery note preview, and download actions.</p>
            </div>
            <a href="{{ route('orders.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition-all hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Orders
            </a>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ previewDeliveryNoteModal: false }">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-semibold">Please review the following:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Status</p>
                <p class="mt-2">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Trip</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $order->trip?->trip_number ?? '-' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Weight</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ number_format((float) $order->weight_tons, 2) }} t</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Agreed Price</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ number_format((float) $order->agreed_price, 2) }}</p>
            </div>
        </div>

        <div id="transport-actions" class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Transport Actions</h3>
                    <p class="mt-1 text-sm text-slate-500">Issue the delivery note, then upload signed proof and fuel balances to complete.</p>
                </div>
                @if($canDownloadDeliveryNote)
                    <div class="flex flex-col items-end gap-1">
                        <a href="{{ route('orders.delivery_note.pdf', $order->encrypted_id) }}"
                           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-[var(--nmis-primary)] transition-all hover:bg-[var(--nmis-primary)]/5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M4 16.5A2.5 2.5 0 006.5 19h11a2.5 2.5 0 002.5-2.5v-9A2.5 2.5 0 0017.5 5h-1.379a1 1 0 01-.707-.293l-1.414-1.414A1 1 0 0013.293 3h-2.586a1 1 0 00-.707.293L8.586 4.707A1 1 0 017.879 5H6.5A2.5 2.5 0 004 7.5v9z"></path>
                            </svg>
                            Download Delivery Note (PDF)
                        </a>
                        @if($order->delivery_note_issued_at)
                            <p class="text-[11px] text-slate-500">
                                First issued {{ $order->delivery_note_issued_at->format('d M Y, h:i A') }}
                                by {{ $order->deliveryNoteIssuer?->name ?? 'N/A' }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            @if($order->status === 'transportation' && $canCompleteTransportation)
                <form method="POST" action="{{ route('orders.complete_transport', $order->encrypted_id) }}" enctype="multipart/form-data" class="mt-5 grid gap-4 rounded-xl border border-indigo-100 bg-indigo-50/40 p-4 md:grid-cols-2">
                    @csrf
                    <div class="md:col-span-2 rounded-lg border border-slate-200 bg-white/70 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Fuel Balance Per Fleet</p>
                        <div class="mt-2 space-y-3">
                            @foreach($orderFleetOptions as $idx => $fleet)
                                <div class="grid gap-2 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Fleet {{ $idx + 1 }}</label>
                                        <input type="hidden" name="fleet_balances[{{ $idx }}][fleet_id]" value="{{ $fleet->encrypted_id }}">
                                        <input type="text" value="{{ $fleet->fleet_code }} - {{ $fleet->plate_number }}" readonly
                                               class="w-full rounded-lg border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-700">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Remaining Fuel (L)</label>
                                        <input type="number" step="0.01" min="0" name="fleet_balances[{{ $idx }}][remaining_litres]" required
                                               class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm focus:border-[var(--nmis-primary)] focus:ring-1 focus:ring-[var(--nmis-primary)]/20"
                                               placeholder="0.00">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Completion Comment</label>
                        <textarea name="completion_comment" rows="4"
                                  class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm focus:border-[var(--nmis-primary)] focus:ring-1 focus:ring-[var(--nmis-primary)]/20"
                                  placeholder="Any comment about transport completion"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Signed Delivery Note (Required)</label>
                        <input type="file" name="signed_delivery_note" required accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                        <p class="mt-1 text-xs text-slate-500">Allowed formats: PDF, JPG, JPEG, PNG (max 5MB).</p>
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white transition-all hover:bg-indigo-700">
                            Mark Completed
                        </button>
                    </div>
                </form>
            @elseif($order->status === 'transportation')
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    This order is in transportation. You do not have permission to complete it.
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Signed Delivery Note</h3>
                    <p class="mt-1 text-sm text-slate-500">Preview uploaded proof and download directly from this section.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if($completionDocumentPreview['can_preview'])
                        <button type="button"
                                @click="previewDeliveryNoteModal = true"
                                class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-xs font-semibold text-indigo-700 transition-all hover:bg-indigo-100">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Preview Uploaded Note
                        </button>
                    @endif
                    @if($completionDocumentPreview['download_url'])
                        <a href="{{ $completionDocumentPreview['download_url'] }}"
                           class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition-all hover:bg-emerald-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"></path>
                            </svg>
                            Download File
                        </a>
                    @endif
                </div>
            </div>

            @if(! $completionDocumentPreview['has_document'])
                <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">No signed delivery note has been uploaded yet.</div>
            @elseif($completionDocumentPreview['file_missing'])
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">A document path exists, but the file is missing from storage.</div>
            @elseif($completionDocumentPreview['can_preview'])
                <div class="mt-4 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">Preview is ready. Click <span class="font-semibold">Preview Uploaded Note</span> to open it in a modal.</div>
            @else
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">Preview is not supported for this file type. Use download to view it.</div>
            @endif

            @if($completionDocumentPreview['has_document'] && ! $completionDocumentPreview['file_missing'])
                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</p><p class="mt-1 truncate font-medium text-slate-800">{{ $completionDocumentPreview['filename'] ?? '-' }}</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Type</p><p class="mt-1 font-medium text-slate-800">{{ strtoupper($completionDocumentPreview['extension'] ?? 'n/a') }}</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">MIME</p><p class="mt-1 truncate font-medium text-slate-800">{{ $completionDocumentPreview['mime_type'] ?? '-' }}</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Size</p><p class="mt-1 font-medium text-slate-800">{{ $documentSize ?? '-' }}</p></div>
                </div>
            @endif
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Order Details</h3>
                <dl class="mt-4 grid gap-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Customer</dt><dd class="font-medium text-slate-900">{{ $order->customer?->name ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Contact Person</dt><dd class="font-medium text-slate-900">{{ $order->customer?->contact_person ?: '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Phone</dt><dd class="font-medium text-slate-900">{{ $order->customer?->phone ?: '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-900">{{ $order->customer?->email ?: '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Cargo Type</dt><dd class="font-medium text-slate-900">{{ $order->cargo_type }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Cargo Description</dt><dd class="text-right font-medium text-slate-900">{{ $order->cargo_description ?: '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Estimated Fuel</dt><dd class="font-medium text-slate-900">{{ $order->estimated_fuel_litres ? number_format((float) $order->estimated_fuel_litres, 2).' L' : '-' }}</dd></div>
                    @if($canViewDistance)
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Distance</dt><dd class="font-medium text-slate-900">{{ $order->distance_km ? number_format((float) $order->distance_km, 2).' km' : 'Not calculated' }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Expected Loading</dt><dd class="font-medium text-slate-900">{{ $order->expected_loading_date?->format('d M Y') ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Expected Leaving</dt><dd class="font-medium text-slate-900">{{ $order->expected_leaving_date?->format('d M Y') ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Created By</dt><dd class="font-medium text-slate-900">{{ $order->creator?->name ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Created On</dt><dd class="font-medium text-slate-900">{{ $order->created_at?->format('d M Y, h:i A') ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Completed By</dt><dd class="font-medium text-slate-900">{{ $order->completer?->name ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Completed On</dt><dd class="font-medium text-slate-900">{{ $order->completed_at?->format('d M Y, h:i A') ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Delivery Note Issued</dt><dd class="text-right font-medium text-slate-900">@if($order->delivery_note_issued_at){{ $order->delivery_note_issued_at->format('d M Y, h:i A') }} by {{ $order->deliveryNoteIssuer?->name ?? 'N/A' }}@else-@endif</dd></div>
                </dl>
                <div class="mt-4 border-t border-slate-200/70 pt-4 text-sm">
                    <p class="font-semibold text-slate-700">Route</p>
                    <p class="mt-1 text-slate-600">{{ $order->origin_address }}</p>
                    <p class="my-1 text-xs font-medium uppercase tracking-wide text-slate-400">to</p>
                    <p class="text-slate-600">{{ $order->destination_address }}</p>
                </div>
                <div class="mt-4 border-t border-slate-200/70 pt-4 text-sm">
                    <p class="font-semibold text-slate-700">Remarks</p>
                    <p class="mt-1 text-slate-600">{{ $order->remarks ?: 'No remarks provided.' }}</p>
                </div>
                @if($order->completion_comment)
                    <div class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                        <span class="font-semibold">Completion comment:</span> {{ $order->completion_comment }}
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-900">Status History</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($order->statusHistory as $history)
                            <div class="rounded-lg border border-slate-200/70 bg-slate-50 p-3">
                                <p class="text-sm font-medium text-slate-900">{{ ucfirst($history->from_status ?? 'N/A') }} to {{ ucfirst($history->to_status) }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $history->changedBy?->name ?? 'System' }} on {{ $history->created_at?->format('d M Y, h:i A') }}</p>
                                @if($history->remarks)<p class="mt-2 text-sm text-slate-700">{{ $history->remarks }}</p>@endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No status updates recorded yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-900">Fuel and Fleet Summary</h3>
                    <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fuel Requisitions</p><p class="mt-1 font-semibold text-slate-900">{{ $order->fuelRequisitions->count() }}</p></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fuel Balance Records</p><p class="mt-1 font-semibold text-slate-900">{{ $order->fuelBalances->count() }}</p></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 sm:col-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned Legs</p><p class="mt-1 font-semibold text-slate-900">{{ $order->legs->count() }}</p></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="border-b border-slate-200/70 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-900">Assigned Legs</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Seq</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Route</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fleet</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Driver</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($order->legs as $leg)
                            <tr>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $leg->leg_sequence }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $leg->origin_address }} to {{ $leg->destination_address }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $leg->fleet?->fleet_code ?? $leg->fleet?->plate_number ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $leg->driver?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ ucfirst($leg->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">No legs assigned for this order yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200/60 bg-white shadow-sm">
            <div class="border-b border-slate-200/70 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-900">Fuel Requisition Details</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Requested</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fleet and Route</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fuel Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Financials</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Approval</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($order->fuelRequisitions as $requisition)
                            <tr>
                                <td class="px-6 py-4 text-sm text-slate-700 align-top">
                                    <p class="font-medium text-slate-900">{{ $requisition->requester?->name ?? 'N/A' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $requisition->created_at?->format('d M Y, h:i A') ?? '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Type: {{ ucwords(str_replace('_', ' ', $requisition->requisition_type ?? 'order_based')) }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 align-top">
                                    <p class="font-medium text-slate-900">{{ $requisition->fleet?->fleet_code ?? $requisition->fleet?->plate_number ?? '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Station: {{ $requisition->fuel_station ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $requisition->origin_address ?: $order->origin_address }} to {{ $requisition->destination_address ?: $order->destination_address }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 align-top">
                                    <p><span class="font-medium text-slate-900">Additional:</span> {{ number_format((float) ($requisition->additional_litres ?? 0), 2) }} L</p>
                                    <p class="mt-1 text-xs text-slate-500">Estimated: {{ $requisition->estimated_fuel_litres !== null ? number_format((float) $requisition->estimated_fuel_litres, 2).' L' : '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Balance: {{ $requisition->available_balance_litres !== null ? number_format((float) $requisition->available_balance_litres, 2).' L' : '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Distance: {{ $requisition->total_distance_km !== null ? number_format((float) $requisition->total_distance_km, 2).' km' : '-' }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 align-top">
                                    <p><span class="font-medium text-slate-900">Price:</span> {{ number_format((float) ($requisition->fuel_price ?? 0), 2) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Discount: {{ number_format((float) ($requisition->discount ?? 0), 2) }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-900">Total: {{ number_format((float) ($requisition->total_amount ?? 0), 2) }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 align-top">
                                    <p class="font-medium text-slate-900">{{ $requisition->payment_channel ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $requisition->payment_account ?: '-' }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 align-top">
                                    <p class="font-medium text-slate-900">{{ ucwords(str_replace('_', ' ', $requisition->status)) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Supervisor: {{ $requisition->supervisor?->name ?? '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Accountant: {{ $requisition->accountant?->name ?? '-' }}</p>
                                    @if($requisition->supervisor_remarks)
                                        <p class="mt-1 text-xs text-slate-500">Sup. note: {{ $requisition->supervisor_remarks }}</p>
                                    @endif
                                    @if($requisition->accountant_remarks)
                                        <p class="mt-1 text-xs text-slate-500">Acc. note: {{ $requisition->accountant_remarks }}</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">No fuel requisitions found for this order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($completionDocumentPreview['can_preview'])
            <div x-cloak
                 x-show="previewDeliveryNoteModal"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" @click="previewDeliveryNoteModal = false"></div>
                <div class="relative z-10 w-full max-w-5xl rounded-2xl border border-slate-200 bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                        <h4 class="text-sm font-semibold text-slate-900">Signed Delivery Note Preview</h4>
                        <div class="flex items-center gap-2">
                            @if($completionDocumentPreview['download_url'])
                                <a href="{{ $completionDocumentPreview['download_url'] }}"
                                   class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition-all hover:bg-emerald-700">
                                    Download
                                </a>
                            @endif
                            <button type="button"
                                    @click="previewDeliveryNoteModal = false"
                                    class="inline-flex items-center rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-50">
                                Close
                            </button>
                        </div>
                    </div>
                    <div class="max-h-[80vh] overflow-auto bg-slate-100 p-3">
                        @if($completionDocumentPreview['preview_kind'] === 'pdf')
                            <iframe src="{{ $completionDocumentPreview['preview_url'] }}"
                                    title="Signed delivery note modal preview for {{ $order->order_number }}"
                                    class="h-[72vh] w-full rounded-lg border border-slate-200 bg-white"></iframe>
                        @else
                            <div class="flex min-h-[20rem] items-center justify-center">
                                <img src="{{ $completionDocumentPreview['preview_url'] }}"
                                     alt="Signed delivery note modal preview for {{ $order->order_number }}"
                                     class="max-h-[72vh] w-auto rounded-lg border border-slate-200 bg-white object-contain shadow-sm">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--nmis-primary), var(--nmis-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</x-app-layout>
