/**
 * Ikon yang dapat dipilih dinamis (nama disimpan di database, mis. ikon layanan).
 * Hanya ikon di daftar ini yang di-bundle agar ukuran JS tetap kecil.
 */
import {
    Building, ChartColumn, Cloud, CodeXml, Cpu, Database, Globe, GraduationCap, LayoutDashboard, Lightbulb,
    Megaphone, MessageCircle, Newspaper, Palette, PenTool, Rocket, Search, Server, ShieldCheck, ShoppingBag,
    Smartphone, Sparkles, Wrench, Zap,
} from '@lucide/vue';

export const iconMap = {
    Building, ChartColumn, Cloud, CodeXml, Cpu, Database, Globe, GraduationCap, LayoutDashboard, Lightbulb,
    Megaphone, MessageCircle, Newspaper, Palette, PenTool, Rocket, Search, Server, ShieldCheck, ShoppingBag,
    Smartphone, Sparkles, Wrench, Zap,
};

export const resolveIcon = (name) => iconMap[name] || Sparkles;
