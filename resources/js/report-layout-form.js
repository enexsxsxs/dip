export function registerReportLayoutForm(Alpine) {
    Alpine.data('reportLayoutForm', (initialSchema, headerSourceLayouts = [], footerPickUsers = {}) => ({
        tab: 'fields',
        fields: [],
        /** Список макетов для подстановки шапки: { id, title } */
        headerSourceLayouts: Array.isArray(headerSourceLayouts) ? headerSourceLayouts : [],
        footerPickUsersHead: Array.isArray(footerPickUsers?.head) ? footerPickUsers.head : [],
        footerPickUsersEngineer: Array.isArray(footerPickUsers?.engineer) ? footerPickUsers.engineer : [],
        selectedHeaderLayoutId: '',
        headerImportBusy: false,
        headerImportError: '',
        /** Выбранные подписанты в подвале (роли user / admin); пусто = как раньше, первый по фамилии. */
        pdfFooterHeadUserId: '',
        pdfFooterEngineerUserId: '',
        docTitle: '',
        docSubtitle: '',
        docTitleFontPt: 14,
        docSubtitleFontPt: 12,
        /** Базовый вид текста заявки в PDF (и стиль области редактора). */
        bodyDefaultFontFamily: 'DejaVu Serif',
        bodyDefaultFontSizePt: 11,
        bodyLineHeight: 1.35,
        /** Временные значения панели «к выделению». */
        selectionFontFamily: 'DejaVu Serif',
        selectionFontSizePt: 11,
        /** Три блока шапки (как в акте: ведомство / учреждение / название документа). */
        headerSections: [],
        /** Подвал PDF: legacy | rapport_two | rapport_three (старый triple в БД = rapport_two). */
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

            this.headerSections = [this.emptyHeaderSection(), this.emptyHeaderSection(), this.emptyHeaderSection()];
            this.loadHeaderFromSchema(s.header);

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

        emptyHeaderLine() {
            return { text: '', editable: false, line_id: null };
        },

        emptyHeaderSection() {
            return {
                align: 'center',
                bold: true,
                lines: [this.emptyHeaderLine()],
                font_size_pt: 12,
                font_family: 'DejaVu Serif',
            };
        },

        normalizeHeaderLine(raw) {
            if (raw !== null && typeof raw === 'object' && !Array.isArray(raw)) {
                return {
                    text: String(raw.text ?? ''),
                    editable: Boolean(raw.editable),
                    line_id: raw.line_id ? String(raw.line_id) : null,
                };
            }

            return { text: String(raw ?? ''), editable: false, line_id: null };
        },

        ensureHeaderLineId(line) {
            if (!line.editable) {
                return;
            }
            if (!line.line_id) {
                line.line_id = `hdr_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
            }
        },

        onHeaderLineEditableToggle(sectionIndex, lineIndex) {
            const line = this.headerSections[sectionIndex]?.lines[lineIndex];
            if (!line || typeof line !== 'object') {
                return;
            }
            if (line.editable) {
                this.ensureHeaderLineId(line);
            }
        },

        /** Новые ключи для строк «В заявке» после копирования шапки из другого макета. */
        regenerateEditableHeaderLineIds() {
            for (const sec of this.headerSections) {
                for (const line of sec.lines || []) {
                    if (line && typeof line === 'object' && line.editable) {
                        line.line_id = `hdr_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
                    }
                }
            }
        },

        /** В интерфейсе показываем Times New Roman / Arial; в схеме по-прежнему DejaVu (PDF). */
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

        async importHeaderFromSelectedLayout() {
            const id = String(this.selectedHeaderLayoutId ?? '').trim();
            if (id === '' || this.headerImportBusy) {
                return;
            }
            this.headerImportBusy = true;
            this.headerImportError = '';
            try {
                const res = await fetch(`/report-layouts/${encodeURIComponent(id)}/header-json`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    throw new Error('HTTP '.$res.status);
                }
                const data = await res.json();
                const header = data?.header;
                this.headerSections = [this.emptyHeaderSection(), this.emptyHeaderSection(), this.emptyHeaderSection()];
                if (header !== null && typeof header === 'object') {
                    this.loadHeaderFromSchema(header);
                    this.regenerateEditableHeaderLineIds();
                }
                this.selectedHeaderLayoutId = '';
            } catch {
                this.headerImportError =
                    'Не удалось загрузить шапку. Проверьте сеть или обновите страницу и попробуйте снова.';
            } finally {
                this.headerImportBusy = false;
            }
        },

        loadHeaderFromSchema(h) {
            if (!h || typeof h !== 'object') {
                return;
            }
            if (Array.isArray(h.sections) && h.sections.length > 0) {
                for (let i = 0; i < 3; i++) {
                    const sec = h.sections[i];
                    if (!sec || typeof sec !== 'object') {
                        continue;
                    }
                    const align =
                        sec.align === 'right' || sec.align === 'left' ? sec.align : 'center';
                    const rawLines = Array.isArray(sec.lines) && sec.lines.length ? sec.lines : [''];
                    const lines = rawLines.map((x) => this.normalizeHeaderLine(x));
                    for (const L of lines) {
                        if (L.editable) {
                            this.ensureHeaderLineId(L);
                        }
                    }
                    this.headerSections[i] = {
                        align,
                        bold: sec.bold !== false,
                        lines,
                        font_size_pt: Number(sec.font_size_pt) || 12,
                        font_family:
                            sec.font_family === 'DejaVu Sans' ? 'DejaVu Sans' : 'DejaVu Serif',
                    };
                }

                return;
            }
            if (Array.isArray(h.blocks) && h.blocks.length > 0) {
                for (let i = 0; i < 3 && i < h.blocks.length; i++) {
                    const b = h.blocks[i];
                    if (!b || typeof b !== 'object') {
                        continue;
                    }
                    const align =
                        b.align === 'right' || b.align === 'left' ? b.align : 'center';
                    const base = {
                        align,
                        bold: true,
                        font_size_pt: 12,
                        font_family: 'DejaVu Serif',
                    };
                    if (Array.isArray(b.lines) && b.lines.length) {
                        const lines = b.lines.map((x) => this.normalizeHeaderLine(x));
                        for (const L of lines) {
                            if (L.editable) {
                                this.ensureHeaderLineId(L);
                            }
                        }
                        this.headerSections[i] = { ...base, lines };
                    } else if (b.html) {
                        this.headerSections[i] = { ...base, lines: [this.normalizeHeaderLine(String(b.html))] };
                    } else if (b.text) {
                        this.headerSections[i] = { ...base, lines: [this.normalizeHeaderLine(String(b.text))] };
                    }
                }
            }
        },

        addHeaderLine(sectionIndex) {
            this.headerSections[sectionIndex].lines.push(this.emptyHeaderLine());
        },

        removeHeaderLine(sectionIndex, lineIndex) {
            const lines = this.headerSections[sectionIndex].lines;
            if (lines.length <= 1) {
                lines[0] = this.emptyHeaderLine();

                return;
            }
            lines.splice(lineIndex, 1);
        },

        addField() {
            this.fields.push({
                id: `field_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`,
                name: '',
                type: 'text',
                options: [],
                allow_other: false,
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

        /**
         * Подставляет в HTML редактора виджеты вместо токенов {{field:id}} (хранение в БД — по-прежнему токены).
         */
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

        serializeHeaderLineForSchema(line) {
            const n =
                line !== null && typeof line === 'object' && !Array.isArray(line) && 'text' in line
                    ? line
                    : this.normalizeHeaderLine(line);
            if (n.editable) {
                this.ensureHeaderLineId(n);

                return { text: String(n.text ?? ''), editable: true, line_id: n.line_id };
            }

            return String(n.text ?? '');
        },

        buildHeader() {
            const sections = [];
            for (const s of this.headerSections) {
                const rawLines = s.lines || [];
                const linesOut = rawLines.map((l) => this.serializeHeaderLineForSchema(l));
                const hasAny = linesOut.some((x) => {
                    if (typeof x === 'object' && x !== null && x.editable) {
                        return true;
                    }

                    return String(x).trim() !== '';
                });
                if (!hasAny) {
                    continue;
                }
                sections.push({
                    align: s.align || 'center',
                    bold: Boolean(s.bold),
                    font_size_pt: Number(s.font_size_pt) || 12,
                    font_family:
                        s.font_family === 'DejaVu Sans' ? 'DejaVu Sans' : 'DejaVu Serif',
                    lines: linesOut,
                });
            }

            return { sections };
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
                fields.push(row);
            }

            return {
                header: this.buildHeader(),
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
        },

        prepareSubmit() {
            const input = document.getElementById('schema');
            if (input) {
                input.value = JSON.stringify(this.buildSchema());
            }
        },
    }));
}
