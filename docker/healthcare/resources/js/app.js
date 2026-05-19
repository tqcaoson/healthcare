import Alpine from 'alpinejs';

import orderEntry from './stores/order-entry';
import resultsViewer from './stores/results-viewer';
import notificationInbox from './stores/notification-inbox';
import fhirViewer from './stores/fhir-viewer';
import clinicalNoteEditor from './stores/clinical-note-editor';
import bedManagement from './stores/bed-management';
import toastStore from './stores/toast-store';
import nurseStation from './stores/nurse-station';
import icuBoard from './stores/icu-board';
import systemSettings from './stores/system-settings';
import queueBoard from './stores/queue-board';
import fileUploader from './stores/file-uploader';
import barcodeScanner from './stores/barcode-scanner';
import documentViewer from './stores/document-viewer';
import dicomViewer from './stores/dicom-viewer';
import cdsAlert from './stores/cds-alert';
import virtualTable from './stores/virtual-table';
import biometricLogin from './stores/biometric-login';
import telemetryWidget
    from './stores/telemetry-widget';

import deviceIntegration
    from './stores/device-integration';

import infiniteScroll
    from './stores/infinite-scroll';

import pwaInstall
    from './stores/pwa-install';

import stateDebugger
    from './stores/state-debugger';

import errorBoundary
    from './stores/error-boundary';

window.Alpine = Alpine;

Alpine.data('orderEntry', orderEntry);
Alpine.data('resultsViewer', resultsViewer);
Alpine.data('notificationInbox', notificationInbox);
Alpine.data('fhirViewer', fhirViewer);
Alpine.data('clinicalNoteEditor', clinicalNoteEditor);
Alpine.data('bedManagement', bedManagement);
Alpine.data('toastStore', toastStore);
Alpine.data('nurseStation', nurseStation);
Alpine.data('icuBoard', icuBoard);
Alpine.data('systemSettings', systemSettings);
Alpine.data('queueBoard', queueBoard);
Alpine.data('fileUploader', fileUploader);
Alpine.data('barcodeScanner', barcodeScanner);
Alpine.data('documentViewer', documentViewer);
Alpine.data('dicomViewer', dicomViewer);
Alpine.data('cdsAlert', cdsAlert);
Alpine.data('virtualTable', virtualTable);
Alpine.data('biometricLogin', biometricLogin);
Alpine.data(
    'telemetryWidget',
    telemetryWidget
);

Alpine.data(
    'deviceIntegration',
    deviceIntegration
);

Alpine.data(
    'infiniteScroll',
    infiniteScroll
);

Alpine.data(
    'pwaInstall',
    pwaInstall
);

Alpine.data(
    'stateDebugger',
    stateDebugger
);

Alpine.data(
    'errorBoundary',
    errorBoundary
);

Alpine.start();