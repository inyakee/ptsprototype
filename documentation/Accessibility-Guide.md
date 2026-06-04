# Accessibility Guide

## Implemented

- Semantic HTML landmarks
- Skip link
- Keyboard-focus styles
- ARIA labels for navigation, menu, maps, and interactive controls
- Accessible form labels and live status messages
- Sufficient color contrast for body text and controls
- `prefers-reduced-motion` support
- Accordion controls with `aria-expanded`

## QA Checklist

- Navigate every page with only the keyboard.
- Confirm focus order follows visible page order.
- Confirm form errors are understandable.
- Test with browser zoom at 200%.
- Run Lighthouse accessibility checks before launch.
