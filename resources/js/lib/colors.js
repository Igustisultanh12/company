/**
 * Peta kelas warna statis (agar terdeteksi Tailwind saat build).
 */
export const badge = {
    violet: 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
    fuchsia: 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/15 dark:text-fuchsia-300',
    pink: 'bg-pink-100 text-pink-700 dark:bg-pink-500/15 dark:text-pink-300',
    rose: 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
    orange: 'bg-orange-100 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300',
    amber: 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
    lime: 'bg-lime-100 text-lime-800 dark:bg-lime-500/15 dark:text-lime-300',
    emerald: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    teal: 'bg-teal-100 text-teal-700 dark:bg-teal-500/15 dark:text-teal-300',
    cyan: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-500/15 dark:text-cyan-300',
    sky: 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
    indigo: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',
    slate: 'bg-slate-100 text-slate-700 dark:bg-slate-500/15 dark:text-slate-300',
};

export const solid = {
    violet: 'bg-violet-500', fuchsia: 'bg-fuchsia-500', pink: 'bg-pink-500', rose: 'bg-rose-500', orange: 'bg-orange-500',
    amber: 'bg-amber-400', lime: 'bg-lime-400', emerald: 'bg-emerald-500', teal: 'bg-teal-500', cyan: 'bg-cyan-400',
    sky: 'bg-sky-500', indigo: 'bg-indigo-500', slate: 'bg-slate-400',
};

export const gradient = {
    violet: 'from-violet-500 to-fuchsia-500', fuchsia: 'from-fuchsia-500 to-pink-500', pink: 'from-pink-500 to-rose-400',
    rose: 'from-rose-500 to-orange-400', orange: 'from-orange-500 to-amber-400', amber: 'from-amber-400 to-yellow-300',
    lime: 'from-lime-400 to-emerald-400', emerald: 'from-emerald-500 to-teal-400', teal: 'from-teal-500 to-cyan-400',
    cyan: 'from-cyan-400 to-sky-500', sky: 'from-sky-500 to-indigo-500', indigo: 'from-indigo-500 to-violet-500',
    slate: 'from-slate-500 to-slate-400',
};

export const softBg = {
    violet: 'bg-violet-50 dark:bg-violet-500/10', fuchsia: 'bg-fuchsia-50 dark:bg-fuchsia-500/10', pink: 'bg-pink-50 dark:bg-pink-500/10',
    rose: 'bg-rose-50 dark:bg-rose-500/10', orange: 'bg-orange-50 dark:bg-orange-500/10', amber: 'bg-amber-50 dark:bg-amber-500/10',
    lime: 'bg-lime-50 dark:bg-lime-500/10', emerald: 'bg-emerald-50 dark:bg-emerald-500/10', teal: 'bg-teal-50 dark:bg-teal-500/10',
    cyan: 'bg-cyan-50 dark:bg-cyan-500/10', sky: 'bg-sky-50 dark:bg-sky-500/10', indigo: 'bg-indigo-50 dark:bg-indigo-500/10',
    slate: 'bg-slate-50 dark:bg-slate-500/10',
};

export const text = {
    violet: 'text-violet-600 dark:text-violet-300', fuchsia: 'text-fuchsia-600 dark:text-fuchsia-300', pink: 'text-pink-600 dark:text-pink-300',
    rose: 'text-rose-600 dark:text-rose-300', orange: 'text-orange-600 dark:text-orange-300', amber: 'text-amber-600 dark:text-amber-300',
    lime: 'text-lime-700 dark:text-lime-300', emerald: 'text-emerald-600 dark:text-emerald-300', teal: 'text-teal-600 dark:text-teal-300',
    cyan: 'text-cyan-700 dark:text-cyan-300', sky: 'text-sky-600 dark:text-sky-300', indigo: 'text-indigo-600 dark:text-indigo-300',
    slate: 'text-slate-600 dark:text-slate-300',
};

export const hex = {
    violet: '#8b5cf6', fuchsia: '#d946ef', pink: '#ec4899', rose: '#f43f5e', orange: '#f97316', amber: '#f59e0b',
    lime: '#84cc16', emerald: '#10b981', teal: '#14b8a6', cyan: '#06b6d4', sky: '#0ea5e9', indigo: '#6366f1', slate: '#94a3b8',
};

export const pick = (map, key, fallback = 'violet') => map[key] || map[fallback];
