import {
    Archive, BookOpen, Briefcase, CalendarCheck, CalendarDays, ChartColumn, CircleQuestionMark, ClipboardList, FileCheck,
    Globe, House, Inbox, Kanban, LayoutDashboard, Megaphone, MessageSquareQuote, Newspaper, NotebookPen, Package,
    Receipt, Scale, ScrollText, Settings, ShieldCheck, Signature, Sparkles, Users, UsersRound,
} from '@lucide/vue';

export function adminMenu() {
    return [
        { title: 'Utama', items: [{ label: 'Dashboard', icon: LayoutDashboard, route: 'admin.dashboard' }] },
        {
            title: 'Bisnis',
            items: [
                { label: 'Pesanan Website', icon: Briefcase, route: 'admin.orders.index', match: 'admin.orders.*' },
                { label: 'Invoice', icon: Receipt, route: 'admin.invoices.index', match: 'admin.invoices.*' },
                { label: 'Pesan Masuk', icon: Inbox, route: 'admin.messages.index' },
            ],
        },
        {
            title: 'Konten Website',
            items: [
                { label: 'Layanan', icon: Sparkles, route: 'admin.cms.index', params: { resource: 'services' } },
                { label: 'Paket Harga', icon: Package, route: 'admin.cms.index', params: { resource: 'packages' } },
                { label: 'Portofolio', icon: Globe, route: 'admin.cms.index', params: { resource: 'portfolios' } },
                { label: 'Blog', icon: Newspaper, route: 'admin.cms.index', params: { resource: 'posts' } },
                { label: 'Testimoni', icon: MessageSquareQuote, route: 'admin.cms.index', params: { resource: 'testimonials' } },
                { label: 'FAQ', icon: CircleQuestionMark, route: 'admin.cms.index', params: { resource: 'faqs' } },
                { label: 'Tim', icon: UsersRound, route: 'admin.cms.index', params: { resource: 'team' } },
            ],
        },
        {
            title: 'Perkantoran',
            items: [
                { label: 'Arsip Dokumen', icon: Archive, route: 'panel.documents.index' },
                { label: 'Tanda Tangan (TTE)', icon: Signature, route: 'panel.tte.index', match: 'panel.tte.*' },
                { label: 'Pengaturan TTE', icon: FileCheck, route: 'admin.tte.settings' },
                { label: 'Legalitas & Kepatuhan', icon: Scale, route: 'admin.compliance.index' },
                { label: 'Tugas (Kanban)', icon: Kanban, route: 'panel.tasks.index' },
                { label: 'Pengumuman', icon: Megaphone, route: 'admin.cms.index', params: { resource: 'announcements' } },
            ],
        },
        {
            title: 'SDM',
            items: [
                { label: 'Pengguna', icon: Users, route: 'admin.users.index' },
                { label: 'Absensi', icon: CalendarCheck, route: 'admin.attendance.index' },
                { label: 'Cuti & Izin', icon: CalendarDays, route: 'admin.leaves.index' },
                { label: 'Laporan Harian', icon: NotebookPen, route: 'admin.reports.index' },
            ],
        },
        {
            title: 'Sistem',
            items: [
                { label: 'Log Aktivitas', icon: ScrollText, route: 'admin.audit.index' },
                { label: 'Pengaturan', icon: Settings, route: 'admin.settings.index' },
            ],
        },
    ];
}

export function agentMenu() {
    return [
        { title: 'Utama', items: [{ label: 'Dashboard', icon: House, route: 'agent.dashboard' }] },
        {
            title: 'Pekerjaan',
            items: [
                { label: 'Absensi', icon: CalendarCheck, route: 'agent.attendance.index' },
                { label: 'Tugas Saya', icon: Kanban, route: 'panel.tasks.index' },
                { label: 'Laporan Harian', icon: NotebookPen, route: 'agent.reports.index' },
                { label: 'Pesanan Saya', icon: Briefcase, route: 'agent.orders.index', match: 'agent.orders.*' },
            ],
        },
        {
            title: 'Dokumen',
            items: [
                { label: 'Pengajuan TTE', icon: Signature, route: 'panel.tte.index', match: 'panel.tte.*' },
                { label: 'Arsip Dokumen', icon: Archive, route: 'panel.documents.index' },
            ],
        },
        {
            title: 'Lainnya',
            items: [
                { label: 'Cuti & Izin', icon: ClipboardList, route: 'agent.leaves.index' },
                { label: 'Pengumuman', icon: Megaphone, route: 'panel.announcements.index' },
            ],
        },
    ];
}

export const footerLinks = [
    { label: 'Panduan', icon: BookOpen },
    { label: 'Statistik', icon: ChartColumn },
    { label: 'Keamanan', icon: ShieldCheck },
];
