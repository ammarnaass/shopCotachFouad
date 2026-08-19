@extends('admin.layout')

@section('title', $label ? __t('admin.shipping.label') . ': ' . $label->tracking_number : __t('admin.shipping.label_form_title'))

@section('content')
{{-- Breadcrumb --}}
<nav class="flex mb-6 text-sm text-gray-500">
    <a href="{{ route('admin.shipping.index', ['tab' => 'labels']) }}" class="hover:text-blue-600">{{ __t('admin.shipping.title') }}</a>
    <i class="fas fa-chevron-left mx-2 text-xs mt-1"></i>
    <span class="text-gray-800">{{ $label ? 'بوليصة: ' . $label->tracking_number : 'إنشاء بوليصة' }}</span>
</nav>

@if($label)
    {{-- Hero with status --}}
    <div class="mb-6 bg-gradient-to-l from-{{ $label->getStatusColor() }}-600 to-{{ $label->getStatusColor() }}-700 rounded-xl p-5 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold flex items-center gap-3">
                    <i class="fas fa-file-invoice text-3xl"></i>
                    بوليصة: {{ $label->tracking_number }}
                </h1>
                <p class="text-sm opacity-80 mt-1">الحالة: {{ $label->getStatusLabel() }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.shipping.label.pdf', $label) }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-semibold backdrop-blur-sm">
                    <i class="fas fa-file-pdf ml-1"></i> {{ __t('admin.shipping.download_label') }}
                </a>
                @if($label->status === 'pending')
                    <form action="{{ route('admin.shipping.label.updateStatus', $label) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="shipped">
                        <button type="submit" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-semibold backdrop-blur-sm">
                            <i class="fas fa-shipping-fast ml-1"></i> شحن الآن
                        </button>
                    </form>
                @elseif($label->status === 'shipped')
                    <form action="{{ route('admin.shipping.label.updateStatus', $label) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-semibold backdrop-blur-sm">
                            <i class="fas fa-check-circle ml-1"></i> تم التسليم
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Label Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Details Card --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <i class="fas fa-info-circle text-blue-600"></i> تفاصيل البوليصة
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ __t('admin.shipping.tracking_number') }}</div>
                        <div class="font-mono font-bold text-sm">{{ $label->tracking_number }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ __t('admin.shipping.carrier') }}</div>
                        <div class="font-bold text-sm">{{ $label->carrier?->name ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ __t('admin.shipping.weight') }}</div>
                        <div class="font-bold text-sm">{{ $label->weight ? $label->weight . ' كغ' : '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ __t('admin.shipping.cost') }}</div>
                        <div class="font-bold text-sm text-green-700">{{ number_format($label->cost, 2) }} {{ currentCurrencySymbol() }}</div>
                    </div>
                </div>

                @if($label->order)
                    <div class="mt-4 bg-blue-50 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs text-blue-600">{{ __t('admin.shipping.order') }}:</span>
                                <a href="{{ route('admin.orders.show', $label->order) }}" class="font-bold text-blue-700 hover:underline mr-2">#{{ $label->order->order_number }}</a>
                                <span class="text-xs text-gray-500">| {{ __t('admin.shipping.amount') }}: {{ number_format($label->order->grand_total, 2) }} {{ currentCurrencySymbol() }}</span>
                            </div>
                            <a href="{{ $label->getTrackingLink() }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-external-link-alt ml-1"></i> تتبع خارجي
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Tracking Timeline --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-lg flex items-center gap-2">
                        <i class="fas fa-route text-indigo-600"></i> سجل التتبع
                    </h2>
                    {{-- Add tracking update form --}}
                    <button type="button" onclick="document.getElementById('trackingForm').classList.toggle('hidden')" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-plus ml-1"></i> إضافة تحديث
                    </button>
                </div>

                {{-- Add tracking form --}}
                <div id="trackingForm" class="hidden bg-gray-50 rounded-lg p-4 mb-4">
                    <form action="{{ route('admin.shipping.label.tracking', $label) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs text-gray-600">{{ __t('admin.shipping.status') }}</label>
                                <select name="status" class="w-full border rounded px-3 py-2 text-sm" required>
                                    @foreach($statusOptions as $key => $label2)
                                        <option value="{{ $key }}">{{ $label2 }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">{{ __t('admin.shipping.location') }}</label>
                                <input type="text" name="location" class="w-full border rounded px-3 py-2 text-sm" placeholder="الرياض - مركز التوزيع">
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">{{ __t('admin.shipping.notes') }}</label>
                                <input type="text" name="description" class="w-full border rounded px-3 py-2 text-sm" placeholder="تم استلام الشحنة">
                            </div>
                        </div>
                        <button type="submit" class="mt-3 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                            <i class="fas fa-check ml-1"></i> إضافة
                        </button>
                    </form>
                </div>

                {{-- Timeline --}}
                <div class="space-y-0">
                    @forelse($label->trackingUpdates->sortByDesc('tracked_at') as $update)
                        <div class="flex gap-4 pb-4 relative">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center z-10">
                                    <i class="fas fa-map-pin text-indigo-600 text-xs"></i>
                                </div>
                                <div class="w-0.5 bg-gray-200 flex-1 mt-1"></div>
                            </div>
                            <div class="flex-1 pb-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-sm">{{ $update->getStatusLabel() }}</span>
                                    <span class="text-xs text-gray-400">{{ $update->tracked_at->format('Y-m-d H:i') }}</span>
                                </div>
                                @if($update->location)
                                    <div class="text-xs text-gray-500"><i class="fas fa-map-marker-alt ml-1"></i>{{ $update->location }}</div>
                                @endif
                                @if($update->description)
                                    <div class="text-xs text-gray-600 mt-1">{{ $update->description }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-route text-3xl mb-2"></i>
                            <p>{{ __t('admin.shipping.no_tracking_updates') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Status Actions --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-bold text-lg mb-4">{{ __t('admin.shipping.update_status') }}</h2>
                <form action="{{ route('admin.shipping.label.updateStatus', $label) }}" method="POST">
                    @csrf
                    <select name="status" class="w-full border rounded-lg px-4 py-2.5 mb-3 text-sm">
                        <option value="pending" {{ $label->status === 'pending' ? 'selected' : '' }}>{{ __t('admin.shipping.pending') }}</option>
                        <option value="printed" {{ $label->status === 'printed' ? 'selected' : '' }}>{{ __t('admin.shipping.printed') }}</option>
                        <option value="shipped" {{ $label->status === 'shipped' ? 'selected' : '' }}>{{ __t('admin.shipping.shipped') }}</option>
                        <option value="delivered" {{ $label->status === 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                        <option value="returned" {{ $label->status === 'returned' ? 'selected' : '' }}>{{ __t('admin.shipping.returned') }}</option>
                    </select>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg font-semibold text-sm">
                        <i class="fas fa-sync ml-1"></i> تحديث الحالة
                    </button>
                </form>
            </div>

            {{-- Timeline Info --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-bold text-lg mb-3">{{ __t('admin.shipping.time_info') }}</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __t('admin.shipping.created_at') }}</span>
                        <span>{{ $label->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    @if($label->shipped_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">{{ __t('admin.shipping.shipped_at') }}</span>
                            <span>{{ $label->shipped_at->format('Y-m-d H:i') }}</span>
                        </div>
                    @endif
                    @if($label->delivered_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">{{ __t('admin.shipping.delivered_at') }}</span>
                            <span>{{ $label->delivered_at->format('Y-m-d H:i') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __t('admin.shipping.tracking_updates') }}</span>
                        <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded text-xs">{{ $label->trackingUpdates->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- Create Label Form --}}
    <div class="mb-6 bg-gradient-to-l from-orange-600 to-red-600 rounded-xl p-5 text-white">
        <h1 class="text-2xl font-bold flex items-center gap-3">
            <i class="fas fa-file-invoice text-3xl"></i> إنشاء بوليصة شحن جديدة
        </h1>
    </div>

    <form action="{{ route('admin.shipping.label.store') }}" method="POST" class="max-w-2xl">
        @csrf
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __t('admin.shipping.order') }} *</label>
                <select name="order_id" class="w-full border rounded-lg px-4 py-2.5" required>
                    <option value="">- {{ __t('admin.shipping.select_order') }} -</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}">#{{ $order->order_number }} - {{ number_format($order->grand_total, 2) }} {{ currentCurrencySymbol() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __t('admin.shipping.carrier') }} *</label>
                <select name="carrier_id" class="w-full border rounded-lg px-4 py-2.5" required>
                    @foreach($carriers as $carrier)
                        <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __t('admin.shipping.weight_kg') }}</label>
                    <input type="number" name="weight" step="0.01" min="0" class="w-full border rounded-lg px-4 py-2.5" placeholder="0.5">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __t('admin.shipping.cost') }} ({{ currentCurrencySymbol() }}) *</label>
                    <input type="number" name="cost" step="0.01" min="0" class="w-full border rounded-lg px-4 py-2.5" required placeholder="25.00">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __t('admin.shipping.tracking_number_optional') }}</label>
                <input type="text" name="tracking_number" class="w-full border rounded-lg px-4 py-2.5" placeholder="{{ __t('admin.shipping.auto_generated') }}">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold">
                <i class="fas fa-save ml-1"></i> إنشاء البوليصة
            </button>
        </div>
    </form>
@endif
@endsection
