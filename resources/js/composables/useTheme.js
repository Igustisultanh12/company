import { ref } from 'vue';

const KEY = 'ksa-theme';
const theme = ref('system');
let initialized = false;

function read() {
    try {
        return localStorage.getItem(KEY) || 'system';
    } catch {
        return 'system';
    }
}

function apply(value) {
    const dark = value === 'dark' || (value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', dark);
}

export function useTheme() {
    if (!initialized && typeof window !== 'undefined') {
        initialized = true;
        theme.value = read();
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener?.('change', () => {
            if (theme.value === 'system') apply('system');
        });
    }

    const setTheme = (value) => {
        theme.value = value;
        try {
            localStorage.setItem(KEY, value);
        } catch {
            /* abaikan */
        }
        apply(value);
    };

    const isDark = () => document.documentElement.classList.contains('dark');
    const toggle = () => setTheme(isDark() ? 'light' : 'dark');

    return { theme, setTheme, toggle, isDark };
}
