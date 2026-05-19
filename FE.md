# FRONTEND.md — Healthcare Information System UI/UX

## Tổng quan

Frontend cho hệ thống y tế Laravel, xây dựng với **Alpine.js + Blade templates** (web), và **React** (advanced dashboards). Hệ thống phục vụ các role: Clinician (bác sĩ), Nurse (y tá), Pharmacist (dược sĩ), Admin, và Patient Portal.

---

## Tech Stack Frontend

```
Web Interface:      Blade templates + Alpine.js + Tailwind CSS
Mobile:             React Native / React (responsive)
State Management:   Alpine store / React hooks
UI Framework:       Tailwind CSS 3.x + custom component library
Real-time Updates:  Laravel Echo + WebSocket (Pusher / Redis)
HTTP Client:        Axios + custom interceptors for JWT
Forms:              Laravel Livewire (dynamic forms) + custom validation
Charts/Reports:     Chart.js, ECharts, Recharts (React)
Accessibility:      WCAG 2.1 AA compliance
```

---

## Kiến trúc Components

```
frontend/
├── resources/
│   ├── views/                           # Blade templates
│   │   ├── layouts/
│   │   │   ├── app.blade.php           # Main layout
│   │   │   ├── auth.blade.php          # Auth layout
│   │   │   └── downtime.blade.php      # Downtime read-only mode
│   │   │
│   │   ├── auth/
│   │   │   ├── login.blade.php
│   │   │   ├── mfa-verify.blade.php
│   │   │   └── password-reset.blade.php
│   │   │
│   │   ├── dashboard/
│   │   │   ├── index.blade.php         # Role-based dashboard
│   │   │   ├── clinician.blade.php
│   │   │   ├── nurse.blade.php
│   │   │   ├── pharmacist.blade.php
│   │   │   └── admin.blade.php
│   │   │
│   │   ├── patients/
│   │   │   ├── index.blade.php         # Patient search & list
│   │   │   ├── show.blade.php          # Patient detail view
│   │   │   ├── create.blade.php        # New patient registration
│   │   │   ├── edit-demographics.blade.php
│   │   │   └── chart.blade.php         # Complete EHR chart
│   │   │
│   │   ├── encounters/
│   │   │   ├── index.blade.php
│   │   │   ├── show.blade.php
│   │   │   ├── new-encounter.blade.php
│   │   │   ├── vitals.blade.php        # Vital signs entry
│   │   │   ├── assessment.blade.php
│   │   │   └── discharge.blade.php
│   │   │
│   │   ├── orders/
│   │   │   ├── index.blade.php         # Order management
│   │   │   ├── place-order.blade.php   # CPOE interface
│   │   │   ├── lab-orders.blade.php
│   │   │   ├── radiology-orders.blade.php
│   │   │   └── medication-orders.blade.php
│   │   │
│   │   ├── results/
│   │   │   ├── index.blade.php         # Results viewer
│   │   │   ├── lab-results.blade.php
│   │   │   ├── radiology-reports.blade.php
│   │   │   └── critical-values.blade.php
│   │   │
│   │   ├── medications/
│   │   │   ├── index.blade.php
│   │   │   ├── administration.blade.php # MAR (Medication Administration Record)
│   │   │   ├── reconciliation.blade.php
│   │   │   └── drug-interaction-check.blade.php
│   │   │
│   │   ├── clinical/
│   │   │   ├── notes.blade.php
│   │   │   ├── allergies.blade.php
│   │   │   ├── conditions.blade.php
│   │   │   └── procedures.blade.php
│   │   │
│   │   ├── appointments/
│   │   │   ├── calendar.blade.php
│   │   │   ├── schedule.blade.php
│   │   │   └── resource-management.blade.php
│   │   │
│   │   ├── billing/
│   │   │   ├── claims.blade.php
│   │   │   ├── charge-entry.blade.php
│   │   │   └── denial-management.blade.php
│   │   │
│   │   ├── reports/
│   │   │   ├── quality-metrics.blade.php
│   │   │   ├── census.blade.php
│   │   │   ├── workflow-analytics.blade.php
│   │   │   └── adverse-events.blade.php
│   │   │
│   │   ├── admin/
│   │   │   ├── users.blade.php
│   │   │   ├── audit-log.blade.php
│   │   │   ├── system-health.blade.php
│   │   │   └── integrations.blade.php
│   │   │
│   │   └── components/
│   │       ├── patient-banner.blade.php
│   │       ├── vitals-widget.blade.php
│   │       ├── allergy-alert.blade.php
│   │       ├── order-summary.blade.php
│   │       ├── result-viewer.blade.php
│   │       └── timeline.blade.php
│   │
│   ├── js/
│   │   ├── app.js                      # Main entry point
│   │   ├── stores/
│   │   │   ├── patient.store.js        # Alpine store
│   │   │   ├── encounter.store.js
│   │   │   ├── user.store.js
│   │   │   └── notification.store.js
│   │   ├── composables/
│   │   │   ├── usePatient.js
│   │   │   ├── useEncounter.js
│   │   │   ├── useOrders.js
│   │   │   ├── useResults.js
│   │   │   └── useNotifications.js
│   │   ├── services/
│   │   │   ├── api.js                  # Axios instance + interceptors
│   │   │   ├── patient.service.js
│   │   │   ├── encounter.service.js
│   │   │   ├── order.service.js
│   │   │   ├── result.service.js
│   │   │   ├── medication.service.js
│   │   │   └── auth.service.js
│   │   ├── utils/
│   │   │   ├── validators.js
│   │   │   ├── formatters.js
│   │   │   ├── date-utils.js
│   │   │   └── notification-handler.js
│   │   └── plugins/
│   │       └── echo.js                 # Laravel Echo config
│   │
│   └── css/
│       ├── app.css                     # Tailwind + custom CSS
│       ├── components.css
│       ├── animations.css
│       └── print-styles.css
│
├── resources/react/                    # React for advanced UIs
│   ├── components/
│   │   ├── Dashboard.jsx
│   │   ├── PatientChart.jsx
│   │   ├── OrderEntry.jsx
│   │   ├── ResultsViewer.jsx
│   │   └── ReportGenerator.jsx
│   │
│   └── hooks/
│       ├── usePatient.js
│       ├── useOrder.js
│       └── useFhirData.js
│
└── public/
    ├── fonts/
    ├── icons/
    └── images/
```

---

## UI/UX Flows

### 1. Authentication Flow

```
┌─────────────────────┐
│    Login Page       │
│  - Email/Username   │
│  - Password         │
└──────────┬──────────┘
           │
           ▼
   ┌───────────────┐
   │  2FA Verify   │
   │  - TOTP/SMS   │
   │  - Backup code│
   └───────┬───────┘
           │
           ▼
   ┌──────────────────┐
   │ Role-based       │
   │ Dashboard        │
   │ (Session: 15min) │
   └──────────────────┘
```

**Key Components:**
- **Login Component**: Email + password, remember device option
- **MFA Verify**: TOTP (Google Authenticator), SMS backup, recovery codes
- **Session Management**: Redux/store track token, auto-logout at 15 min idle
- **Error Handling**: Invalid credentials, MFA timeout, session expired

---

### 2. Patient Search & Selection

```
┌──────────────────────────────────┐
│  PATIENT SEARCH                  │
│                                  │
│  Search: [John Doe____________]  │
│  MRN    [10234567_____________]  │
│  DOB    [01/01/1980____________]  │
│                                  │
│  [x] Advanced Search             │
│                                  │
└──────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────┐
│  RESULTS (Meilisearch powered)   │
│                                  │
│  ✓ John SMITH (MRN: 10234567)   │
│    DOB: 01/01/1980 (44yo, M)    │
│    Active encounters: 2          │
│    Last visit: 2025-05-15       │
│                                  │
│  ✓ John Smyth (MRN: 10234568)   │
│    DOB: 15/06/1975              │
│    [DUPLICATE ALERT]             │
│                                  │
└──────────────────────────────────┘
           │ select
           ▼
┌──────────────────────────────────┐
│  PATIENT BANNER (Sticky top)     │
│  ┌──────────────────────────────┐│
│  │ [Photo] John SMITH            ││
│  │ MRN: 10234567 | DOB: 01/01/80││
│  │ 📍 44y/M | Blood: O+ | ⚠ 5 pts││
│  │                               ││
│  │ ⚠ NKDA (Penicillin)           ││
│  │ ACTIVE: Hypertension, DM2     ││
│  └──────────────────────────────┘│
└──────────────────────────────────┘
```

**Features:**
- Real-time Meilisearch integration
- Fuzzy matching (typo tolerance)
- MPI (Master Patient Index) duplicate detection
- Patient banner shows key info: allergies, active conditions, abnormal flags
- Quick actions: View chart, New encounter, Place order, View results

---

### 3. Patient Chart / EHR

```
┌────────────────────────────────────────────────────┐
│                  PATIENT CHART                      │
│  [Timeline] [Vitals] [Labs] [Imaging] [Meds] [All] │
└────────────────────────────────────────────────────┘

╔════════════════════════════════════════════════════╗
║           TODAY's ENCOUNTER (In Progress)          ║
║  2025-05-19 | Ward: 4B | Bed: 401                 ║
║  Chief Complaint: Chest pain                       ║
║  Temperature: 37.2°C | BP: 145/92 (↑)              ║
║  HR: 88 | RR: 18 | O₂: 98%                         ║
║                                                    ║
║  🔴 CRITICAL ALERT: ECG ordered, await result     ║
╚════════════════════════════════════════════════════╝

Timeline View (Vertical scroll):
─────────────────────────────────────────────────────
2025-05-19 09:30  [V] Vitals recorded (Nurse)
2025-05-19 09:15  [C] Clinical Note: Chest pain assessment
2025-05-19 09:00  [O] Order placed: ECG, Troponin
2025-05-19 08:45  [A] Allergy added: Aspirin (urticaria)
2025-05-18 14:20  [R] Lab result: CBC (reviewed)
2025-05-18 12:00  [E] Encounter started

Compact Cards for each event:
┌─────────────────────────────────────┐
│ [Icon] 2025-05-19 09:30             │
│ Vitals recorded by Nurse Kim        │
│                                     │
│ Temperature: 37.2°C                 │
│ BP: 145/92 (↑ abnormal)             │
│ HR: 88 | RR: 18 | O₂: 98%           │
│                                     │
│ [View Details] [Edit]               │
└─────────────────────────────────────┘
```

**Tabs:**
- **Timeline**: Chronological view of all events
- **Vitals**: Chart trending vital signs over time
- **Labs**: Recent lab results with reference ranges
- **Imaging**: Radiology reports with image gallery
- **Meds**: Current/past medication list
- **All**: Combined view of all clinical data

---

### 4. Vital Signs Entry (Nurse)

```
┌─────────────────────────────────────────┐
│  VITAL SIGNS ENTRY                      │
│  Patient: John SMITH | Time: 09:30 AM   │
└─────────────────────────────────────────┘

Temperature (°C)
┌──────────────────────┐
│  [37.2] ℃            │  Normal ✓
│   ↑ Increase | ↓     │
└──────────────────────┘

Blood Pressure (mmHg)
┌──────────────────────┐
│  Systolic:  [145] ⚠  │  High
│  Diastolic: [92]  ⚠  │  High
│  Status: Abnormal     │
└──────────────────────┘

Heart Rate (bpm)
┌──────────────────────┐
│  [88] bpm            │  Normal ✓
└──────────────────────┘

Oxygen Saturation (%)
┌──────────────────────┐
│  [98] % on RA        │  Normal ✓
│  (Room Air)          │
└──────────────────────┘

[📷 Photo] [Sync from Monitor] [Previous]

[Save Vitals]  [Save & Print]  [Cancel]
```

**Features:**
- Quick numeric input with +/- buttons
- Abnormal flag detection (red highlight)
- Trending graph (last 24h)
- Photo/attachment support
- Integration with bedside monitors
- Mandatory confirmation before save

---

### 5. Computerized Provider Order Entry (CPOE)

```
┌──────────────────────────────────────┐
│  PLACE NEW ORDER                     │
│  Patient: John SMITH (MRN: 10234567) │
└──────────────────────────────────────┘

Order Type:
[🧬 Lab]  [🏥 Radiology]  [💊 Medication]  [🔬 Other]
(Lab selected)

Order Selection:
Search: [CBC, CMP, Troponin______]
┌──────────────────────────────────────┐
│ ✓ Complete Blood Count (CBC)         │
│   Category: Hematology               │
│   Reference range:                   │
│   - WBC: 4.5-11.0 K/μL              │
│   - RBC: 4.5-5.9 M/μL               │
│   - Hemoglobin: 13.5-17.5 g/dL      │
│   ⓘ Last: 2025-05-15                │
│                                      │
│   [Select]                           │
└──────────────────────────────────────┘

Order Details:
Priority: [STAT] [URGENT] [ROUTINE]
Specimen: [Whole Blood EDTA]
Special Instructions: [Keep refrigerated___]

⚠️ Clinical Decision Support:
  ✓ Patient age 44y - appropriate order
  ✓ No drug interactions
  ✓ Allergy check - OK (no conflict)

[Place Order]  [Save as Draft]  [Cancel]

After submission:
┌──────────────────────────────────────┐
│  ✓ Order Placed                      │
│  Order #: OD-2025-0123456           │
│  Status: Pending                     │
│  Sent to: LIS                        │
│  ETA: 30 minutes                     │
│  [View Order] [Print Requisition]    │
└──────────────────────────────────────┘
```

**Features:**
- Smart order typeahead (Meilisearch integration)
- CDS alerts (non-blocking advisory)
- Indication/clinical reasoning field (mandatory)
- Specimen type selection
- Priority/urgency selector
- Order summary before confirmation
- Print requisition for lab pickup

---

### 6. Results Viewer

```
┌──────────────────────────────────────────┐
│  RESULTS VIEWER                          │
│  Patient: John SMITH                     │
│  Date filter: [Last 7 days ▼]            │
└──────────────────────────────────────────┘

PENDING ORDERS:
┌──────────────────────────────────────────┐
│ 🔵 Troponin I (Order #OD-2025-0123456)   │
│    Ordered: 2025-05-19 09:00             │
│    Expected: 2025-05-19 10:30            │
│    Status: In Progress (45 min elapsed)  │
│    Specimen: Serum                       │
└──────────────────────────────────────────┘

COMPLETED RESULTS:
┌──────────────────────────────────────────┐
│ ✅ Complete Blood Count                  │
│    Resulted: 2025-05-19 08:45            │
│    Status: Reviewed by Dr. Johnson       │
│                                          │
│    WBC          8.5 K/μL    ✓ Normal    │
│    RBC          4.8 M/μL    ✓ Normal    │
│    Hemoglobin   14.2 g/dL   ✓ Normal    │
│    Platelets    245 K/μL    ✓ Normal    │
│                                          │
│    [View Details] [Add to Summary]      │
└──────────────────────────────────────────┘

┌──────────────────────────────────────────┐
│ 🔴 Comprehensive Metabolic Panel (CMP)   │
│    Resulted: 2025-05-18 14:20            │
│    Status: UNREVIEWED                    │
│                                          │
│    Glucose      145 mg/dL   ⚠ HIGH      │
│    Creatinine   1.2 mg/dL   ✓ Normal    │
│    BUN          18 mg/dL    ✓ Normal    │
│    Potassium    3.8 mEq/L   ✓ Normal    │
│                                          │
│    ⓘ Abnormal results highlighted       │
│    [Notify Clinician] [Acknowledge]     │
└──────────────────────────────────────────┘

IMAGING RESULTS:
┌──────────────────────────────────────────┐
│ ✅ Chest X-Ray (PA & Lateral)            │
│    Resulted: 2025-05-17 11:30            │
│    Interpreted by: Dr. Sarah Chen (Radiology)
│                                          │
│    FINDINGS: No acute cardiopulmonary    │
│    process. Heart size normal. No focal  │
│    consolidation. Osseous structures     │
│    intact.                               │
│                                          │
│    [View Images] [View Report] [Compare]│
└──────────────────────────────────────────┘
```

**Features:**
- Pending vs completed results
- Color coding: ✓ normal, ⚠ abnormal, 🔴 critical
- Reference ranges displayed
- Review status tracking
- Image viewer with zoom/pan
- Ability to acknowledge/action results
- Critical value notification routing

---

### 7. Medication Administration Record (MAR)

```
┌─────────────────────────────────────────────┐
│  MEDICATION ADMINISTRATION RECORD (MAR)     │
│  Patient: John SMITH | Ward: 4B, Bed: 401  │
│  Date: 2025-05-19                          │
└─────────────────────────────────────────────┘

SCHEDULED MEDICATIONS:
┌─────────────────────────────────────────────┐
│ Metoprolol 50 mg                            │
│ Route: PO (by mouth)                        │
│ Frequency: BID (twice daily)                │
│ Indication: Hypertension                    │
│ Dose: 50 mg                                 │
│ Duration: 2025-05-19 to 2025-06-19        │
│                                             │
│ Times:  [06:00]  [18:00]                   │
│ ─────────────────────────────────────────   │
│ 06:00   ✓ Given (Nurse: Kim Johnson)       │
│ Time:   06:05                              │
│ Barcode verified                           │
│ [View Details]                             │
│                                             │
│ 18:00   ⏳ Due in 2 hours                   │
│ [Mark as Given] [Hold] [Refuse]            │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ Aspirin 100 mg                              │
│ Route: PO                                   │
│ Frequency: OD (once daily)                  │
│ ⚠️ ALERT: Patient allergic to Aspirin       │
│           (Urticaria documented)            │
│                                             │
│ Status: [⛔ HOLD - ALLERGY]                 │
│ [Acknowledge & Verify] [Dismiss]           │
└─────────────────────────────────────────────┘

PRN (As-needed) MEDICATIONS:
┌─────────────────────────────────────────────┐
│ Acetaminophen 500 mg                        │
│ Route: PO                                   │
│ Indication: Fever/Pain                      │
│ Frequency: Q6H as needed                    │
│                                             │
│ History:                                    │
│ 2025-05-19 08:30 ✓ Given (1000 mg total)   │
│ 2025-05-19 02:15 ✓ Given (500 mg)          │
│ [Give Now] [Details]                       │
└─────────────────────────────────────────────┘

REFUSALS:
┌─────────────────────────────────────────────┐
│ 2025-05-19 12:00 Patient refused Metoprolol│
│ Reason: Dizziness                          │
│ Nurse: Kim Johnson                         │
│ [Document Refusal] [Contact Provider]      │
└─────────────────────────────────────────────┘
```

**Features:**
- Barcode verification (5 Rights of Medication)
- Allergy alerts (blocking)
- Scheduled vs PRN medications
- Time-based administration tracking
- Refusal/hold documentation
- Drug interaction warnings
- Medication reconciliation status

---

### 8. Medication Reconciliation

```
┌──────────────────────────────────────────┐
│  MEDICATION RECONCILIATION                │
│  Patient: John SMITH                      │
│  Encounter: 2025-05-19 (Admission)        │
└──────────────────────────────────────────┘

CURRENT HOME MEDICATIONS (Patient-reported):
┌──────────────────────────────────────────┐
│ 1. Lisinopril 10 mg, PO, OD              │
│ 2. Metoprolol 50 mg, PO, BID             │
│ 3. Atorvastatin 40 mg, PO, OD            │
│ 4. Aspirin 81 mg, PO, OD                 │
│ 5. Multivitamin, PO, OD                  │
│                                          │
│ Source: Patient interview (2025-05-19)   │
│ [Reconciled by: Dr. Johnson]              │
└──────────────────────────────────────────┘

MEDICATION HISTORY (From pharmacy records):
┌──────────────────────────────────────────┐
│ ✓ Lisinopril 10 mg       [MATCH]         │
│ ✓ Metoprolol 50 mg       [MATCH]         │
│ ✓ Atorvastatin 40 mg     [MATCH]         │
│ ⚠️ Aspirin 81 mg          [PATIENT HAS   │
│                           DOCUMENTED     │
│                           ALLERGY]       │
│ ✓ Multivitamin           [MATCH]         │
│                                          │
│ ? Omeprazole 20 mg       [IN HISTORY     │
│                           BUT NOT        │
│                           REPORTED]      │
└──────────────────────────────────────────┘

RECOMMENDED ORDERS FOR ADMISSION:
┌──────────────────────────────────────────┐
│ [✓] Continue Lisinopril 10 mg, PO, OD   │
│ [✓] Continue Metoprolol 50 mg, PO, BID  │
│ [✓] Continue Atorvastatin 40 mg, PO, OD │
│ [✗] DISCONTINUE Aspirin (ALLERGY)        │
│ [?] Clarify Omeprazole status            │
│                                          │
│ [Place Selected Orders] [Modify]         │
└──────────────────────────────────────────┘

Status: ✅ RECONCILED
Reviewed by: Dr. Michael Johnson, 2025-05-19 09:45
Signature: [Digital signature]
```

**Features:**
- Multi-source reconciliation (patient + pharmacy + external)
- Discrepancy detection
- Allergy cross-check
- Medication status tracking (continue/hold/DC)
- Documentation of rationale
- Digital signature

---

### 9. Clinical Decision Support (CDS) Alert

```
When order is placed:

┌─────────────────────────────────────────┐
│        ⚠️  CLINICAL ALERT               │
├─────────────────────────────────────────┤
│ Metformin + Renal Impairment            │
│                                         │
│ You ordered Metformin 1000 mg BID       │
│                                         │
│ ⚠️ WARNING:                             │
│ This patient's eGFR is 42 mL/min/1.73m²│
│ (CMP from 2025-05-18)                   │
│                                         │
│ RECOMMENDATION:                         │
│ Consider dose reduction or alternative  │
│ per ADA guidelines for CKD Stage 3b     │
│                                         │
│ References:                             │
│ - ADA 2025 Standards of Care            │
│ - FDA Metformin labeling                │
│                                         │
│ [More Info] [Override] [Discontinue]   │
├─────────────────────────────────────────┤
│ [✓ Continue with caution] [Cancel]      │
└─────────────────────────────────────────┘

Note: CDS alert is ADVISORY only. Clinician
      can override with documentation.
```

**Features:**
- Non-blocking advisories (never interrupt workflow)
- Evidence-based recommendations
- Evidence links/references
- Override option with rationale capture
- Severity levels: info, warning, critical
- Frequency limiting (no alert spam)

---

### 10. Appointments & Scheduling

```
┌──────────────────────────────────────────┐
│  APPOINTMENT CALENDAR                    │
│  View: [Week ▼] | Date: [2025-05-19 ▼]  │
└──────────────────────────────────────────┘

WEEKLY VIEW (Time slots by resource):
┌─────────────────────────────────────────────────┐
│ MON 19   TUE 20   WED 21   THU 22   FRI 23     │
├─────────────────────────────────────────────────┤
│ CLINIC A (Dr. Smith)                            │
│ 09:00 [╳] John DOE              30min          │
│ 09:30 [╳] BLOCKED (Lunch)      30min          │
│ 10:00 [ ] Available                            │
│ 10:30 [╳] Jane SMITH            45min          │
│ 11:15 [ ] Available                            │
│ 12:00 [╳] BLOCKED (Lunch)      60min          │
│                                                │
│ CLINIC B (Dr. Johnson)                         │
│ 09:00 [╳] Michael BROWN         30min          │
│ 09:30 [ ] Available                            │
│ 10:00 [╳] Sarah WHITE           45min          │
│ 10:45 [ ] Available                            │
│ 12:00 [╳] BLOCKED (Lunch)      60min          │
└─────────────────────────────────────────────────┘

APPOINTMENT DETAILS (Click on slot):
┌──────────────────────────────────────────┐
│ New Appointment                          │
│                                          │
│ Patient: [John SMITH____________]        │
│ Date: [2025-05-19 ▼]                     │
│ Time: [10:00 ▼]                          │
│ Duration: [30 min ▼]                     │
│ Reason: [Annual Physical______]          │
│ Provider: [Dr. Sarah Chen ▼]              │
│ Location: [Clinic A ▼]                    │
│ Resource: [Exam Room 1 ▼]                 │
│                                          │
│ [Send Reminder] [Require Pre-reg]        │
│                                          │
│ [Create Appointment]  [Cancel]           │
└──────────────────────────────────────────┘
```

**Features:**
- Calendar view (day/week/month)
- Resource blocking (lunch, breaks, maintenance)
- Drag-and-drop rescheduling
- Overbooking prevention
- Reminder automation
- Double-booking alerts
- No-show tracking

---

### 11. Quality Reports & Analytics

```
┌──────────────────────────────────────────┐
│  QUALITY METRICS DASHBOARD               │
│  Period: [Q2 2025 ▼]  Department: [All ▼]│
└──────────────────────────────────────────┘

KEY PERFORMANCE INDICATORS:
┌────────────────────┬────────────────────┐
│ Average LOS        │ 3.2 days           │
│ Target: 3.5        │ ✓ On Target        │
├────────────────────┼────────────────────┤
│ 30-Day Readmission │ 8.2%               │
│ Target: <10%       │ ✓ On Target        │
├────────────────────┼────────────────────┤
│ Sepsis Mortality   │ 18.5%              │
│ Benchmark: 20%     │ ✓ Better           │
├────────────────────┼────────────────────┤
│ C. diff Infection  │ 2.1 cases/1000     │
│ Target: <3/1000    │ ✓ On Target        │
└────────────────────┴────────────────────┘

MONTHLY TREND (Line chart):
```
Sepsis Mortality Rate (%)
|
25 |         ╱╲
   |       ╱  ╲╱╲
20 |---- Target ----
   |   ╱╲    ╱  ╲
15 | ╱   ╲╱╲╱    ╲╱
   |
   └───────────────────→ Month
   J  F  M  A  M  J
```

ADVERSE EVENTS:
┌──────────────────────────────────────────┐
│ FALLS: 12 incidents (May 2025)           │
│ - 8 assisted falls (low-risk)            │
│ - 4 unassisted (2 with injury)           │
│ Trend: ↑ +3 vs April                     │
│ Root cause: [Staffing ↓]                 │
│ Action: [Increase rounds, PT consult]    │
│ Status: Open (Due: 2025-06-01)           │
│                                          │
│ [View All] [Add Incident]                │
└──────────────────────────────────────────┘

UTILIZATION:
```
Bed Occupancy (%)
┌──────────────────────────────┐
│ 4A  ████████████████ 82%     │
│ 4B  ████████████████████ 95% │
│ 4C  ████████████ 68%         │
│ ICU ████████████████ 87%     │
└──────────────────────────────┘
```

**Features:**
- KPI dashboard (LOS, readmission, mortality)
- Trend analysis (monthly/quarterly)
- Benchmarking vs goals
- Adverse event tracking
- Root cause analysis
- Action items with ownership
- Export to PDF/Excel

---

### 12. Admin Panel - System Health

```
┌──────────────────────────────────────────┐
│  SYSTEM HEALTH MONITORING                │
│  Last check: 2025-05-19 10:15 UTC        │
└──────────────────────────────────────────┘

SYSTEM STATUS:
┌────────────────────┬─────────────────────┐
│ Component          │ Status              │
├────────────────────┼─────────────────────┤
│ Database (MySQL)   │ ✅ OK (8.0.35)      │
│ Redis Cache        │ ✅ OK               │
│ Queue (Horizon)    │ ⚠️  WARN: 523 jobs  │
│ LIS (HL7)          │ ✅ OK (last: 2min)  │
│ RIS (HL7)          │ ⚠️  WARN: 30min ago │
│ Pharmacy (HL7)     │ ✅ OK               │
│ Meilisearch        │ ✅ OK               │
│ S3 Storage         │ ✅ OK (1.2 TB used)│
│ Disk Space         │ ⚠️  WARN: 18% free  │
│ TLS Certificate    │ ✅ OK (365 days)    │
└────────────────────┴─────────────────────┘

QUEUE MONITORING (Horizon):
┌──────────────────────────────────────────┐
│ ProcessIncomingHl7Message: 125 pending   │
│ NotifyCriticalLabValue:     0 pending    │
│ ProcessMedicationAdmin:     58 pending   │
│ GenerateQualityMetrics:     1 running    │
│                                          │
│ Failed Jobs (past 24h): 3                │
│ [View Failed] [Retry All]                │
└──────────────────────────────────────────┘

HL7 MESSAGE TRACKING:
┌──────────────────────────────────────────┐
│ LIS Inbox:   342 pending, 0 dead         │
│ RIS Inbox:   156 pending, 2 dead         │
│ Outbox:      89 pending, 1 dead          │
│                                          │
│ [Retry Dead] [View Log] [Clear Inbox]   │
└──────────────────────────────────────────┘

ALERTS:
┌──────────────────────────────────────────┐
│ 🔴 High RIS latency (>30 min)            │
│    Duration: 45 minutes                  │
│    Started: 2025-05-19 09:30             │
│    [Investigate] [Acknowledge]           │
│                                          │
│ 🟡 Disk space low (18% free)             │
│    Threshold: <20%                       │
│    [View Details] [Expand Storage]       │
└──────────────────────────────────────────┘

AUDIT LOG:
┌──────────────────────────────────────────┐
│ Search by user, action, resource         │
│ Date range: [2025-05-19 ▼] to [today]   │
│                                          │
│ 2025-05-19 10:15 Database check ✅ OK    │
│ 2025-05-19 10:10 RIS latency warning    │
│ 2025-05-19 10:05 Queue job failed       │
│ 2025-05-19 10:00 LIS sync successful    │
│ 2025-05-19 09:55 Cache warmed           │
│                                          │
│ [More] [Export Log]                     │
└──────────────────────────────────────────┘
```

**Features:**
- Real-time system status
- Component health checks
- Queue monitoring (Horizon)
- HL7 message tracking
- Alert notifications
- Audit log search/export
- Downtime procedures

---

### 13. Audit Log Viewer

```
┌──────────────────────────────────────────┐
│  AUDIT LOG                               │
│  Healthcare System PHI Access Log        │
└──────────────────────────────────────────┘

FILTER:
┌──────────────────────────────────────────┐
│ Date: [2025-05-19 ▼] to [today ▼]       │
│ User: [All Users ▼]                      │
│ Action: [All ▼]                          │
│ Resource: [Patient / Encounter / Order]  │
│ MRN: [____________________]               │
│ Status: [All / Success / Failure]         │
│                                          │
│ [Search]                                 │
└──────────────────────────────────────────┘

RESULTS:
┌──────────────────────────────────────────┐
│ Timestamp    User             Action     │
├──────────────────────────────────────────┤
│ 10:15:23     Dr. Smith        Viewed     │
│ User: Dr. Mark Smith (ID: user-001)     │
│ Action: Viewed patient chart             │
│ Resource: Patient #10234567 (John Doe)  │
│ Status: ✅ Success                       │
│ Duration: 12 minutes                    │
│ IP: 192.168.1.100                       │
│ [View Details]                           │
│                                          │
│ 10:02:45     Nurse Kim        Viewed     │
│ User: Nurse Kim Johnson (ID: user-002)  │
│ Action: Entered vital signs              │
│ Resource: Encounter #enc-98765           │
│ Status: ✅ Success                       │
│ Change: Temp 37.2°C, BP 145/92           │
│ IP: 192.168.1.105                       │
│ [View Details]                           │
│                                          │
│ 09:55:12     Pharmacist Lee   Verified   │
│ User: Pharmacist Lee Wang (ID: user-003)│
│ Action: Verified medication order       │
│ Resource: MedicationOrder #ord-54321    │
│ Status: ✅ Success                       │
│ [View Details]                           │
│                                          │
│ 09:45:33     Admin System     Created    │
│ User: System (Automated job)             │
│ Action: Generated quality metrics        │
│ Resource: QualityReport #2025-Q2         │
│ Status: ✅ Success                       │
│ [View Details]                           │
└──────────────────────────────────────────┘

DETAILED VIEW (Click entry):
┌──────────────────────────────────────────┐
│ PHI Access Audit Record                  │
│                                          │
│ Timestamp: 2025-05-19 10:15:23 UTC      │
│ User: Dr. Mark Smith (ID: user-001)     │
│ Role: Physician                          │
│ Action: Viewed patient chart             │
│ Resource Type: Patient                   │
│ Resource ID: 10234567                    │
│ MRN: 10234567                            │
│ Status: Authorized (Success)             │
│ Duration: 12 minutes                     │
│ IP Address: 192.168.1.100                │
│ User Agent: Safari 17.4 / macOS 14.5    │
│ Session ID: sess-abc123xyz               │
│ Purpose: Clinical decision-making        │
│ Data Accessed:                           │
│  - Demographics                          │
│  - Encounter history                     │
│  - Order list                            │
│  - Results (lab)                         │
│                                          │
│ Compliance: HIPAA PHI Access Logged      │
│ [Export] [Print]                         │
└──────────────────────────────────────────┘
```

**Features:**
- Comprehensive PHI access logging
- User, action, resource filtering
- Timestamp & IP tracking
- Session correlation
- Purpose tracking
- Data access granularity
- Non-tamperable audit trail
- Export capability

---

## Form Components & Patterns

### Form Validation

```javascript
// Alpine.js form validation (Blade template)
<form @submit.prevent="submitForm">
    <div x-data="patientForm()" class="space-y-4">
        
        <!-- Text Input with validation -->
        <div>
            <label>First Name</label>
            <input 
                type="text" 
                x-model="form.givenName"
                @blur="validateField('givenName')"
                :class="errors.givenName ? 'border-red-500' : ''"
            >
            <p x-show="errors.givenName" class="text-red-600 text-sm">
                {{ errors.givenName }}
            </p>
        </div>

        <!-- Date picker with calendar -->
        <div>
            <label>Date of Birth</label>
            <input 
                type="date" 
                x-model="form.birthDate"
                @change="calculateAge()"
                max="{{ today }}"
            >
            <p x-text="`Age: ${calculateAge()} years`"></p>
        </div>

        <!-- Select dropdown with search -->
        <div>
            <label>Blood Type</label>
            <select x-model="form.bloodType" class="w-full">
                <option value="">-- Select --</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="A+">A+</option>
                <!-- ... -->
            </select>
        </div>

        <!-- Multi-select for conditions -->
        <div>
            <label>Chronic Conditions</label>
            <div class="space-y-2">
                <template x-for="condition in conditions">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            :value="condition.id"
                            @change="form.conditions = $el.checked ? 
                                      [...form.conditions, $el.value] : 
                                      form.conditions.filter(id => id != $el.value)"
                        >
                        <span x-text="condition.name"></span>
                    </label>
                </template>
            </div>
        </div>

        <!-- Submit button -->
        <button 
            type="submit"
            :disabled="!isFormValid()"
            class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50"
        >
            Save Patient
        </button>
    </div>
</form>
```

---

## Real-time Features

### WebSocket Events (Laravel Echo)

```javascript
// Listen for lab results
Echo.channel(`encounter.${encounterId}`)
    .listen('LabResultReceived', (event) => {
        // Update results list in real-time
        addResultToUI(event.result);
        notificationStore.add({
            type: 'success',
            message: `Lab result ready: ${event.result.test_name}`
        });
    })
    .listen('CriticalValueFlagged', (event) => {
        // Show critical alert
        showCriticalValueAlert(event);
        playAlertSound();
    });

// Listen for order status changes
Echo.channel(`user.${userId}.orders`)
    .listen('OrderPlaced', (event) => {
        orderStore.addOrder(event.order);
    })
    .listen('OrderCancelled', (event) => {
        orderStore.removeOrder(event.orderId);
    });

// Listen for medication administration alerts
Echo.channel(`department.${departmentId}`)
    .listen('MedicationAdministered', (event) => {
        updateMARDisplay(event);
    });
```

---

## Mobile Responsive Design

All views must be responsive:

```html
<!-- Patient banner on mobile -->
<div class="bg-white border-b sticky top-0 z-40 p-3">
    <div class="flex items-center justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-lg font-bold truncate">John SMITH</h2>
            <p class="text-sm text-gray-600">MRN: 10234567</p>
        </div>
        <div class="flex gap-2">
            <button class="text-2xl">⚠️</button>
            <button class="text-2xl">📋</button>
        </div>
    </div>
</div>

<!-- Scrollable tabs for mobile -->
<div class="overflow-x-auto border-b">
    <div class="flex gap-4 px-3 py-2 whitespace-nowrap">
        <button class="tab-btn" data-tab="timeline">Timeline</button>
        <button class="tab-btn" data-tab="vitals">Vitals</button>
        <button class="tab-btn" data-tab="labs">Labs</button>
        <button class="tab-btn" data-tab="meds">Meds</button>
    </div>
</div>

<!-- Mobile-optimized vital entry -->
<div class="grid grid-cols-2 gap-2 p-3">
    <div>
        <label class="block text-xs font-bold">Temp (°C)</label>
        <input type="number" step="0.1" class="w-full text-lg">
    </div>
    <div>
        <label class="block text-xs font-bold">BP (sys/dia)</label>
        <input type="text" placeholder="145/92" class="w-full text-lg">
    </div>
</div>
```

---

## Accessibility (WCAG 2.1 AA)

```html
<!-- Semantic HTML -->
<main role="main" aria-label="Patient chart">
    <section aria-labelledby="vitals-heading">
        <h2 id="vitals-heading">Vital Signs</h2>
        <!-- Content -->
    </section>
</main>

<!-- ARIA labels for icons -->
<button aria-label="Edit patient demographics">
    <svg class="w-5 h-5"><use href="#icon-edit"></use></svg>
</button>

<!-- Form accessibility -->
<label for="mrn">Medical Record Number</label>
<input 
    id="mrn"
    type="text"
    aria-invalid="false"
    aria-describedby="mrn-hint"
>
<small id="mrn-hint">Format: 10-digit number</small>

<!-- Alerts accessibility -->
<div role="alert" class="border-l-4 border-red-500 p-4">
    <p class="font-bold">Critical Value Alert</p>
    <p>Potassium 2.8 mEq/L (LOW)</p>
</div>

<!-- Color contrast enforced -->
<!-- All text ≥ 4.5:1 contrast ratio (normal), 3:1 (large) -->
```

---

## Dark Mode Support

```css
/* Tailwind dark mode */
@media (prefers-color-scheme: dark) {
    :root {
        --bg-primary: #1a1a1a;
        --bg-secondary: #2a2a2a;
        --text-primary: #ffffff;
        --text-secondary: #cccccc;
    }
}

<!-- Template usage -->
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    Patient chart
</div>
```

---

## Key Interactions & Shortcuts

### Keyboard Shortcuts

```
Ctrl + K       Search patient (quick search modal)
Ctrl + N       New encounter
Ctrl + O       Place new order
Ctrl + Shift + P  Print current view
Ctrl + E       Export to PDF
?              Show help/shortcuts overlay
```

### Toast Notifications

```javascript
// Success
toast.success('Order placed successfully', { duration: 3000 });

// Error
toast.error('Failed to save vital signs. Retry?', { 
    duration: 5000,
    action: () => submitVitals()
});

// Warning
toast.warning('Patient allergy: Penicillin', { duration: 0 }); // persistent

// Info
toast.info('Lab results updated', { duration: 3000 });
```

---

## State Management (Alpine.js)

```javascript
// Global store for patient context
document.addEventListener('alpine:init', () => {
    Alpine.store('patient', {
        current: null,
        encounters: [],
        
        select(patientId) {
            axios.get(`/api/v1/patients/${patientId}`)
                .then(res => {
                    this.current = res.data;
                    localStorage.setItem('current_patient', patientId);
                })
                .catch(err => toast.error('Failed to load patient'));
        },
        
        clearSelection() {
            this.current = null;
            this.encounters = [];
            localStorage.removeItem('current_patient');
        }
    });
});

// Template access
<div x-data x-text="$store.patient.current?.mrn"></div>
```

---

## API Integration Interceptors

```javascript
// Axios instance with CSRF + JWT
const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
    }
});

// JWT token in Authorization header
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Error handling
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Redirect to login
            window.location.href = '/login';
        }
        if (error.response?.status === 403) {
            toast.error('Access denied');
        }
        if (error.response?.status >= 500) {
            toast.error('Server error. Please try again.');
        }
        return Promise.reject(error);
    }
);
```

---

## Print Styles

```css
@media print {
    /* Hide navigation, buttons */
    .navbar, .sidebar, button, [role="button"] {
        display: none !important;
    }
    
    /* Patient chart print optimized */
    .patient-chart {
        page-break-inside: avoid;
    }
    
    /* Section breaks */
    .section-header {
        page-break-before: always;
    }
    
    /* Vital table */
    .vitals-table {
        border-collapse: collapse;
    }
    
    .vitals-table td {
        border: 1px solid #000;
        padding: 8px;
    }
}
```

---

## Performance Optimization

### Code Splitting & Lazy Loading

```html
<!-- Blade lazy component loading -->
<div x-data x-intersect="$fetch('/patient/vitals-widget')">
    <!-- Vitals widget loads on scroll -->
</div>

<!-- Alpine lazy listener -->
<div x-data x-show="loaded" 
     @scroll.window="loaded = true">
    <!-- Loads when scrolled into view -->
</div>
```

### Image Optimization

```html
<!-- Lazy load DICOM images -->
<img 
    src="data:image/svg+xml,%3Csvg/%3E" 
    data-src="/imaging/study-123/image-1.jpg"
    loading="lazy"
    alt="Chest X-Ray"
>

<!-- Responsive images -->
<picture>
    <source media="(min-width: 1024px)" srcset="/chart-lg.png">
    <source media="(min-width: 640px)" srcset="/chart-md.png">
    <img src="/chart-sm.png" alt="Chart">
</picture>
```

---

## Security Considerations

```html
<!-- Content Security Policy headers (set server-side) -->
<!-- Prevents XSS, clickjacking, etc. -->

<!-- Never embed PHI in URLs -->
<!-- ❌ /patient/10234567/chart -->
<!-- ✅ /patient/chart (ID from session) -->

<!-- Sanitize user input (Alpine automatically escapes) -->
<p x-text="userInput"></p>  <!-- Safe -->
<p x-html="userInput"></p>  <!-- Requires sanitization -->

<!-- Ensure forms are CSRF-protected -->
<form method="POST">
    @csrf
</form>
```

---

## Testing & QA

### Unit Tests (Vitest + Alpine)

```javascript
import { describe, it, expect } from 'vitest';

describe('Patient Form Validation', () => {
    it('should validate MRN format', () => {
        expect(validateMRN('10234567')).toBe(true);
        expect(validateMRN('invalid')).toBe(false);
    });
    
    it('should calculate age correctly', () => {
        const dob = new Date('1980-01-01');
        expect(calculateAge(dob)).toBe(45); // or 44 if before birthday this year
    });
});
```

### Integration Tests (Cypress)

```javascript
describe('Patient Chart Workflow', () => {
    it('should load patient and display vitals', () => {
        cy.login('doctor@hospital.local');
        cy.visit('/patients/10234567');
        cy.contains('John SMITH').should('be.visible');
        cy.get('[data-cy=vitals-tab]').click();
        cy.get('[data-cy=temperature]').should('contain', '37.2');
    });
    
    it('should place an order with CDS validation', () => {
        cy.login('doctor@hospital.local');
        cy.get('[data-cy=place-order-btn]').click();
        cy.get('[data-cy=order-search]').type('CBC');
        cy.get('[data-cy=cbc-option]').click();
        cy.get('[data-cy=submit-order]').click();
        cy.contains('Order placed').should('be.visible');
    });
});
```

---

## Deployment Checklist

```
Frontend Build:
□ npm run build (minification, tree-shaking)
□ npm run build:css (Tailwind purge)
□ npm run test (unit + integration tests)
□ npm run audit (security vulnerabilities)

Assets:
□ Versioned CSS/JS (hash in filename)
□ Gzip compression enabled
□ CDN for static assets
□ Service Worker for offline support

Security:
□ CSP headers configured
□ X-Frame-Options set
□ X-Content-Type-Options: nosniff
□ Secure cookie flags (HttpOnly, Secure, SameSite)
□ No inline scripts (except Alpine)

Performance:
□ Core Web Vitals optimized
□ LCP < 2.5s
□ FID < 100ms
□ CLS < 0.1
□ Page load < 3s

Accessibility:
□ WAVE audit (no errors)
□ Axe DevTools scan
□ Keyboard navigation (Tab/Enter/Escape)
□ Screen reader testing
```

---

## Downtime Mode UI

When backend is unavailable, switch to read-only cached view:

```html
<!-- Downtime banner -->
<div class="bg-yellow-100 border-l-4 border-yellow-500 p-4">
    <p class="font-bold">⚠️ System Downtime Mode</p>
    <p class="text-sm">Viewing cached data only. Changes not saved.</p>
    <p class="text-sm text-gray-600">Last updated: 2025-05-19 10:15</p>
</div>

<!-- Disable all write operations -->
<button :disabled="isDowntime" @click="savePatient()">
    Save Patient
</button>

<!-- Cached patient snapshot display -->
<div x-show="isDowntime">
    <h2>John SMITH (MRN: 10234567)</h2>
    <p>DOB: 01/01/1980 | Gender: M</p>
    <p>Active Conditions: Hypertension, DM2</p>
    <p>Allergies: NKDA</p>
    <p>Last 5 Encounters: [displayed from cache]</p>
</div>
```

---

## Conclusion

This frontend architecture prioritizes:
- **Clinical usability** (minimize clicks, clear workflows)
- **Safety** (CDS alerts, allergy checks, critical value escalation)
- **HIPAA compliance** (PHI protection, audit logging, session management)
- **Performance** (real-time updates, responsive, offline capable)
- **Accessibility** (WCAG AA, keyboard navigation)
- **Resilience** (downtime procedures, graceful degradation)

All views are mobile-responsive and tested for accessibility. Real-time updates via WebSocket keep clinicians informed of urgent events (critical values, new results, medication alerts).
