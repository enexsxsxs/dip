/** Редактор макета шапки (тот же формат, что раньше хранился в layout.schema.header). */
export function registerDocumentHeaderForm(Alpine) {
    Alpine.data('documentHeaderForm', (initialSchema, headerRoleSigners = {}) => ({
        headerSections: [],
        /** Максимум блоков шапки (как в типовом акте: ведомство / учреждение / заголовок). */
        maxHeaderBlocks: 3,
        headerRolePickerByLineKey: {},
        headerRoleSigners: headerRoleSigners && typeof headerRoleSigners === 'object' ? headerRoleSigners : {},

        init() {
            const s = initialSchema && typeof initialSchema === 'object' ? initialSchema : {};
            this.headerSections = [this.emptyHeaderSection()];
            this.loadHeaderFromSchema(s);
            this.syncHeaderRolePickerFromLines();
        },

        emptyHeaderLine() {
            return { text: '', editable: false, line_id: null, role_key: '' };
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

        headerRoleOptions() {
            return [
                { key: 'sign_chief_doctor', label: 'Главный врач' },
                { key: 'sign_writeoff_head', label: 'Заведующая отделением (списание)' },
                { key: 'sign_move_head', label: 'Заведующая отделением (перемещение)' },
                { key: 'senior_nurse', label: 'Старшая медсестра (по заявке)' },
                { key: 'admin', label: 'Администратор' },
                { key: 'user', label: 'Пользователь' },
            ];
        },

        headerLineRoleKey(sectionIndex, lineIndex) {
            return `${sectionIndex}:${lineIndex}`;
        },

        headerRoleTokenForLine(sectionIndex, lineIndex) {
            const key = this.headerLineRoleKey(sectionIndex, lineIndex);
            const roleKey = String(this.headerRolePickerByLineKey[key] ?? '').trim();
            if (roleKey === '') {
                return '';
            }

            return `{{role:${roleKey}}}`;
        },

        insertRoleTokenIntoHeaderLine(sectionIndex, lineIndex) {
            const key = this.headerLineRoleKey(sectionIndex, lineIndex);
            const roleKey = String(this.headerRolePickerByLineKey[key] ?? '').trim();
            if (roleKey === '') {
                return;
            }
            const line = this.headerSections?.[sectionIndex]?.lines?.[lineIndex];
            if (!line || typeof line !== 'object') {
                return;
            }
            line.role_key = roleKey;
            line.text = this.signerNameByRoleKey(roleKey) || String(line.text ?? '').trim();
        },

        normalizeHeaderLine(raw) {
            if (raw !== null && typeof raw === 'object' && !Array.isArray(raw)) {
                const roleKey = String(raw.role_key ?? '').trim();
                let txt = String(raw.text ?? '');
                const tokenRole = this.extractRoleKeyFromText(txt);
                const effectiveRole = roleKey || tokenRole;
                if (effectiveRole !== '') {
                    txt = this.signerNameByRoleKey(effectiveRole) || txt;
                }

                return {
                    text: txt,
                    editable: Boolean(raw.editable),
                    line_id: raw.line_id ? String(raw.line_id) : null,
                    role_key: effectiveRole,
                };
            }

            const txt = String(raw ?? '');
            const roleKey = this.extractRoleKeyFromText(txt);

            return {
                text: roleKey ? (this.signerNameByRoleKey(roleKey) || txt) : txt,
                editable: false,
                line_id: null,
                role_key: roleKey,
            };
        },

        extractRoleKeyFromText(text) {
            const raw = String(text ?? '').trim();
            const m = raw.match(/^\{\{\s*role:([a-z0-9_]+)\s*\}\}$/i);

            return m ? String(m[1]).trim() : '';
        },

        signerNameByRoleKey(roleKey) {
            const key = String(roleKey ?? '').trim();
            if (key === '') {
                return '';
            }

            return String(this.headerRoleSigners?.[key] ?? '').trim();
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
            const key = this.headerLineRoleKey(sectionIndex, lineIndex);
            if (line.editable) {
                this.ensureHeaderLineId(line);
            } else {
                const next = { ...this.headerRolePickerByLineKey };
                delete next[key];
                this.headerRolePickerByLineKey = next;
                line.role_key = '';
            }
        },

        syncHeaderRolePickerFromLines() {
            const out = {};
            for (let si = 0; si < this.headerSections.length; si++) {
                const sec = this.headerSections[si];
                const lines = Array.isArray(sec?.lines) ? sec.lines : [];
                for (let li = 0; li < lines.length; li++) {
                    const line = lines[li];
                    const roleKey = String(line?.role_key ?? '').trim();
                    if (roleKey !== '') {
                        out[this.headerLineRoleKey(si, li)] = roleKey;
                    }
                }
            }
            this.headerRolePickerByLineKey = out;
        },

        loadHeaderFromSchema(h) {
            if (!h || typeof h !== 'object') {
                return;
            }
            const max = this.maxHeaderBlocks;
            if (Array.isArray(h.sections) && h.sections.length > 0) {
                const out = [];
                for (let i = 0; i < h.sections.length && i < max; i++) {
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
                    out.push({
                        align,
                        bold: sec.bold !== false,
                        lines,
                        font_size_pt: Number(sec.font_size_pt) || 12,
                        font_family:
                            sec.font_family === 'DejaVu Sans' ? 'DejaVu Sans' : 'DejaVu Serif',
                    });
                }
                if (out.length > 0) {
                    this.headerSections = out;
                }

                return;
            }
            if (Array.isArray(h.blocks) && h.blocks.length > 0) {
                const out = [];
                for (let i = 0; i < h.blocks.length && i < max; i++) {
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
                        out.push({ ...base, lines });
                    } else if (b.html) {
                        out.push({ ...base, lines: [this.normalizeHeaderLine(String(b.html))] });
                    } else if (b.text) {
                        out.push({ ...base, lines: [this.normalizeHeaderLine(String(b.text))] });
                    }
                }
                if (out.length > 0) {
                    this.headerSections = out;
                }
            }
        },

        canAddHeaderBlock() {
            return this.headerSections.length < this.maxHeaderBlocks;
        },

        addHeaderBlock() {
            if (!this.canAddHeaderBlock()) {
                return;
            }
            this.headerSections.push(this.emptyHeaderSection());
        },

        removeHeaderBlock(sectionIndex) {
            if (this.headerSections.length <= 1) {
                return;
            }
            if (sectionIndex < 0 || sectionIndex >= this.headerSections.length) {
                return;
            }
            this.headerSections.splice(sectionIndex, 1);
            this.$nextTick(() => this.syncHeaderRolePickerFromLines());
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

        serializeHeaderLineForSchema(line) {
            const n =
                line !== null && typeof line === 'object' && !Array.isArray(line) && 'text' in line
                    ? line
                    : this.normalizeHeaderLine(line);
            const roleKey = String(n.role_key ?? '').trim();
            if (n.editable) {
                this.ensureHeaderLineId(n);
                if (roleKey !== '') {
                    return { text: String(n.text ?? ''), editable: true, line_id: n.line_id, role_key: roleKey };
                }

                return { text: String(n.text ?? ''), editable: true, line_id: n.line_id };
            }
            if (roleKey !== '') {
                return { text: String(n.text ?? ''), role_key: roleKey };
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

        prepareSubmit() {
            const input = document.getElementById('schema');
            if (input) {
                input.value = JSON.stringify(this.buildHeader());
            }
        },
    }));
}
