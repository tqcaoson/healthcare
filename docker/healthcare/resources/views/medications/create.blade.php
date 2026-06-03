@extends('layouts.app')
@section('page-title', 'Ghi nhận thuốc')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('medications.index') }}"
           class="d-flex align-items-center gap-1 text-secondary text-decoration-none small">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Danh sách
        </a>
        <span class="text-secondary">/</span>
        <h1 class="h5 fw-bold text-dark mb-0">Ghi nhận thuốc đã dùng</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
            <svg fill="currentColor" viewBox="0 0 20 20" width="20" height="20" class="flex-shrink-0 mt-1">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
            </svg>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('medications.store') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf

        {{-- Bệnh nhân & Encounter --}}
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#eff6ff,#eef2ff);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Bệnh nhân & Lần khám</h6>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem;">
                <div>
                    <label class="form-label fw-medium">Bệnh nhân <span class="text-danger">*</span></label>
                    <select id="patient_id" name="patient_id" class="form-select" required>
                        <option value="">-- Chọn bệnh nhân --</option>
                        @foreach($patients as $patient)
                            @php $d = $patient->currentDemographics; @endphp
                            <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $d?->family_name }} {{ $d?->given_name }} ({{ $patient->mrn }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Lần khám <span class="text-danger">*</span></label>
                        <select id="encounter_id" name="encounter_id" class="form-select" required>
                            <option value="">-- Chọn bệnh nhân trước --</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Lệnh thuốc <span class="text-danger">*</span></label>
                        <select id="order_id" name="order_id" class="form-select" required>
                            <option value="">-- Chọn bệnh nhân trước --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Thông tin thuốc --}}
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#fff7ed,#fffbeb);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-warning bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-warning">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Thông tin thuốc</h6>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Tên thuốc <span class="text-danger">*</span></label>
                        <input type="text" name="drug_name" value="{{ old('drug_name') }}"
                               class="form-control"
                               placeholder="vd: Paracetamol, Metformin" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Mã RxNorm</label>
                        <input type="text" name="rxnorm_code" value="{{ old('rxnorm_code') }}"
                               class="form-control" placeholder="vd: 161">
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Liều <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" name="dose_value" value="{{ old('dose_value') }}"
                               class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Đơn vị <span class="text-danger">*</span></label>
                        <input type="text" name="dose_unit" value="{{ old('dose_unit') }}"
                               class="form-control" placeholder="mg, mL, units" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Đường dùng <span class="text-danger">*</span></label>
                        <select name="route" class="form-select" required>
                            <option value="oral"    {{ old('route', 'oral') === 'oral'    ? 'selected' : '' }}>Uống (oral)</option>
                            <option value="IV"      {{ old('route') === 'IV'      ? 'selected' : '' }}>Tiêm tĩnh mạch (IV)</option>
                            <option value="IM"      {{ old('route') === 'IM'      ? 'selected' : '' }}>Tiêm bắp (IM)</option>
                            <option value="SC"      {{ old('route') === 'SC'      ? 'selected' : '' }}>Tiêm dưới da (SC)</option>
                            <option value="topical" {{ old('route') === 'topical' ? 'selected' : '' }}>Bôi ngoài da</option>
                            <option value="inhaled" {{ old('route') === 'inhaled' ? 'selected' : '' }}>Hít</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label fw-medium">Thời gian dùng <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="administered_at"
                           value="{{ old('administered_at', now()->format('Y-m-d\TH:i')) }}"
                           class="form-control" required>
                </div>

                <div class="form-check p-3 bg-light rounded-3" style="padding-left:1rem!important;">
                    <input type="checkbox" name="is_prn" id="is_prn" value="1" {{ old('is_prn') ? 'checked' : '' }}
                           class="form-check-input">
                    <label for="is_prn" class="form-check-label">
                        <span class="fw-medium">Thuốc PRN</span>
                        <span class="text-muted ms-1">— dùng khi cần thiết</span>
                    </label>
                </div>

                <div>
                    <label class="form-label fw-medium">Ghi chú</label>
                    <textarea name="notes" rows="2"
                              class="form-control"
                              placeholder="Ghi chú thêm nếu cần...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-1">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc</p>
            <div class="d-flex gap-2">
                <a href="{{ route('medications.index') }}" class="btn btn-outline-secondary">Huỷ</a>
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Lưu
                </button>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    var patientSel   = document.getElementById('patient_id');
    var encounterSel = document.getElementById('encounter_id');
    var orderSel     = document.getElementById('order_id');

    var allEncounters = @json($encountersJson);
    var allOrders     = @json($ordersJson);

    var oldEncounter = '{{ old('encounter_id') }}';
    var oldOrder     = '{{ old('order_id') }}';

    function rebuildSelect(sel, items, selectedId, emptyLabel) {
        sel.innerHTML = '';
        var ph = document.createElement('option');
        ph.value = '';
        ph.textContent = emptyLabel;
        sel.appendChild(ph);

        items.forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.label;
            if (String(item.id) === String(selectedId)) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    function onPatientChange(patientId, restoreEnc, restoreOrd) {
        if (!patientId) {
            rebuildSelect(encounterSel, [], null, '-- Chọn bệnh nhân trước --');
            rebuildSelect(orderSel,     [], null, '-- Chọn bệnh nhân trước --');
            return;
        }
        var encs = allEncounters.filter(function (e) { return e.patient_id === String(patientId); });
        var ords = allOrders.filter(function (o)     { return o.patient_id === String(patientId); });

        rebuildSelect(encounterSel, encs, restoreEnc || null, encs.length ? '-- Chọn encounter --' : 'Không có encounter phù hợp');
        rebuildSelect(orderSel,     ords, restoreOrd || null, ords.length ? '-- Chọn lệnh thuốc --' : 'Không có lệnh thuốc active/pending');
    }

    patientSel.addEventListener('change', function () {
        onPatientChange(this.value, null, null);
    });

    var savedPatient = '{{ old('patient_id') }}';
    if (savedPatient) {
        onPatientChange(savedPatient, oldEncounter, oldOrder);
    }
})();
</script>
@endsection
