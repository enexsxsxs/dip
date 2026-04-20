export function registerReportRequestForm(Alpine) {
    Alpine.data('reportRequestForm', (layoutsPayload, oldDataRaw) => ({
        layoutId: null,
        layouts: Array.isArray(layoutsPayload) ? layoutsPayload : [],
        values: {},
        headerOverrides: {},
        extraJson: '',
        dataJson: '',
        selectionFontFamily: 'DejaVu Serif',
        selectionFontSizePt: 11,

        init() {
            const sel = document.getElementById('request_layout_id');
            const firstId = this.layouts[0]?.id ?? null;
            const parsed = sel ? parseInt(sel.value, 10) : NaN;
            this.layoutId = Number.isFinite(parsed) ? parsed : firstId;

            let oldObj = {};
            if (typeof oldDataRaw === 'string' && oldDataRaw.trim() !== '') {
                try {
                    const p = JSON.parse(oldDataRaw);
                    if (p !== null && typeof p === 'object' && !Array.isArray(p)) {
                        oldObj = p;
                    }
                } catch {
                    //
                }
            } else if (oldDataRaw !== null && typeof oldDataRaw === 'object' && !Array.isArray(oldDataRaw)) {
                oldObj = oldDataRaw;
            }

            const ids = new Set(this.activeFields.map((f) => f.id).filter(Boolean));
            this.values = {};
            const extra = { ...oldObj };
            for (const id of ids) {
                if (Object.prototype.hasOwnProperty.call(oldObj, id)) {
                    this.values[id] = oldObj[id];
                    delete extra[id];
                } else {
                    this.values[id] = '';
                }
            }
            delete extra.recipient_user_id;
            delete extra.header_overrides;
            this.extraJson = Object.keys(extra).length ? JSON.stringify(extra, null, 2) : '';

            this.rebuildHeaderOverridesFromOld(oldObj.header_overrides);
            this.syncSelectionFromLayout();
            this.syncDataJson();
        },

        get activeLayout() {
            return this.layouts.find((l) => l.id === this.layoutId);
        },

        get activeFields() {
            return this.activeLayout?.fields ?? [];
        },

        get editableHeaderLines() {
            const l = this.activeLayout;
            if (!l?.has_header) {
                return [];
            }
            const rows = l.header_editable_lines;

            return Array.isArray(rows) ? rows : [];
        },

        get hasRichTextFields() {
            return this.activeFields.some((f) => f.type !== 'select' && f.type !== 'number');
        },

        layoutFieldKey(fieldId) {
            return `${this.layoutId ?? 'x'}_${fieldId}`;
        },

        rebuildHeaderOverridesFromOld(savedHo) {
            const prev =
                savedHo !== null && typeof savedHo === 'object' && !Array.isArray(savedHo) ? { ...savedHo } : {};
            const next = {};
            for (const row of this.editableHeaderLines) {
                const id = row.line_id;
                if (Object.prototype.hasOwnProperty.call(prev, id)) {
                    const raw = String(prev[id] ?? '');
                    // Старые заявки могли хранить role-токены; в форме показываем читаемый текст из макета.
                    if (/^\{\{\s*role:[a-z0-9_]+\s*\}\}$/i.test(raw.trim())) {
                        next[id] = String(row.default_text ?? '');
                    } else {
                        next[id] = raw;
                    }
                } else {
                    next[id] = String(row.default_text ?? '');
                }
            }
            this.headerOverrides = next;
        },

        previewFontStack(layoutFamily) {
            const fam = layoutFamily || 'DejaVu Serif';

            return fam === 'DejaVu Sans' ? 'Arial, Helvetica, sans-serif' : '"Times New Roman", Times, serif';
        },

        bodyStyleForLayout() {
            const l = this.activeLayout || {};
            const fam = l.body_default_font_family === 'DejaVu Sans' ? 'DejaVu Sans' : 'DejaVu Serif';
            const size = Number(l.body_default_font_size_pt) || 11;
            const lh = Number(l.body_line_height) || 1.35;

            return { fam, size, lh };
        },

        syncSelectionFromLayout() {
            const { fam, size } = this.bodyStyleForLayout();
            this.selectionFontFamily = fam;
            this.selectionFontSizePt = size;
        },

        applyRteBaseStyle(el) {
            if (!el) {
                return;
            }
            const { fam, size, lh } = this.bodyStyleForLayout();
            el.style.fontFamily = this.previewFontStack(fam);
            el.style.fontSize = `${size}pt`;
            el.style.lineHeight = String(lh);
        },

        richInit(el, fieldId) {
            const raw = this.values[fieldId];
            const s = raw != null ? String(raw) : '';
            if (s.trim() === '') {
                el.innerHTML = '<p><br></p>';
            } else if (!/<[a-z]/i.test(s)) {
                el.innerHTML = '';
                const p = document.createElement('p');
                p.textContent = s;
                el.appendChild(p);
            } else {
                el.innerHTML = s;
            }
            this.applyRteBaseStyle(el);
            this.richSync(fieldId, el);
        },

        richSync(fieldId, el) {
            this.values[fieldId] = el.innerHTML;
            this.syncDataJson();
        },

        rteExec(fieldId, cmd, value = null) {
            const el = document.getElementById(`rte_${fieldId}`);
            if (!el) {
                return;
            }
            el.focus();
            document.execCommand(cmd, false, value);
            this.richSync(fieldId, el);
        },

        rteApplySelectionFont(fieldId) {
            const el = document.getElementById(`rte_${fieldId}`);
            if (!el) {
                return;
            }
            el.focus();
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) {
                return;
            }
            const range = sel.getRangeAt(0);
            if (range.collapsed) {
                return;
            }
            if (!el.contains(range.commonAncestorContainer)) {
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
            this.richSync(fieldId, el);
        },

        rteInsertHr(fieldId) {
            const el = document.getElementById(`rte_${fieldId}`);
            if (!el) {
                return;
            }
            el.focus();
            document.execCommand('insertHorizontalRule', false, null);
            this.richSync(fieldId, el);
        },

        rteRefreshBaseStyle(fieldId) {
            const el = document.getElementById(`rte_${fieldId}`);
            if (el) {
                this.applyRteBaseStyle(el);
            }
        },

        onLayoutChange() {
            const preservedHo = { ...this.headerOverrides };
            const s = document.getElementById('request_layout_id');
            if (s) {
                this.layoutId = parseInt(s.value, 10) || null;
            }
            const ids = new Set(this.activeFields.map((f) => f.id).filter(Boolean));
            const next = {};
            for (const id of ids) {
                next[id] = this.values[id] ?? '';
            }
            this.values = next;
            this.rebuildHeaderOverridesFromOld(preservedHo);
            this.syncSelectionFromLayout();
            this.syncDataJson();
        },

        syncDataJson() {
            let extra = {};
            try {
                if (this.extraJson.trim()) {
                    const p = JSON.parse(this.extraJson);
                    if (p !== null && typeof p === 'object' && !Array.isArray(p)) {
                        extra = p;
                    }
                }
            } catch {
                extra = {};
            }
            const merged = { ...extra, ...this.values };
            const editable = this.editableHeaderLines;
            if (editable.length > 0) {
                const ho = {};
                for (const row of editable) {
                    ho[row.line_id] = String(this.headerOverrides[row.line_id] ?? '');
                }
                merged.header_overrides = ho;
            } else {
                delete merged.header_overrides;
            }
            this.dataJson = JSON.stringify(merged);
        },
    }));
}
