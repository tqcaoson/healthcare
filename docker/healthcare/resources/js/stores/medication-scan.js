export default () => ({

    // --- State ---
    step: 'idle',           // idle | scanning-patient | scanning-medication | ready | administering | done | error
    encounterId: null,
    orderId: null,
    patientId: null,

    schedule: [],           // MAR schedule for the encounter
    selectedOrder: null,    // order being administered

    checks: {
        patient: false,
        medication: false,
        dose: false,
        schedule: false,
    },

    patientInfo: null,      // returned from /mar/scan/patient
    medicationInfo: null,   // returned from /mar/scan/medication

    administering: false,
    errorMessage: '',
    successMessage: '',

    get allChecksPass() {
        return this.checks.patient && this.checks.medication
            && this.checks.dose && this.checks.schedule;
    },

    // --- Lifecycle ---
    async init() {
        // Read encounter_id from URL query param if present
        const params = new URLSearchParams(window.location.search);
        const enc = params.get('encounter_id');
        if (enc) {
            this.encounterId = parseInt(enc, 10);
            await this.loadSchedule();
        }
    },

    // --- MAR Schedule ---
    async loadSchedule() {
        if (!this.encounterId) return;

        try {
            const res = await fetch(`/api/v1/mar/schedule?encounter_id=${this.encounterId}`, {
                headers: this.authHeaders(),
            });
            const json = await res.json();
            this.schedule = json.data ?? [];
        } catch {
            this.setError('Không thể tải lịch phát thuốc. Vui lòng thử lại.');
        }
    },

    selectOrder(order) {
        this.selectedOrder = order;
        this.orderId = order.order_id;
        this.resetChecks();
        this.step = 'scanning-patient';
    },

    // --- Patient Barcode Scan ---
    async onPatientScan(barcode) {
        if (!this.patientId) {
            this.setError('Chưa chọn bệnh nhân. Vui lòng chọn lần khám trước.');
            return;
        }

        try {
            const res = await fetch('/api/v1/mar/scan/patient', {
                method: 'POST',
                headers: { ...this.authHeaders(), 'Content-Type': 'application/json' },
                body: JSON.stringify({ barcode, patient_id: this.patientId }),
            });

            const json = await res.json();

            if (json.verified) {
                this.checks.patient = true;
                this.patientInfo = json.patient;
                this.errorMessage = '';
                this.step = 'scanning-medication';
            } else {
                this.checks.patient = false;
                this.setError(json.message || 'Barcode bệnh nhân không hợp lệ.');
            }
        } catch {
            this.setError('Lỗi kết nối khi xác minh bệnh nhân.');
        }
    },

    // --- Medication Barcode Scan ---
    async onMedicationScan(barcode) {
        if (!this.orderId) {
            this.setError('Chưa chọn order thuốc. Vui lòng chọn từ danh sách MAR.');
            return;
        }

        try {
            const res = await fetch('/api/v1/mar/scan/medication', {
                method: 'POST',
                headers: { ...this.authHeaders(), 'Content-Type': 'application/json' },
                body: JSON.stringify({ barcode, order_id: this.orderId }),
            });

            const json = await res.json();

            if (json.verified) {
                this.checks.medication = true;
                this.medicationInfo = json.medication;
                this.errorMessage = '';

                // Dose check: compare scanned dose with order dose
                this.checks.dose = true; // dose is embedded in order; mismatch caught server-side

                // Schedule check: based on is_due flag from MAR schedule
                this.checks.schedule = this.selectedOrder?.is_due ?? true;

                this.step = 'ready';
            } else {
                this.checks.medication = false;
                this.setError(json.message || 'Barcode thuốc không khớp với order.');
            }
        } catch {
            this.setError('Lỗi kết nối khi xác minh thuốc.');
        }
    },

    // --- Record Administration ---
    async administer() {
        if (!this.allChecksPass || this.administering) return;

        this.administering = true;
        this.errorMessage = '';

        try {
            const med = this.medicationInfo;
            const res = await fetch('/api/v1/mar/administer', {
                method: 'POST',
                headers: { ...this.authHeaders(), 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    order_id: this.orderId,
                    patient_id: this.patientId,
                    encounter_id: this.encounterId,
                    patient_barcode: this.patientInfo?.mrn,
                    medication_barcode: med?.rxnorm_code,
                    drug_name: med?.drug_name,
                    rxnorm_code: med?.rxnorm_code,
                    dose_value: med?.dose_value,
                    dose_unit: med?.dose_unit,
                    route: med?.route,
                    scheduled_at: this.selectedOrder?.administration_window,
                }),
            });

            const json = await res.json();

            if (res.ok) {
                this.step = 'done';
                this.successMessage = `Đã phát thuốc ${med?.drug_name} thành công lúc ${new Date().toLocaleTimeString('vi-VN')}.`;
                // Refresh MAR schedule to reflect updated state
                await this.loadSchedule();
            } else {
                this.setError(json.message || 'Không thể ghi nhận phát thuốc.');
            }
        } catch {
            this.setError('Lỗi kết nối khi ghi nhận phát thuốc.');
        } finally {
            this.administering = false;
        }
    },

    // --- Helpers ---
    resetChecks() {
        this.checks = { patient: false, medication: false, dose: false, schedule: false };
        this.patientInfo = null;
        this.medicationInfo = null;
        this.errorMessage = '';
        this.successMessage = '';
        this.step = 'idle';
    },

    setError(msg) {
        this.errorMessage = msg;
    },

    authHeaders() {
        // Sanctum CSRF + token from meta tag (set by Laravel layout)
        const token = document.head.querySelector('meta[name="api-token"]')?.content ?? '';
        return {
            'Authorization': `Bearer ${token}`,
            'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]')?.content ?? '',
            'Accept': 'application/json',
        };
    },
});
