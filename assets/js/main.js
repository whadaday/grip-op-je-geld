/**
 * Begrotingstool — hoofdscript
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Veldselectors ──────────────────────────────────────────────────────
    var fieldIn          = document.querySelectorAll('.bt-field-in');
    var fieldOut         = document.querySelectorAll('.bt-field-out');
    var fieldInkomsten   = document.getElementById('bt-totaal-inkomsten');
    var fieldUitgaven    = document.getElementById('bt-totaal-uitgaven');
    var fieldSaldo       = document.getElementById('bt-saldo');

    var budgetTable      = document.getElementById('bt-budget-table');
    var maandSaldo       = document.getElementById('bt-maand-saldo');
    var saldoLabel       = document.getElementById('bt-saldo-label');

    // ── Helpers ────────────────────────────────────────────────────────────

    function formatNumber(num, showDecimals) {
        if (showDecimals === undefined) showDecimals = true;
        if (showDecimals) {
            return num.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        var hasDecimals = num % 1 !== 0;
        var formatted   = hasDecimals ? num.toFixed(2) : num.toFixed(0);
        return formatted.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function parseInput(value) {
        if (!value) return 0;
        var cleaned = value.replace(/\./g, '').replace(',', '.').replace(/[^0-9.]/g, '');
        var num = parseFloat(cleaned);
        return isNaN(num) ? 0 : Math.round(num * 100) / 100;
    }

    function getFieldLabel(input) {
        var container = input.closest('.bt-field');
        var labelEl   = container ? container.querySelector('.bt-label') : null;
        if (!labelEl) return '';
        return labelEl.textContent.trim();
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function collectBudgetLines(inputs) {
        var lines = [];
        inputs.forEach(function (input) {
            var value = parseInput(input.value);
            if (value > 0) {
                lines.push({ name: getFieldLabel(input), value: value });
            }
        });
        return lines;
    }

    // ── Budget-tabel renderen ──────────────────────────────────────────────

    function renderBudgetTable(totalIn, totalOut, saldo) {
        if (!budgetTable) return;

        var isNeg = saldo < 0;
        var euro  = '€ ';

        function rowsHtml(lines, emptyText) {
            if (!lines.length) {
                return '<tr class="budget-line budget-line--empty"><td colspan="2">' + emptyText + '</td></tr>';
            }
            return lines.map(function (line) {
                return '<tr class="budget-line">' +
                    '<td class="budget-line__name">' + escapeHtml(line.name) + '</td>' +
                    '<td class="budget-line__amount">' + euro + formatNumber(line.value, false) + '</td>' +
                    '</tr>';
            }).join('');
        }

        var html = '<table class="budget-table">';
        html += '<caption class="sr-only">Overzicht van jouw inkomsten en uitgaven met het saldo</caption>';

        html += '<tr class="budget-section"><th colspan="2" scope="colgroup">Inkomsten</th></tr>';
        html += rowsHtml(collectBudgetLines(fieldIn), 'Geen inkomsten ingevuld');
        html += '<tr class="budget-subtotal"><th scope="row">Totaal inkomsten</th>' +
            '<td class="budget-line__amount">' + euro + formatNumber(totalIn, false) + '</td></tr>';

        html += '<tr class="budget-section"><th colspan="2" scope="colgroup">Uitgaven</th></tr>';
        html += rowsHtml(collectBudgetLines(fieldOut), 'Geen uitgaven ingevuld');
        html += '<tr class="budget-subtotal"><th scope="row">Totaal uitgaven</th>' +
            '<td class="budget-line__amount">' + euro + formatNumber(totalOut, false) + '</td></tr>';

        html += '<tr class="budget-saldo ' + (isNeg ? 'is-negative' : 'is-positive') + '">' +
            '<th scope="row">Saldo</th>' +
            '<td class="budget-line__amount">' + (isNeg ? '- ' : '') + euro + formatNumber(Math.abs(saldo), false) + '</td></tr>';

        html += '</table>';
        budgetTable.innerHTML = html;
    }

    // ── Totalen berekenen ──────────────────────────────────────────────────

    function calculateTotals() {
        var totalIn  = 0;
        var totalOut = 0;

        fieldIn.forEach(function (input) { totalIn  += parseInput(input.value); });
        fieldOut.forEach(function (input) { totalOut += parseInput(input.value); });

        var saldo = totalIn - totalOut;

        if (fieldInkomsten) fieldInkomsten.value = formatNumber(totalIn);
        if (fieldUitgaven)  fieldUitgaven.value  = formatNumber(totalOut);
        if (fieldSaldo)     fieldSaldo.value      = formatNumber(saldo);

        if (maandSaldo) maandSaldo.textContent = formatNumber(Math.abs(saldo), false);
        if (saldoLabel) saldoLabel.textContent = saldo < 0 ? 'kom je per maand tekort' : 'houdt je per maand over';

        renderBudgetTable(totalIn, totalOut, saldo);

        document.body.classList.toggle('saldo-negatief', saldo < 0);
        document.body.classList.toggle('saldo-positief', saldo >= 0);
        document.body.classList.toggle('budget-empty',   totalIn === 0 && totalOut === 0);
    }

    // ── Invoer-formatting ──────────────────────────────────────────────────

    function formatWithThousands(value) {
        var parts = value.split(',');
        parts[0]  = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return parts.join(',');
    }

    function handleNumericInput(e) {
        var input     = e.target;
        var cursorPos = input.selectionStart;
        var oldLength = input.value.length;
        var value     = input.value.replace(/\./g, '').replace(/[^0-9,]/g, '');

        var parts = value.split(',');
        if (parts.length > 2) {
            value = parts[0] + ',' + parts.slice(1).join('');
        }
        parts = value.split(',');
        if (parts.length === 2 && parts[1].length > 2) {
            value = parts[0] + ',' + parts[1].substring(0, 2);
        }

        value         = formatWithThousands(value);
        input.value   = value;

        var diff = input.value.length - oldLength;
        input.setSelectionRange(cursorPos + diff, cursorPos + diff);

        calculateTotals();
    }

    Array.from(fieldIn).concat(Array.from(fieldOut)).forEach(function (input) {
        input.addEventListener('input', handleNumericInput);
        input.addEventListener('blur', function () {
            var num = parseInput(input.value);
            if (num > 0) input.value = formatNumber(num, false);
        });
    });

    calculateTotals();

    // ── Stap-navigatie ─────────────────────────────────────────────────────

    var steps       = document.querySelectorAll('.bt-step');
    var progressBar = document.querySelector('.bt-progress__bar');
    var progressLbl = document.querySelector('.bt-progress__label');
    var progressEl  = document.querySelector('.bt-progress');
    var TOTAL       = steps.length;

    function showStep(n) {
        steps.forEach(function (step) {
            var isTarget = parseInt(step.dataset.step) === n;
            step.hidden = !isTarget;
        });

        var pct = Math.round((n / TOTAL) * 100);
        if (progressBar) progressBar.style.width = pct + '%';
        if (progressLbl) progressLbl.textContent = 'Stap ' + n + ' van ' + TOTAL;
        if (progressEl)  progressEl.setAttribute('aria-valuenow', n);

        window.scrollTo(0, 0);
    }

    document.querySelectorAll('[data-next-step]').forEach(function (btn) {
        btn.addEventListener('click', function () { showStep(parseInt(this.dataset.nextStep)); });
    });
    document.querySelectorAll('[data-prev-step]').forEach(function (btn) {
        btn.addEventListener('click', function () { showStep(parseInt(this.dataset.prevStep)); });
    });

    // ── Lightbox ───────────────────────────────────────────────────────────

    var lightbox     = document.createElement('div');
    lightbox.className = 'bt-lightbox';
    lightbox.setAttribute('role', 'dialog');
    lightbox.setAttribute('aria-modal', 'true');
    lightbox.setAttribute('aria-label', 'Meer informatie');
    lightbox.innerHTML =
        '<div class="bt-lightbox-content">' +
            '<a class="bt-lightbox-close brn" role="button" aria-label="Sluiten" tabindex="0">&times;</a>' +
            '<div class="bt-lightbox-body"></div>' +
        '</div>';
    document.body.appendChild(lightbox);

    var lightboxBody    = lightbox.querySelector('.bt-lightbox-body');
    var lightboxClose   = lightbox.querySelector('.bt-lightbox-close');
    var lightboxTrigger = null;

    function getFocusable() {
        return Array.from(lightbox.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )).filter(function (el) { return el.offsetParent !== null; });
    }

    function closeLightbox() {
        if (!lightbox.classList.contains('is-active')) return;
        lightbox.classList.remove('is-active');
        if (lightboxTrigger) lightboxTrigger.focus();
        lightboxTrigger = null;
    }

    function openLightbox(content) {
        lightboxTrigger      = document.activeElement;
        lightboxBody.innerHTML = content;
        lightbox.classList.add('is-active');
        lightboxClose.focus();
    }

    lightboxClose.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) closeLightbox();
    });
    document.addEventListener('keydown', function (e) {
        if (!lightbox.classList.contains('is-active')) return;
        if (e.key === 'Escape') { closeLightbox(); return; }
        if (e.key === 'Tab') {
            var focusable = getFocusable();
            if (!focusable.length) { e.preventDefault(); return; }
            var first = focusable[0];
            var last  = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault(); last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault(); first.focus();
            }
        }
    });

    // Info-knoppen koppelen aan de lightbox
    document.querySelectorAll('.bt-info-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var field = this.closest('.bt-field');
            var desc  = field ? field.querySelector('.bt-field-description') : null;
            if (desc) openLightbox(desc.innerHTML);
        });
    });

    // Optionele 'Meer info'-link in de paginainhoud
    var moreInfoLink = document.querySelector('#moreInfo');
    if (moreInfoLink) {
        moreInfoLink.addEventListener('click', function (e) {
            e.preventDefault();
            openLightbox(
                '<h2>Over de Begrotingstool</h2>' +
                '<p>Rond je achttiende, krijg je ineens te maken met allerlei nieuwe inkomsten en uitgaven. Salaris, toeslagen, verzekeringen, huur, abonnementen, etc. Als je dan niet weet hoeveel geld er elke maand binnenkomt en eruit gaat, kun je al snel het overzicht kwijtraken.</p>' +
                '<p>Deze tool helpt je om in korte tijd overzicht te krijgen in je inkomsten en uitgaven. Zo zie je meteen waar je geld naartoe gaat en kun je geldkeuzes maken die bij jou passen.</p>' +
                '<p>Deze tool is ontwikkeld door Diversion dankzij financiering van de gemeente Nijmegen.</p>'
            );
        });
    }

    // ── Accordion ──────────────────────────────────────────────────────────

    document.querySelectorAll('.accordion-header').forEach(function (header) {
        header.addEventListener('click', function () {
            var content    = this.nextElementSibling;
            var isExpanded = this.getAttribute('aria-expanded') === 'true';

            document.querySelectorAll('.accordion-header').forEach(function (h) {
                h.setAttribute('aria-expanded', 'false');
                h.nextElementSibling.classList.remove('active');
            });

            if (!isExpanded) {
                this.setAttribute('aria-expanded', 'true');
                content.classList.add('active');
            }
        });
    });

    // ── PDF-download ────────────────────────────────────────────────────────

    var downloadBtn = document.getElementById('downloadPdf');

    function generateBudgetPDF() {
        if (typeof window.jspdf === 'undefined') return;

        var jsPDF     = window.jspdf.jsPDF;
        var isNeg     = document.body.classList.contains('saldo-negatief');
        var doc       = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

        var margin    = 20;
        var pageW     = doc.internal.pageSize.getWidth();
        var contentW  = pageW - margin * 2;
        var green     = [21, 124, 104];
        var red       = [192, 57, 43];
        var accent    = isNeg ? red   : green;
        var accentBg  = isNeg ? [253, 236, 234] : [234, 250, 241];

        var y = 22;

        // Datum
        var today = new Date().toLocaleDateString('nl-NL', { day: 'numeric', month: 'long', year: 'numeric' });
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        doc.setTextColor(150, 150, 150);
        doc.text(today, pageW - margin, y, { align: 'right' });

        // Titel
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(20);
        doc.setTextColor(34, 34, 34);
        doc.text('Jouw resultaat', margin, y);
        y += 12;

        // Saldo-box
        var saldoTxt  = maandSaldo ? maandSaldo.textContent : '0';
        var saldoLbl  = saldoLabel ? saldoLabel.textContent : '';
        var saldoSign = isNeg ? '- € ' : '€ ';

        doc.setFillColor(accentBg[0], accentBg[1], accentBg[2]);
        doc.roundedRect(margin, y, contentW, 24, 4, 4, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(18);
        doc.setTextColor(accent[0], accent[1], accent[2]);
        doc.text(saldoSign + saldoTxt, pageW / 2, y + 10, { align: 'center' });
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(80, 80, 80);
        doc.text(saldoLbl, pageW / 2, y + 18, { align: 'center' });
        y += 34;

        // Resultaattekst
        if (y > 240) { doc.addPage(); y = 20; }
        if (!isNeg) {
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(10);
            doc.setTextColor(34, 34, 34);
            doc.text('Lekker bezig!', margin, y);
            y += 6;
        }
        var resultBody = isNeg
            ? 'Uit je begroting blijkt dat je meer uitgeeft dan je binnenkrijgt. Door de vragen en tips hieronder te bekijken, kom je erachter waar het knelt en wat je hieraan kunt doen. Er bestaan ook veel organisaties die met je mee kunnen denken. Je hoeft het niet alleen te doen!'
            : 'Je geeft minder uit dan je binnenkrijgt. Gebruik de vragen en tips hieronder om te zorgen dat je dit volhoudt en bewust te kiezen wat je met je geld wilt doen.';
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(60, 60, 60);
        doc.splitTextToSize(resultBody, contentW).forEach(function (line) {
            if (y > 278) { doc.addPage(); y = 20; }
            doc.text(line, margin, y);
            y += 5;
        });
        y += 8;

        // Herbereken totalen
        var totalIn  = 0; fieldIn.forEach(function (i) { totalIn  += parseInput(i.value); });
        var totalOut = 0; fieldOut.forEach(function (i) { totalOut += parseInput(i.value); });
        var saldoNum = totalIn - totalOut;
        var saldoAbs = Math.abs(saldoNum);

        function budgetRows(inputs) {
            var rows = [];
            inputs.forEach(function (i) {
                var val = parseInput(i.value);
                if (val > 0) rows.push([getFieldLabel(i), '€ ' + formatNumber(val, false)]);
            });
            return rows;
        }

        var inkRows = budgetRows(fieldIn);
        var uitRows = budgetRows(fieldOut);
        if (!inkRows.length) inkRows.push([{ content: 'Geen inkomsten ingevuld', colSpan: 2, styles: { textColor: [136, 136, 136], fontStyle: 'italic' } }]);
        if (!uitRows.length) uitRows.push([{ content: 'Geen uitgaven ingevuld',  colSpan: 2, styles: { textColor: [136, 136, 136], fontStyle: 'italic' } }]);

        var subStyle = { fontStyle: 'bold', lineWidth: { top: 0.4 }, lineColor: [221, 221, 221] };

        doc.autoTable({
            startY: y,
            theme: 'plain',
            styles: { fontSize: 8.5, cellPadding: { top: 2, bottom: 2, left: 3, right: 3 }, textColor: [34, 34, 34], overflow: 'linebreak' },
            body: [
                [{ content: 'Inkomsten', colSpan: 2, styles: { fillColor: accent, textColor: [255, 255, 255], fontStyle: 'bold', cellPadding: { top: 3.5, bottom: 3.5, left: 3, right: 3 } } }]
            ].concat(inkRows).concat([
                [{ content: 'Totaal inkomsten', styles: subStyle }, { content: '€ ' + formatNumber(totalIn,  false), styles: Object.assign({ halign: 'right' }, subStyle) }],
                [{ content: 'Uitgaven',  colSpan: 2, styles: { fillColor: accent, textColor: [255, 255, 255], fontStyle: 'bold', cellPadding: { top: 3.5, bottom: 3.5, left: 3, right: 3 } } }]
            ]).concat(uitRows).concat([
                [{ content: 'Totaal uitgaven',  styles: subStyle }, { content: '€ ' + formatNumber(totalOut, false), styles: Object.assign({ halign: 'right' }, subStyle) }],
                [{ content: 'Saldo', styles: { fontStyle: 'bold', textColor: accent } }, { content: (isNeg ? '- ' : '') + '€ ' + formatNumber(saldoAbs, false), styles: { fontStyle: 'bold', halign: 'right', textColor: accent } }]
            ]),
            columnStyles: { 1: { halign: 'right', cellWidth: 42 } },
            margin: { left: margin, right: margin }
        });

        y = doc.lastAutoTable.finalY + 10;

        // Tips & vragen
        var faqEl = document.querySelector(isNeg ? '.accordion--negatief' : '.accordion--positief');
        if (faqEl) {
            if (y > 248) { doc.addPage(); y = 20; }
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(13);
            doc.setTextColor(34, 34, 34);
            doc.text('Tips & vragen', margin, y);
            y += 6;

            faqEl.querySelectorAll('.accordion-item').forEach(function (item) {
                var titleEl   = item.querySelector('.accordion-header span:first-child');
                var contentEl = item.querySelector('.accordion-content');
                var title     = titleEl   ? titleEl.textContent.trim()   : '';
                var content   = contentEl ? contentEl.innerText.trim()   : '';
                if (!title) return;

                if (y > 255) { doc.addPage(); y = 20; }

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);
                doc.setTextColor(34, 34, 34);
                var titleLines = doc.splitTextToSize(title, contentW);
                doc.text(titleLines, margin, y);
                y += titleLines.length * 5 + 1;

                doc.setFont('helvetica', 'normal');
                doc.setFontSize(9);
                doc.setTextColor(60, 60, 60);
                doc.splitTextToSize(content, contentW).forEach(function (line) {
                    if (y > 278) { doc.addPage(); y = 20; }
                    doc.text(line, margin, y);
                    y += 4.5;
                });
                y += 4;
            });
        }

        // Footer
        var pageCount = doc.getNumberOfPages();
        for (var p = 1; p <= pageCount; p++) {
            doc.setPage(p);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            doc.setTextColor(160, 160, 160);
            doc.text('Begrotingstool — Diversion / gemeente Nijmegen', margin, 290);
            if (pageCount > 1) {
                doc.text('Pagina ' + p + ' van ' + pageCount, pageW - margin, 290, { align: 'right' });
            }
        }

        doc.save('mijn-resultaat.pdf');
    }

    if (downloadBtn) {
        downloadBtn.addEventListener('click', generateBudgetPDF);
    }

    // ── Intro: Naar de tool ────────────────────────────────────────────────

    var startBtn = document.querySelector('.bt-btn--start');
    if (startBtn) {
        startBtn.addEventListener('click', function () {
            var card = document.querySelector('.bt-intro__card');
            var form = document.getElementById('bt-form');
            if (card) card.style.display = 'none';
            if (form) form.removeAttribute('hidden');
        });
    }

    // ── Intro: Meer weten ─────────────────────────────────────────────────

    var meerInfoLink = document.getElementById('bt-meer-info');
    if (meerInfoLink) {
        meerInfoLink.addEventListener('click', function (e) {
            e.preventDefault();
            openLightbox(
                '<h2>Over de Begrotingstool</h2>' +
                '<p>Rond je achttiende, krijg je ineens te maken met allerlei nieuwe inkomsten en uitgaven. Salaris, toeslagen, verzekeringen, huur, abonnementen, etc. Als je dan niet weet hoeveel geld er elke maand binnenkomt en eruit gaat, kun je al snel het overzicht kwijtraken. Je kunt dan ongemerkt meer uitgeven dan je hebt, met stress of geldproblemen als gevolg.</p>' +
                '<p>Deze tool helpt je om in korte tijd overzicht te krijgen in je inkomsten en uitgaven. Zo zie je meteen waar je geld naartoe gaat en kun je geldkeuzes maken die bij jou passen.</p>' +
                '<p>Deze tool is ontwikkeld door Diversion dankzij financiering van de gemeente Nijmegen.</p>'
            );
        });
    }

});
