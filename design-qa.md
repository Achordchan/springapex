# Design QA

- Date: 2026-07-21
- Scope: Cumulative visual QA for shared Heroes and follow-up page refinements
- Source visual truth: `evidence/hero-unification/reference-about-2560.png`
- Combined comparison: `evidence/hero-unification/final-reference-comparison-2560.jpg`
- Implementation screenshots: `evidence/hero-unification/after-*-2560.png`, `after-*-1326.png`, and `after-*-390.png`
- Viewports: 2560 × 1440, 1326 × 921, and 390 × 844
- Routes: About, Products, Solutions, Capabilities, Contact, Product detail, and Resources

## Final Findings

No actionable P0, P1, or P2 visual differences remain.

- Fonts and typography: Every non-home Hero now uses the About benchmark's 40px desktop title, 17px supporting copy, light display weight, line-height, and existing project font stack. Mobile titles resolve to 38px without clipping.
- Spacing and alignment: Desktop Heroes use the same 330px height and 1280px centered rail as About. At 2560px the content starts at x=640; the title, copy, and primary button align consistently. The primary button remains 44px high.
- Colors and visual tokens: Existing black, white, warm gray, border, and button tokens are reused. No new gradients, decorative cards, or unrelated visual effects were introduced.
- Image quality and crop: Existing high-resolution responsive images are reused. Products, Solutions, Contact, Capabilities, Resources, and Product detail have route-specific crop rules while preserving the shared Hero structure. About retains its original benchmark crop.
- Copy and controls: Existing English site copy remains unchanged except for concise route-specific Hero CTAs. Product-detail breadcrumb and both Hero CTAs remain functional.
- Responsive behavior: All tested routes report document scroll width equal to viewport width at 2560, 1326, and 390px. Mobile Capabilities and Resources use a left-biased blueprint crop to keep text readable. Product-detail artwork starts below the CTA row and remains a static responsive image at every breakpoint.
- Runtime and performance: Product-detail 3D rendering has been removed. The Hero no longer loads Three.js, GSAP, WebGL checks, an animation bundle, or a canvas; it uses the existing responsive product image instead.

## Comparison History

1. Shared Hero implementation
   - Replaced separate About, Products, Solutions, Capabilities, Contact, Product, Resources, generic Page, and solution-detail Hero structures with `parts/inner-hero.php`.
   - Preserved the home Hero as a separate design.

2. Responsive correction
   - P1: Capabilities and Resources imagery reduced mobile text contrast.
   - Fix: Shifted the blueprint crop to 18% horizontal position on mobile.
   - P1: Product-detail artwork overlapped the mobile CTA row.
   - Fix: Moved the product stage below the actions and reduced it to a 72% wide, 240px high presentation.

3. Interaction correction
   - P2: Product fallback catalog CTA was normalized twice in local preview and produced an invalid route.
   - Fix: Kept the fallback as `/resources/` until the shared navigation helper resolves it. Verified final href: `/preview/index.php?sa_page=resources`.

4. Cleanup and regression pass
   - Removed unused legacy Hero selectors for Products, Product detail, Solutions, About, Capabilities, and Contact, plus obsolete contact-point styles.
   - Rechecked the existing universal footer and page sections; no layout regression was observed in the captured desktop, medium, or mobile views.

5. Solutions Hero image correction
   - P1: The previous 1899 × 828 source was only 2.29:1, while the 1326 × 330 Hero slot is 4.02:1. `object-fit: cover` therefore cropped the tall spring and lower wire forms.
   - Fix: Generated and art-directed a new 3840 × 480 desktop source with all products inside the right safe area, plus 480/768/1200/1920/2560 AVIF and WebP variants.
   - Mobile keeps the original portrait-friendly source through `<picture media>` art direction, preventing the ultra-wide desktop asset from reducing mobile readability.
   - Comparison evidence: `evidence/hero-unification/solutions-v2-v3-comparison-1326.jpg`.
   - Final browser checks: 330px Hero height, no horizontal overflow, full product arrangement at 1326px, dedicated mobile image at 390px, and 0 browser warnings/errors.

6. Capabilities and Resources Hero image correction
   - P1: The previous 1600 × 560 blueprint source was 2.86:1 and lost the right-side spring arrangement when covering the 1326 × 330 Hero slot.
   - Fix: Generated a dedicated 3840 × 480 desktop source with the complete spring capability set inside the right safe area. Capabilities and Resources share the new desktop asset while retaining the original blueprint image for mobile art direction.
   - Responsive assets: 480/768/1200/1920/2560 AVIF and WebP variants; the 2560px AVIF is approximately 11KB.
   - Comparison evidence: `evidence/hero-unification/capabilities-v3-v4-comparison-1326.jpg`.
   - Final browser checks: full image at 1326px and 2560px, readable mobile copy at 390px, no horizontal overflow, no redundant desktop preload on mobile, and 0 browser warnings/errors.

7. About value-point readability correction
   - P1: The three value titles rendered at 9px and their supporting copy at 7.5px, making the content difficult to read on the 1326px desktop layout.
   - Fix: Increased the value titles to 14px/600 weight, descriptions to 12px with 1.55 line-height, icons to 30px, and spacing between the icon, title, and copy. The existing three-column structure and restrained divider treatment remain unchanged.
   - Responsive behavior: The values stay in three columns on desktop and tablet, then become full-width stacked rows below 700px with 22px vertical padding and no horizontal overflow.
   - Evidence: `evidence/about-values/about-values-before-1326x921.png`, `about-values-after-1326x921.png`, `about-values-after-2560x1440.png`, `about-values-after-390x844.png`, and `about-values-before-after-1326.png`.
   - Final browser checks: 2560 × 1440, 1326 × 921, and 390 × 844 all preserve the story/image layout; value copy is fully visible and document scroll width equals viewport width.

8. Products conversion hierarchy correction
   - P1: The Products page repeated `Upload Your Drawing` in the Hero, application-help band, and final conversion panel, giving three visually equal calls to the same action.
   - Fix: Assigned one purpose to each stage of the page: the Hero now links to `Explore Product Range`, the help band offers `Talk to an Engineer`, and only the final conversion panel retains `Upload Your Drawing`.
   - Copy correction: The final panel now asks visitors to send their drawing and application requirements for an engineering review; its duplicate engineer button was removed.
   - Evidence: `evidence/products-cta/products-cta-before-1326x921.png`, `products-cta-after-1326x921.png`, `products-cta-after-390x844.png`, and `products-cta-before-after-1326.png`.
   - Final browser checks: the page contains exactly one drawing-upload CTA at 2560px, 1326px, and 390px; the Hero anchor resolves to `#product-categories`; no button text is clipped and no horizontal overflow occurs.

9. Product-detail 3D removal
   - Decision: Abandoned the interactive spring experiment and restored the Product-detail Hero to a static product presentation.
   - Cleanup: Removed the canvas mount point, dynamic module loader, WebGL/device capability checks, interactive label, canvas styles, Three.js/GSAP dependencies, build command, source module, and 580KB generated bundle.
   - Layout preservation: Kept the existing staged media wrapper and responsive crop rules so the desktop and mobile product image position remains unchanged.
   - Evidence: `evidence/product-static-hero/product-hero-before-1326x921.png`, `product-hero-after-1326x921.png`, `product-hero-after-390x844.png`, and `product-hero-before-after-1326.png`.
   - Final browser checks: no canvas, `data-product-transition`, `data-three-state`, interactive label, or 3D module request remains at 2560px, 1326px, or 390px.

10. Product Materials and Applications readability correction
   - P1: Feature labels rendered at 9px, supporting copy at 14px, section headings at 19px, and links at 13px, making the lower Product-detail content difficult to scan.
   - Fix: Increased headings to 24px desktop/23px mobile, supporting copy to 16px, feature labels to 13px/500 weight, links to 14px, icon circles to 48px, and icons to 22px. Section and column spacing were increased without adding decorative cards or effects.
   - Responsive behavior: Desktop retains the two-column Materials/Applications layout. Tablet stacks the sections, while 390px mobile keeps two feature columns to preserve readable labels without creating an excessively long page.
   - Evidence: `evidence/product-meta/product-meta-before-1326x921.png`, `product-meta-after-1326x921.png`, `product-meta-after-390x844.png`, and `product-meta-before-after-1326.png`.
   - Final browser checks: 2560 × 1440, 1326 × 921, and 390 × 844 have no horizontal overflow or clipped feature labels.

11. Project-wide forced-wrap and narrow-copy correction
   - P1: Shared inner-page Hero copy was limited to 360px while supporting text was capped at approximately 265px. Stored subtitle line breaks were rendered as `<br>`, forcing short sentences into two or three lines even when safe horizontal space remained.
   - Root fix: Normalized Hero subtitle whitespace in `parts/inner-hero.php`, expanded the desktop copy rail to 480px and supporting copy to 46ch, and allowed 38ch on tablet/mobile where the full content rail is available.
   - Related corrections: Removed the 22ch cap from Home pillar descriptions, widened the Capabilities introduction and CTA copy, and expanded/increased the About Story paragraph without changing intentional display-heading line breaks.
   - 1326px result: Products 2 lines, Solutions 1, Capabilities 2, About 2, Contact 1, and Product detail 2. Every shared Hero now contains zero forced `<br>` elements.
   - Responsive result: The same routes have no horizontal overflow at 2560 × 1440 or 390 × 844. Product-detail copy maintains a 32px gap above its mobile image stage.
   - Evidence: `evidence/text-wrap-audit/01-before-*-1326.png`, `02-after-*-1326.png`, `03-after-*-390.png`, `04-after-*-2560.png`, and `product-before-after-1326.png`.

## Validation

- [x] Shared Hero dimensions match the About benchmark
- [x] All non-home preview routes render the shared Hero
- [x] 2560 × 1440 desktop layout checked
- [x] 1326 × 921 medium desktop layout checked
- [x] 390 × 844 mobile layout checked
- [x] No horizontal overflow on tested routes
- [x] Product static Hero verified with no 3D canvas at any breakpoint
- [x] Removed Three.js/GSAP runtime and product-transition build artifacts
- [x] Browser console warnings and errors: 0 on final static product Hero check
- [x] PHP syntax, JavaScript syntax, CSS parsing, and package metadata checked
- [x] Debug and Mock residue search checked
- [x] Solutions desktop and mobile image art direction checked
- [x] Capabilities and Resources desktop and mobile image art direction checked
- [x] About value-point typography and responsive stacking checked
- [x] Products CTA hierarchy and destination checks completed
- [x] Product Materials and Applications typography checked across desktop and mobile
- [x] Shared Hero and narrow-copy wrapping audited across all seven core routes

## Mobile Home Hero Redesign — 2026-07-21

- Source visual truth: `qa-artifacts/mobile-home/about-reference-430x932.png` for the approved mobile Hero proportions, plus `qa-artifacts/mobile-home/home-before-430x932.png` for the rejected Home state.
- Implementation screenshot: `qa-artifacts/mobile-home/home-final-430x932.png`.
- Full-view comparison evidence: `qa-artifacts/mobile-home/reference-vs-home-430.png` and `qa-artifacts/mobile-home/before-vs-after-430.png`.
- Focused region comparison evidence: the two 430 × 700 combined comparisons cover the complete header, title, supporting copy, CTA hierarchy, and product image transition; no smaller crop was needed because every changed element remains legible at native scale.
- Responsive evidence: `qa-artifacts/mobile-home/home-final-320x700.png`, `home-final-390x844.png`, `home-final-430x932.png`, `home-final-768x1024.png`, and `home-desktop-regression-1440x1000.png`.
- Viewports: 320 × 700, 390 × 844, 430 × 932, 768 × 1024, and 1440 × 1000.
- States: Home Hero with the mobile menu closed, and `qa-artifacts/mobile-home/mobile-menu-430x932.png` with the menu open.

### Findings and Comparison History

1. Initial rejected state
   - P1 typography/layout: the desktop Hero was stacked into an 823.9px mobile section, leaving weak visual grouping between the two CTAs and the product image.
   - P1 header density: Logo, `Get a Quote`, language selector, and menu competed in one 430px row.
   - P2 CTA hierarchy: the two actions appeared side by side with similar visual priority.

2. First implementation pass
   - Fix: hid the header quote button below 860px, restored it as the final mobile-menu action, changed the Hero to one restrained light-gray composition, stacked the secondary action as a text link, and moved the existing spring image directly under the conversion content.
   - P1 remaining issue: a temporary `14ch` title cap forced `Engineered Around` into two lines, producing four title lines at 430px.
   - P2 remaining issue: the white image background created a visible tone change against the light-gray copy surface.
   - Evidence: `qa-artifacts/mobile-home/home-after-pass1-430x932.png`.

3. Final implementation pass
   - Fix: allowed the explicit three-line title to use the complete mobile content rail and normalized the existing product image brightness so its white background integrates with the Hero surface.
   - Post-fix evidence: the title renders in three lines at 320, 390, 430, and 768px; no viewport reports horizontal overflow; header controls do not overlap; all product images report a valid natural size.
   - The 430px Hero ends at y=695.1 instead of y=894.9 in the rejected state, while retaining the complete copy, both actions, and product image.

### Required Fidelity Surfaces

- Fonts and typography: Existing project font families remain unchanged. Mobile Home uses 34px at 320px, 38px at 390px, 41.28px at 430px, and 48px at 768px with a `.98` line height and controlled negative tracking. The explicit three-line title does not clip or add an unintended fourth line.
- Spacing and layout rhythm: Mobile content padding is 16px at phone widths and 24px at tablet width. The CTA group, image stage, industry strip, and following section remain distinct without the previous dead space. Desktop retains its original 460px Hero and two-column composition.
- Colors and visual tokens: The existing black, white, gray, border, and button tokens remain in use. The only new surface is a restrained `#f2f3f3` mobile Hero background; no gradient, shadow, decorative card, or unrelated effect was added.
- Image quality and asset fidelity: The existing responsive `hero-spring-v2` sources are reused. No generated substitute, placeholder, CSS illustration, or SVG product drawing was introduced. The spring remains sharp and fully recognizable at every tested width.
- Copy and content: Existing English Home copy is unchanged. Primary conversion remains `Upload Your Drawing`; `Explore Solutions` is retained as the lower-priority action. `Get a Quote` remains accessible from the mobile menu.
- Icons and controls: Existing Iconoir globe, menu, close, upload, and arrow icons are reused. The menu opens and closes, the mobile quote link is visible, and the open state remains 430px wide without overflow.
- Accessibility and behavior: The existing semantic `button`, `details`, navigation labels, `aria-expanded`, focus management, and menu scroll lock remain intact. Mobile controls remain at least 34px at 320px and 40px at wider phone widths.

### Runtime Verification

- Primary interaction tested: mobile menu open/close and visibility of `Get a Quote`.
- Browser console warnings/errors: 0 at the final 430px and 1440px checks.
- Horizontal overflow: none at 320, 390, 430, 768, or 1440px.
- Desktop regression: quote and language controls remain visible; menu remains hidden; Hero stays 460px high.

## Follow-up Polish

No P3 follow-up is required for this scoped mobile Home Hero redesign.

## Right-side Customer Support — 2026-07-22

- Implementation screenshots: `qa-artifacts/support-widget/products-support-closed-1440x1000.png`, `products-support-open-1440x1000.png`, `products-support-closed-390x844.png`, `products-support-open-final-390x844.png`, and `products-support-open-320x700.png`.
- Viewports: 1440 × 1000, 390 × 844, and 320 × 700.
- States: launcher collapsed, panel expanded, close-button action, and Escape-key dismissal.

### Findings and Iteration

1. P1 placement correction
   - Initial implementation used the conventional bottom-right position, conflicting with the reserved bottom-right product area identified by the user.
   - Fix: moved the launcher to the vertical center of the right edge and made the desktop panel open inward to the left. The bottom-right hit-test is clear at all tested viewports.

2. P2 mobile positioning correction
   - A transformed fixed parent caused the first mobile panel pass to align at x=0 instead of preserving equal page margins.
   - Fix: anchored the mobile panel absolutely to the right-side widget. Final positions are x=12/right=378 at 390px and x=12/right=308 at 320px.

3. Accessibility correction
   - The compact mobile launcher hides its visible label, so the button required an explicit accessible name.
   - Fix: added dynamic `Open customer support` / `Close customer support` labels, retained `aria-expanded` and `aria-controls`, returned focus to the launcher after close/Escape, and verified both dismissal paths.

### Final Review

- Layout: desktop uses a 50px right-side vertical launcher and a 360px inward-opening panel. Mobile uses a 48px right-side button and a centered panel with 12px side margins.
- Style: existing monochrome colors, borders, radii, type scale, buttons, and Iconoir assets are reused; no new dependency, gradient, generated art, or live-chat imitation was introduced.
- Content: project contact URL, configured email, configured telephone number, and business hours come from the existing WordPress content layer.
- Responsiveness: no horizontal overflow at 1440, 390, or 320px. Email content remains fully visible at 320px.
- Behavior: open, close button, outside-click close, and Escape close are implemented without polling. Browser console warnings/errors: 0.

### Real contact links — 2026-07-22

- Email now opens a real compose action: `mailto:victoria@springapex.cn?subject=SpringApex%20Project%20Inquiry`.
- WhatsApp now opens the configured customer-care number in a new tab: `https://wa.me/8618796422510` with a prefilled project-inquiry message.
- Evidence: `qa-artifacts/support-widget/products-support-real-links.png`.
- Browser DOM verification confirmed both href values, the WhatsApp `_blank` target, `noopener noreferrer`, zero horizontal overflow, and zero console warnings/errors.

### Horizontal support launcher — 2026-07-22

- P2: The desktop launcher used vertical writing, making the customer-support label feel unnatural and difficult to scan.
- Fix: replaced the vertical 50px tab with a horizontal black capsule while keeping it fixed at the right-side midpoint and preserving the left-opening panel.
- Evidence: `qa-artifacts/support-widget/products-support-horizontal-closed-1008x921.png` and `products-support-horizontal-open-1008x921.png`.
- Exact user viewport verification: at 1008 × 921 the label uses `horizontal-tb`, has no transform, reports no horizontal overflow, and leaves the bottom-right area clear.
- Mobile remains compact: at 390 × 844 the text label is hidden and the launcher remains a 48 × 48px circular control.

### Compact non-blocking launcher — 2026-07-22

- P1: The horizontal capsule fixed the reading direction but remained 212.4px wide at the 1008 × 921 review viewport, obscuring too much page content.
- Fix: reduced the persistent desktop launcher to a 50 × 50px icon button at the right-side midpoint. The horizontal `Customer Support` label is now transient and appears only on hover or keyboard focus.
- Evidence: `qa-artifacts/support-widget/products-support-compact-closed-1008x921.png` and `products-support-compact-open-1008x921.png`.
- Final checks: no horizontal overflow, bottom-right area remains clear, and the persistent launcher footprint is reduced by approximately 78% compared with the horizontal capsule.

final result: passed
