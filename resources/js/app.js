import './bootstrap';

import Alpine from 'alpinejs';
import { registerReportLayoutForm } from './report-layout-form';
import { registerDocumentHeaderForm } from './document-header-form';
import { registerReportRequestForm } from './report-request-form';

document.addEventListener('alpine:init', () => {
    registerReportLayoutForm(Alpine);
    registerDocumentHeaderForm(Alpine);
    registerReportRequestForm(Alpine);
});

window.Alpine = Alpine;

Alpine.start();
