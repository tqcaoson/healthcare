# Naming Conventions

```
Models:         PascalCase, singular        Patient, LabResult, MedicationOrder
Tables:         snake_case, plural          patients, lab_results, medication_orders
Controllers:    PascalCase + Controller     PatientController, LabResultController
Services:       PascalCase + Service        MpiService, CpoeService, AuditService
Jobs:           PascalCase, verb phrase     ProcessIncomingHl7Message, NotifyCriticalLabValue
Events:         PascalCase, past tense      PatientAdmitted, OrderPlaced, LabResultReceived
Policies:       PascalCase + Policy         PatientPolicy, OrderPolicy
DTOs:           PascalCase + Dto            PlaceOrderDto, AdmitPatientDto
Enums:          PascalCase                  EncounterStatus, OrderPriority, AbnormalFlag
```
