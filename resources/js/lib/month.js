function pad2(value) {
    return String(value).padStart(2, '0');
}

export function parseYearMonth(value) {
    if (!value || typeof value !== 'string') return null;
    const [y, m] = value.split('-').map((p) => Number.parseInt(p, 10));
    if (!y || !m || Number.isNaN(y) || Number.isNaN(m) || m < 1 || m > 12) return null;
    return { year: y, month: m };
}

export function formatYearMonth(year, month) {
    if (!year || !month) return '';
    return `${year}-${pad2(month)}`;
}

export function addMonths(yearMonth, delta) {
    const parsed = parseYearMonth(yearMonth);
    if (!parsed) return '';

    const date = new Date(parsed.year, parsed.month - 1, 1);
    date.setMonth(date.getMonth() + delta);

    return formatYearMonth(date.getFullYear(), date.getMonth() + 1);
}

export function formatMonthLabel(yearMonth, locale = 'id-ID') {
    const parsed = parseYearMonth(yearMonth);
    if (!parsed) return '';

    const date = new Date(parsed.year, parsed.month - 1, 1);
    const monthLabel = new Intl.DateTimeFormat(locale, { month: 'long' }).format(date);
    return `${monthLabel} ${parsed.year}`;
}

