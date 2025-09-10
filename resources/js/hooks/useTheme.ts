import { useCallback, useEffect, useMemo, useState } from 'react';

type ThemeMode = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'theme';

function getInitialMode(): ThemeMode {
    if (typeof window === 'undefined') return 'system';

    // Backward compatibility: previously stored 'dark'/'light' only
    const saved = (localStorage.getItem(STORAGE_KEY) || '').toLowerCase();
    if (saved === 'dark' || saved === 'light' || saved === 'system') {
        return saved as ThemeMode;
    }
    return 'system';
}

function getSystemPrefersDark(): boolean {
    if (typeof window === 'undefined') return false;
    return (
        window.matchMedia &&
        window.matchMedia('(prefers-color-scheme: dark)').matches
    );
}

function applyHtmlClass(isDark: boolean) {
    if (typeof document === 'undefined') return;
    const root = document.documentElement;
    if (isDark) {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }
}

export function useTheme() {
    const [mode, setModeState] = useState<ThemeMode>(getInitialMode);
    // Note: keep a stable reference if needed in future; not used directly now

    const isDark = useMemo(() => {
        if (mode === 'system') {
            return getSystemPrefersDark();
        }
        return mode === 'dark';
    }, [mode]);

    // Apply class and persist mode
    useEffect(() => {
        applyHtmlClass(isDark);
        try {
            localStorage.setItem(STORAGE_KEY, mode);
        } catch (e) {
            // ignore storage errors
        }
    }, [mode, isDark]);

    // Listen to system preference changes when in system mode
    useEffect(() => {
        if (typeof window === 'undefined') return;
        const mql = window.matchMedia('(prefers-color-scheme: dark)');
        const listener = () => {
            if (mode === 'system') {
                applyHtmlClass(mql.matches);
            }
        };
        try {
            // Modern browsers
            mql.addEventListener('change', listener);
        } catch {
            // Safari/older: addListener is used on older Safari versions
            mql.addListener(listener);
        }
        return () => {
            try {
                mql.removeEventListener('change', listener);
            } catch {
                mql.removeListener(listener);
            }
        };
    }, [mode]);

    const setMode = useCallback((newMode: ThemeMode) => {
        setModeState(newMode);
    }, []);

    const setLight = useCallback(() => setMode('light'), [setMode]);
    const setDark = useCallback(() => setMode('dark'), [setMode]);
    const setSystem = useCallback(() => setMode('system'), [setMode]);

    const toggle = useCallback(() => {
        // Toggle only between light and dark for the button convenience.
        // If currently in system mode, resolve current to actual and flip.
        const effectiveDark =
            mode === 'system' ? getSystemPrefersDark() : mode === 'dark';
        setMode(effectiveDark ? 'light' : 'dark');
    }, [mode, setMode]);

    return { mode, isDark, setMode, setLight, setDark, setSystem, toggle };
}
