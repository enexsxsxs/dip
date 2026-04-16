import './bootstrap';

import Alpine from 'alpinejs';
import { registerReportLayoutForm } from './report-layout-form';
import { registerReportRequestForm } from './report-request-form';

document.addEventListener('alpine:init', () => {
    registerReportLayoutForm(Alpine);
    registerReportRequestForm(Alpine);
});

window.Alpine = Alpine;

Alpine.start();
