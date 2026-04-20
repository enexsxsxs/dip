export function registerReportLayoutForm(Alpine) {
    Alpine.data(
        'reportLayoutForm',
        (initialSchema, documentHeaders = [], initialDocumentHeaderId = null, footerPickUsers = {}) => ({
            tab: 'fields',
            fields: [],
            documentHeaders: Array.isArray(documentHeaders) ? documentHeaders : [],
            /** Выбранный макет шапки (document_headers.id) */
            selectedDocumentHeaderId: '',
            /** Старые макеты без FK: сохраняем шапку из JSON как есть, пока не выбран отдельный макет. */
            legacyHeaderSnapshot: null,
            footerPickUsersHead: Array.isArray(footerPickUsers?.head) ? footerPickUsers.head : [],
            footerPickUsersEngineer: Array.isArray(footerPickUsers?.engineer) ? footerPickUsers.engineer : [],
            pdfFooterHeadUserId: '',
            pdfFooterEngineerUserId: '',
            docTitle: '',
            docSubtitle: '',
            docTitleFontPt: 14,
            docSubtitleFontPt: 12,
            bodyDefaultFontFamily: 'DejaVu Serif',
            bodyDefaultFontSizePt: 11,
            bodyLineHeight: 1.35,
            selectionFontFamily: 'DejaVu Serif',
            selectionFontSizePt: 11,
            pdfFooterStyle: 'legacy',

            init() {
                const s = initialSchema && typeof initialSchema === 'object' ? initialSchema : {};
                const rawFields = Array.isArray(s.fields) ? s.fields : [];
                this.fields = rawFields.map((f) => ({
                    id: f.id || `field_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`,
                    name: f.name ?? '',
                    type: f.type === 'number' ? 'number' : f.type === 'select' ? 'select' : 'text',
                    options: Array.isArray(f.options) ? f.options.map((o) => String(o)) : [],
                    allow_other: Boolean(f.allow_other),
                    include_in_pdf_filename: Boolean(f.include_in_pdf_filename),
                }));
                this.docTitle = s.document_title ?? '';
                this.docSubtitle = s.document_subtitle ?? '';
                this.docTitleFontPt = Number(s.document_title_font_size_pt) || 14;
                this.docSubtitleFontPt = Number(s.document_subtitle_font_size_pt) || 12;
                this.bodyLineHeight = Number(s.body_line_height) || 1.35;
                this.bodyDefaultFontFamily =
                    s.body_default_font_family === 'DejaVu Sans' ? 'DejaVu Sans' : 'DejaVu Serif';
                this.bodyDefaultFontSizePt = Number(s.body_default_font_size_pt) || 11;
                this.selectionFontFamily = this.bodyDefaultFontFamily;
                this.selectionFontSizePt = this.bodyDefaultFontSizePt;

                const pf = s.pdf_footer && typeof s.pdf_footer === 'object' ? s.pdf_footer : {};
                const st = pf.style;
                if (st === 'rapport_three') {
                    this.pdfFooterStyle = 'rapport_three';
                } else if (st === 'rapport_two' || st === 'triple') {
                    this.pdfFooterStyle = 'rapport_two';
                } else {
                    this.pdfFooterStyle = 'legacy';
                }

                const hid = pf.head_user_id;
                this.pdfFooterHeadUserId =
                    hid != null && hid !== '' && Number.isFinite(Number(hid)) ? String(hid) : '';
                const eid = pf.engineer_user_id;
                this.pdfFooterEngineerUserId =
                    eid != null && eid !== '' && Number.isFinite(Number(eid)) ? String(eid) : '';

                const idRaw = initialDocumentHeaderId;
                this.selectedDocumentHeaderId =
                    idRaw != null && idRaw !== '' && String(idRaw).trim() !== ''
                        ? String(idRaw).trim()
                        : '';

                if (!this.selectedDocumentHeaderId && s.header && typeof s.header === 'object') {
                    const keys = Object.keys(s.header);
                    if (keys.length > 0) {
                        try {
                            this.legacyHeaderSnapshot = JSON.parse(JSON.stringify(s.header));
                        } catch {
                            this.legacyHeaderSnapshot = null;
                        }
                    }
                }

                this.$nextTick(() => {
                    const el = this.$refs.editor;
                    if (!el) {
                        return;
                    }
                    const html =
                        typeof s.body_html === 'string' && s.body_html.trim() !== ''
                            ? s.body_html
                            : '<p>Текст заявки… Вставьте поля кнопками ниже.</p>';
                    el.innerHTML = this.storageHtmlToEditorHtml(html);
                    this.stripLegacyFieldWidgetTags(el);
                    this.applyEditorBaseStyle();
                    this.syncFieldWidgetLabels();
                });

                this.$watch(
                    'fields',
                    () => {
                        this.$nextTick(() => this.syncFieldWidgetLabels());
                    },
                    { deep: true },
                );
            },

            previewFontStack(layoutFamily) {
                const fam = layoutFamily || 'DejaVu Serif';

                return fam === 'DejaVu Sans' ? 'Arial, Helvetica, sans-serif' : '"Times New Roman", Times, serif';
            },

            applyEditorBaseStyle() {
                const el = this.$refs.editor;
                if (!el) {
                    return;
                }
                const fam = this.bodyDefaultFontFamily || 'DejaVu Serif';
                const size = Number(this.bodyDefaultFontSizePt) || 11;
                const lh = Number(this.bodyLineHeight) || 1.35;
                el.style.fontFamily = this.previewFontStack(fam);
                el.style.fontSize = `${size}pt`;
                el.style.lineHeight = String(lh);
            },

            applySelectionFont() {
                this.focusEditor();
                const sel = window.getSelection();
                if (!sel || !this.$refs.editor || sel.rangeCount === 0) {
                    return;
                }
                const range = sel.getRangeAt(0);
                if (range.collapsed) {
                    return;
                }
                if (!this.$refs.editor.contains(range.commonAncestorContainer)) {
                    return;
                }
                const fam = this.selectionFontFamily || 'DejaVu Serif';
                const sizePt = Number(this.selectionFontSizePt) || 11;
                const span = document.createElement('span');
                span.style.fontFamily = this.previewFontStack(fam);
                span.style.fontSize = `${sizePt}pt`;
                try {
                    range.surroundContents(span);
                } catch {
                    const frag = range.extractContents();
                    span.appendChild(frag);
                    range.insertNode(span);
                }
                sel.removeAllRanges();
            },

            insertHorizontalRule() {
                this.focusEditor();
                document.execCommand('insertHorizontalRule', false, null);
            },

            addField() {
                this.fields.push({
                    id: `field_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`,
                    name: '',
                    type: 'text',
                    options: [],
                    allow_other: false,
                    include_in_pdf_filename: false,
                });
            },

            removeField(index) {
                if (index < 0 || index >= this.fields.length) {
                    return;
                }
                this.fields.splice(index, 1);
            },

            addOption(fieldIndex) {
                this.fields[fieldIndex].options.push('');
            },

            removeOption(fieldIndex, optIndex) {
                this.fields[fieldIndex].options.splice(optIndex, 1);
            },

            focusEditor() {
                this.$refs.editor?.focus();
            },

            escapeHtmlAttr(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;');
            },

            escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            },

            stripLegacyFieldWidgetTags(root) {
                root.querySelectorAll('.report-field-widget__tag').forEach((node) => node.remove());
            },

            fieldLabelForId(fieldId) {
                const id = String(fieldId ?? '').trim();
                const f = this.fields.find((x) => String(x.id) === id);
                const n = f?.name != null ? String(f.name).trim() : '';

                return n || id || 'поле';
            },

            systemTokenEditorLabel(token) {
                const t = String(token ?? '').trim();
                if (t === 'sys.writeoff_equipment_list') {
                    return 'Список оборудования на списание (из БД, без подтверждения администратора)';
                }
                if (t === 'sys.move_equipment_list') {
                    return 'Список оборудования на перемещение (из БД, заявка не подтверждена администратором)';
                }

                return t;
            },

            createSystemTokenWidget(token) {
                const t = String(token ?? '').trim();
                const span = document.createElement('span');
                span.setAttribute('contenteditable', 'false');
                span.setAttribute('draggable', 'true');
                span.setAttribute('data-report-system-token', t);
                span.className = 'report-system-widget';
                const labelEl = document.createElement('span');
                labelEl.className = 'report-system-widget__label';
                labelEl.textContent = this.systemTokenEditorLabel(t);
                span.appendChild(labelEl);

                return span;
            },

            storageHtmlToEditorHtml(html) {
                if (!html || typeof html !== 'string') {
                    return html;
                }

                let out = html.replace(/\{\{field:([^}]+)\}\}/g, (match, idRaw) => {
                    const id = String(idRaw).trim();
                    const safeAttr = this.escapeHtmlAttr(id);
                    const safeLabel = this.escapeHtml(this.fieldLabelForId(id));

                    return `<span contenteditable="false" draggable="true" data-report-field-id="${safeAttr}" class="report-field-widget"><span class="report-field-widget__name">${safeLabel}</span></span>`;
                });

                out = out.replace(/\{\{\s*sys\.writeoff_equipment_list\s*\}\}/g, () => {
                    const safeAttr = this.escapeHtmlAttr('sys.writeoff_equipment_list');
                    const safeLabel = this.escapeHtml(this.systemTokenEditorLabel('sys.writeoff_equipment_list'));

                    return `<span contenteditable="false" draggable="true" data-report-system-token="${safeAttr}" class="report-system-widget"><span class="report-system-widget__label">${safeLabel}</span></span>`;
                });

                out = out.replace(/\{\{\s*sys\.move_equipment_list\s*\}\}/g, () => {
                    const safeAttr = this.escapeHtmlAttr('sys.move_equipment_list');
                    const safeLabel = this.escapeHtml(this.systemTokenEditorLabel('sys.move_equipment_list'));

                    return `<span contenteditable="false" draggable="true" data-report-system-token="${safeAttr}" class="report-system-widget"><span class="report-system-widget__label">${safeLabel}</span></span>`;
                });

                return out;
            },

            createFieldWidgetElement(fieldId) {
                const id = String(fieldId ?? '').trim();
                const span = document.createElement('span');
                span.setAttribute('contenteditable', 'false');
                span.setAttribute('data-report-field-id', id);
                span.setAttribute('draggable', 'true');
                span.className = 'report-field-widget';
                const nameEl = document.createElement('span');
                nameEl.className = 'report-field-widget__name';
                nameEl.textContent = this.fieldLabelForId(id);
                span.appendChild(nameEl);

                return span;
            },

            syncFieldWidgetLabels() {
                const root = this.$refs.editor;
                if (!root) {
                    return;
                }
                root.querySelectorAll('[data-report-field-id]').forEach((w) => {
                    const id = w.getAttribute('data-report-field-id');
                    const nameEl = w.querySelector('.report-field-widget__name');
                    if (nameEl && id != null) {
                        nameEl.textContent = this.fieldLabelForId(id);
                    }
                });
                root.querySelectorAll('[data-report-system-token]').forEach((w) => {
                    const token = w.getAttribute('data-report-system-token');
                    const labelEl = w.querySelector('.report-system-widget__label');
                    if (labelEl && token != null && token !== '') {
                        labelEl.textContent = this.systemTokenEditorLabel(token);
                    }
                });
            },

            serializeEditorToStorageHtml() {
                const root = this.$refs.editor;
                if (!root) {
                    return '';
                }
                const clone = root.cloneNode(true);
                clone.querySelectorAll('[data-report-system-token]').forEach((el) => {
                    const token = el.getAttribute('data-report-system-token');
                    if (token == null || token === '') {
                        return;
                    }
                    el.replaceWith(document.createTextNode(`{{${token}}}`));
                });
                clone.querySelectorAll('[data-report-field-id]').forEach((el) => {
                    const id = el.getAttribute('data-report-field-id');
                    if (id == null || id === '') {
                        return;
                    }
                    el.replaceWith(document.createTextNode(`{{field:${id}}}`));
                });

                return clone.innerHTML.trim();
            },

            exec(cmd, value = null) {
                this.focusEditor();
                document.execCommand(cmd, false, value);
            },

            insertFieldToken(fieldId) {
                const widget = this.createFieldWidgetElement(fieldId);
                this.focusEditor();
                const sel = window.getSelection();
                if (!this.$refs.editor) {
                    return;
                }
                if (!sel) {
                    return;
                }
                if (!this.$refs.editor.contains(sel.anchorNode) && sel.anchorNode !== this.$refs.editor) {
                    this.$refs.editor.focus();
                    const range = document.createRange();
                    range.selectNodeContents(this.$refs.editor);
                    range.collapse(false);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
                if (!sel.rangeCount) {
                    return;
                }
                const range = sel.getRangeAt(0);
                range.deleteContents();
                range.insertNode(widget);
                const space = document.createTextNode('\u00a0');
                widget.parentNode.insertBefore(space, widget.nextSibling);
                range.setStartAfter(space);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            },

            insertSystemToken(token) {
                const widget = this.createSystemTokenWidget(token);
                this.focusEditor();
                const sel = window.getSelection();
                if (!this.$refs.editor || !sel) {
                    return;
                }
                if (!this.$refs.editor.contains(sel.anchorNode) && sel.anchorNode !== this.$refs.editor) {
                    const range = document.createRange();
                    range.selectNodeContents(this.$refs.editor);
                    range.collapse(false);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
                if (!sel.rangeCount) {
                    return;
                }
                const range = sel.getRangeAt(0);
                range.deleteContents();
                range.insertNode(widget);
                const space = document.createTextNode('\u00a0');
                widget.parentNode?.insertBefore(space, widget.nextSibling);
                range.setStartAfter(space);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            },

            fieldsForInsert() {
                return this.fields.filter((f) => f.name && String(f.name).trim() !== '');
            },

            buildPdfFooterPayload() {
                const st =
                    this.pdfFooterStyle === 'rapport_three'
                        ? 'rapport_three'
                        : this.pdfFooterStyle === 'rapport_two'
                          ? 'rapport_two'
                          : 'legacy';
                const out = { style: st, head_user_id: null, engineer_user_id: null };
                if (st === 'rapport_two' || st === 'rapport_three') {
                    const hu = String(this.pdfFooterHeadUserId || '').trim();
                    if (hu !== '' && Number.isFinite(parseInt(hu, 10))) {
                        out.head_user_id = parseInt(hu, 10);
                    }
                }
                if (st === 'rapport_three') {
                    const eu = String(this.pdfFooterEngineerUserId || '').trim();
                    if (eu !== '' && Number.isFinite(parseInt(eu, 10))) {
                        out.engineer_user_id = parseInt(eu, 10);
                    }
                }

                return out;
            },

            buildSchema() {
                const used = new Set();
                const fields = [];
                for (const f of this.fields) {
                    const name = String(f.name ?? '').trim();
                    if (!name) {
                        continue;
                    }
                    let id = String(f.id ?? '').trim();
                    if (!id || used.has(id)) {
                        id = `field_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
                    }
                    used.add(id);
                    const row = { id, name, type: f.type };
                    if (f.type === 'select') {
                        row.options = f.options.map((o) => String(o).trim()).filter(Boolean);
                        row.allow_other = Boolean(f.allow_other);
                    }
                    if (f.include_in_pdf_filename) {
                        row.include_in_pdf_filename = true;
                    }
                    fields.push(row);
                }

                const schema = {
                    fields,
                    document_title: this.docTitle.trim(),
                    document_subtitle: this.docSubtitle.trim(),
                    document_title_font_size_pt: Number(this.docTitleFontPt) || 14,
                    document_subtitle_font_size_pt: Number(this.docSubtitleFontPt) || 12,
                    body_default_font_family: this.bodyDefaultFontFamily,
                    body_default_font_size_pt: Number(this.bodyDefaultFontSizePt) || 11,
                    body_line_height: Number(this.bodyLineHeight) || 1.35,
                    body_html: this.serializeEditorToStorageHtml(),
                    pdf_footer: this.buildPdfFooterPayload(),
                };

                const useHeader = document.getElementById('has_header')?.checked ?? false;
                if (!useHeader) {
                    return schema;
                }
                if (this.selectedDocumentHeaderId) {
                    return schema;
                }
                if (this.legacyHeaderSnapshot) {
                    schema.header = this.legacyHeaderSnapshot;
                }

                return schema;
            },

            prepareSubmit() {
                const useHeader = document.getElementById('has_header')?.checked ?? false;
                const sel = document.getElementById('document_header_id');
                if (sel && !useHeader) {
                    sel.value = '';
                    this.selectedDocumentHeaderId = '';
                }
                const input = document.getElementById('schema');
                if (input) {
                    input.value = JSON.stringify(this.buildSchema());
                }
            },
        }),
    );
}
