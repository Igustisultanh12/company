export const prefersReducedMotion = () =>
    typeof window !== 'undefined' && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

export const isTouch = () => typeof window !== 'undefined' && window.matchMedia?.('(hover: none), (pointer: coarse)').matches;
