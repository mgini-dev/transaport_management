<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-3xl font-bold gradient-text">Fuel Requisition REQ-{{ str_pad($requisition->id, 6, '0', STR_PAD_LEFT) }}</h2>
                <p class="mt-2 text-sm text-slate-500">Full requisition details, workflow actions, and order context.</p>
            </div>
            <div class="flex items-center gap-2">
                @if($canViewOrder)
                    <a href="{{ route('orders.show', $requisition->order?->encrypted_id) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h6"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h4v4"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"></path>
                        </svg>
                        View Full Order
                    </a>
                @endif
                <a href="{{ route('fuel.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Requisitions
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $statusStyles = [
            'submitted' => 'bg-[var(--nmis-primary)]/10 text-[var(--nmis-primary)]',
            'supervisor_approved' => 'bg-[var(--nmis-secondary)]/10 text-[var(--nmis-secondary)]',
            'supervisor_rejected' => 'bg-rose-100 text-rose-700',
            'accountant_approved' => 'bg-[var(--nmis-accent)]/10 text-[var(--nmis-accent)]',
            'accountant_rejected' => 'bg-rose-100 text-rose-700',
        ];
    @endphp

    <div class="space-y-6" x-data="{ showDecisionModal: false, decisionAction: '', decisionApproved: '1', decisionTitle: '', decisionRequireRemarks: false }">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Status</p>
                <p class="mt-2">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusStyles[$requisition->status] ?? 'bg-slate-100 text-slate-700' }}">
                        {{ str_replace('_', ' ', ucfirst($requisition->status)) }}
                    </span>
                </p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Requester</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $requisition->requester?->name ?? '-' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Requested Litres</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ number_format((float) $requisition->additional_litres, 2) }} L</p>
            </div>
            <div class="rounded-xl border border-slate-200/60 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Total Amount</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">TSh {{ number_format((float) $requisition->total_amount, 2) }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Requisition Details</h3>
                <dl class="mt-4 grid gap-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Type</dt>
                        <dd class="font-medium text-slate-900">{{ ($requisition->requisition_type ?? 'order_based') === 'fleet_only' ? 'Fleet Without Order' : 'Fleet With Order' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Order</dt>
                        <dd class="font-medium text-slate-900">{{ $requisition->order?->order_number ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Fleet</dt>
                        <dd class="font-medium text-slate-900">{{ $requisition->fleet?->fleet_code ?? '-' }} - {{ $requisition->fleet?->plate_number ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Station</dt>
                        <dd class="font-medium text-slate-900">{{ $requisition->fuel_station }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Payment Channel</dt>
                        <dd class="font-medium text-slate-900">{{ $requisition->payment_channel }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Payment Account</dt>
                        <dd class="font-medium text-slate-900">{{ $requisition->payment_account }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Fuel Price</dt>
                        <dd class="font-medium text-slate-900">TSh {{ number_format((float) $requisition->fuel_price, 2) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Discount</dt>
                        <dd class="font-medium text-slate-900">TSh {{ number_format((float) $requisition->discount, 2) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Created</dt>
                        <dd class="font-medium text-slate-900">{{ $requisition->created_at?->format('d M Y, h:i A') }}</dd>
                    </div>
                    @if(($requisition->requisition_type ?? 'order_based') === 'fleet_only')
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Route</dt>
                            <dd class="font-medium text-right text-slate-900">{{ $requisition->origin_address ?? '-' }} to {{ $requisition->destination_address ?? '-' }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            <section class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Fuel Calculation Snapshot</h3>
                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Base Distance</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ number_format((float) ($requisition->base_distance_km ?? 0), 2) }} km</p>
                    </div>
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Additional Distance</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ number_format((float) ($requisition->additional_distance_km ?? 0), 2) }} km</p>
                    </div>
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Distance</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ number_format((float) ($requisition->total_distance_km ?? 0), 2) }} km</p>
                    </div>
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estimated Fuel</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ number_format((float) ($requisition->estimated_fuel_litres ?? 0), 2) }} L</p>
                    </div>
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fleet Balance</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ number_format((float) ($requisition->available_balance_litres ?? 0), 2) }} L</p>
                    </div>
                    <div class="rounded-lg border border-emerald-200/80 bg-emerald-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Requested Litres</p>
                        <p class="mt-1 font-semibold text-emerald-800">{{ number_format((float) $requisition->additional_litres, 2) }} L</p>
                    </div>
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Workflow Timeline</h3>
                <div class="mt-4 space-y-3">
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 p-3 text-sm">
                        <p class="font-medium text-slate-900">Submitted by {{ $requisition->requester?->name ?? 'N/A' }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $requisition->created_at?->format('d M Y, h:i A') }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 p-3 text-sm">
                        <p class="font-medium text-slate-900">Supervisor Review</p>
                        @if($requisition->supervisor)
                            <p class="mt-1 text-slate-700">{{ $requisition->supervisor->name }} on {{ $requisition->supervisor_reviewed_at?->format('d M Y, h:i A') }}</p>
                            @if($requisition->supervisor_remarks)
                                <p class="mt-1 text-xs text-slate-500">Remarks: {{ $requisition->supervisor_remarks }}</p>
                            @endif
                        @else
                            <p class="mt-1 text-xs text-amber-600">Pending supervisor review</p>
                        @endif
                    </div>
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 p-3 text-sm">
                        <p class="font-medium text-slate-900">Accounting Review</p>
                        @if($requisition->accountant)
                            <p class="mt-1 text-slate-700">{{ $requisition->accountant->name }} on {{ $requisition->accountant_reviewed_at?->format('d M Y, h:i A') }}</p>
                            @if($requisition->accountant_remarks)
                                <p class="mt-1 text-xs text-slate-500">Remarks: {{ $requisition->accountant_remarks }}</p>
                            @endif
                        @else
                            <p class="mt-1 text-xs text-amber-600">Pending accounting review</p>
                        @endif
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Required Action</h3>
                <div class="mt-4 space-y-4">
                    @if($canSupervisorAction)
                        <div class="rounded-lg border border-[var(--nmis-primary)]/20 bg-[var(--nmis-primary)]/5 p-4">
                            <p class="text-sm font-semibold text-[var(--nmis-primary)]">Supervisor Decision Required</p>
                            <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                                <button type="button"
                                        @click="decisionAction = '{{ route('fuel.supervisor.decision', $requisition->encrypted_id) }}'; decisionApproved = '1'; decisionTitle = 'Approve Requisition (Supervisor)'; decisionRequireRemarks = false; showDecisionModal = true"
                                        class="w-full rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-all">
                                    Approve and Forward to Accounting
                                </button>
                                <button type="button"
                                        @click="decisionAction = '{{ route('fuel.supervisor.decision', $requisition->encrypted_id) }}'; decisionApproved = '0'; decisionTitle = 'Reject Requisition (Supervisor)'; decisionRequireRemarks = true; showDecisionModal = true"
                                        class="w-full rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-700 transition-all">
                                    Reject Requisition
                                </button>
                            </div>
                        </div>
                    @elseif($canAccountantAction)
                        <div class="rounded-lg border border-[var(--nmis-secondary)]/20 bg-[var(--nmis-secondary)]/5 p-4">
                            <p class="text-sm font-semibold text-[var(--nmis-secondary)]">Accounting Decision Required</p>
                            <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                                <button type="button"
                                        @click="decisionAction = '{{ route('fuel.accountant.decision', $requisition->encrypted_id) }}'; decisionApproved = '1'; decisionTitle = 'Approve Requisition (Accounting)'; decisionRequireRemarks = false; showDecisionModal = true"
                                        class="w-full rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-all">
                                    Approve and Finalize
                                </button>
                                <button type="button"
                                        @click="decisionAction = '{{ route('fuel.accountant.decision', $requisition->encrypted_id) }}'; decisionApproved = '0'; decisionTitle = 'Reject Requisition (Accounting)'; decisionRequireRemarks = true; showDecisionModal = true"
                                        class="w-full rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-700 transition-all">
                                    Reject Requisition
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-600">No immediate action is required from your role for this requisition.</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        @if($requisition->order)
            <section class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Order Snapshot</h3>
                    @if($canViewOrder)
                        <a href="{{ route('orders.show', $requisition->order->encrypted_id) }}"
                           class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-[var(--nmis-primary)] hover:bg-[var(--nmis-primary)]/5 transition-all">
                            Open Full Order
                        </a>
                    @endif
                </div>
                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Order Number</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ $requisition->order->order_number }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Trip</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ $requisition->order->trip?->trip_number ?? '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ $requisition->order->customer?->name ?? '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200/70 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ ucfirst($requisition->order->status) }}</p>
                    </div>
                </div>
                <div class="mt-4 rounded-lg border border-slate-200/70 bg-slate-50 p-4 text-sm text-slate-700">
                    <p class="font-semibold text-slate-900">Route</p>
                    <p class="mt-1">{{ $requisition->order->origin_address }}</p>
                    <p class="my-1 text-xs font-semibold uppercase tracking-wide text-slate-400">to</p>
                    <p>{{ $requisition->order->destination_address }}</p>
                </div>
            </section>
        @endif

        <div x-show="showDecisionModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showDecisionModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    <div class="flex items-center justify-between border-b border-slate-200/60 px-6 py-4">
                        <h4 class="text-lg font-semibold text-slate-900" x-text="decisionTitle"></h4>
                        <button type="button" @click="showDecisionModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <form :action="decisionAction" method="POST" class="p-6">
                        @csrf
                        <input type="hidden" name="approved" :value="decisionApproved">
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            Remarks <span class="text-slate-400">(Optional for approval, required for rejection)</span>
                        </label>
                        <textarea name="remarks"
                                  rows="8"
                                  :required="decisionRequireRemarks"
                                  class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 text-sm focus:border-[var(--nmis-primary)] focus:ring-2 focus:ring-[var(--nmis-primary)]/20 transition-all"
                                  placeholder="Write detailed remarks here..."></textarea>
                        <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200/60 pt-4">
                            <button type="button"
                                    @click="showDecisionModal = false"
                                    class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                                Cancel
                            </button>
                            <button type="submit"
                                    :class="decisionApproved === '1' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
                                    class="rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all">
                                Submit Decision
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
