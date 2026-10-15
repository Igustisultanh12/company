const idr = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
const num = new Intl.NumberFormat('id-ID');

export function rupiah(value) {
    return idr.format(Number(value || 0)).replace(/\s/g, '');
}

export function number(value) {
    return num.format(Number(value || 0));
}

/** Terima "YYYY-MM-DD" (tanggal lokal) atau ISO datetime. */
export function toDate(value) {
    if (!value) return null;
    if (value instanceof Date) return value;
    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        const [y, m, d] = value.split('-').map(Number);
        return new Date(y, m - 1, d);
    }
    const d = new Date(value);
    return Number.isNaN(d.getTime()) ? null : d;
}

export function date(value, opts = { day: 'numeric', month: 'short', year: 'numeric' }) {
    const d = toDate(value);
    return d ? d.toLocaleDateString('id-ID', opts) : '—';
}

export function dateLong(value) {
    return date(value, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

export function datetime(value) {
    const d = toDate(value);
    return d ? d.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
}

export function time(value) {
    const d = toDate(value);
    return d ? d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '—';
}

export function relative(value) {
    const d = toDate(value);
    if (!d) return '—';
    const diff = (Date.now() - d.getTime()) / 1000;
    const rtf = new Intl.RelativeTimeFormat('id', { numeric: 'auto' });
    const units = [
        ['year', 31536000], ['month', 2592000], ['week', 604800], ['day', 86400], ['hour', 3600], ['minute', 60],
    ];
    for (const [unit, sec] of units) {
        if (Math.abs(diff) >= sec) return rtf.format(-Math.round(diff / sec), unit);
    }
    return 'baru saja';
}

export function bytes(n) {
    n = Number(n || 0);
    if (n < 1024) return `${n} B`;
    const u = ['KB', 'MB', 'GB'];
    let i = -1;
    do { n /= 1024; i++; } while (n >= 1024 && i < u.length - 1);
    return `${n.toFixed(n < 10 ? 1 : 0)} ${u[i]}`;
}

export function minutesToHours(min) {
    if (min == null) return '—';
    const h = Math.floor(min / 60);
    const m = Math.round(min % 60);
    return h ? `${h}j ${m}m` : `${m}m`;
}

export function initials(name = '') {
    return name.trim().split(/\s+/).slice(0, 2).map((p) => p[0]?.toUpperCase() || '').join('') || 'U';
}

export function waLink(phone, text = '') {
    let p = String(phone || '').replace(/\D/g, '');
    if (p.startsWith('0')) p = `62${p.slice(1)}`;
    return `https://wa.me/${p}${text ? `?text=${encodeURIComponent(text)}` : ''}`;
}

/** Tanggal hari ini (zona waktu perangkat) format YYYY-MM-DD. */
export function todayLocal(offsetDays = 0) {
    const d = new Date();
    d.setDate(d.getDate() + offsetDays);
    const p = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
}

export function greeting() {
    const h = new Date().getHours();
    if (h < 11) return 'Selamat pagi';
    if (h < 15) return 'Selamat siang';
    if (h < 18) return 'Selamat sore';
    return 'Selamat malam';
}
