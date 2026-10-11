# Design Tokens & UI Architecture Specification (DESIGN.md)
## Visual Reference: BizLink / Ronas IT Canvas Design System

### 1. Palette & Surface Colors
- **Canvas Base**: `#F5F6F1` (Warm Bone / Soft Sand / Ecru)
- **Sidebar Surface**: `#FFFFFF` (Pure White) with hairline separator `border-r border-black/[0.06]`
- **Hero & Accent Surfaces**: `#EEF0E9` (Soft Cream / Warm Sand)
- **Standard Card Surface**: `#FFFFFF` (Pure White) with border `border-black/[0.05]` and subtle shadow `shadow-[0_2px_8px_rgba(0,0,0,0.02)]`
- **Featured / Dark Focus Surface**: `#1C1D1F` (Charcoal Black) with high-contrast `#FFFFFF` typography
- **Active Navigation Pill**: `#ECEEE8` (Warm Canvas Tint) with `text-stone-900 font-semibold rounded-xl`
- **Primary Action Buttons**: `#18181B` (Pitch Charcoal Pill) with `#FFFFFF` text and subtle hover lift
- **Typography Colors**:
  - Headings & Primary: `#18181B` / `#1F2937`
  - Body & Labels: `#4B5563` / `#52525B`
  - Muted / Meta: `#71717A` / `#8C9086`
  - Borders: `border-black/[0.06]` / `border-stone-200/80`

### 2. Geometry & Spacing
- **Outer Shell**: Fixed Left Sidebar (`w-64`), Top Header integrated seamlessly with Canvas `#F5F6F1`
- **Card Radii**:
  - Hero Overview Box: `rounded-3xl` (`24px` / `1.5rem`)
  - Content Cards: `rounded-2xl` (`16px` / `1rem`)
  - Pill Navigation & CTAs: `rounded-full` or `rounded-xl` (`12px`)
- **Spacing Scale**:
  - Hero Card Internal Padding: `p-6` to `p-7`
  - Kanban Column Gap: `gap-5`
  - Card Internal Padding: `p-4.5` to `p-5`

### 3. Dashboard Component Anatomy
- **Hero Overview Strip (`bg-[#EEF0E9] rounded-3xl`)**:
  1. Zone 1: Mini Bar Chart (CSS vertical bars for 5 days of inventory movement)
  2. Zone 2: Radial Arc Dial / Gauge (SVG arc half-circle gauge: "88% Ketersediaan Stok" / "Stok Sehat")
  3. Zone 3: Primary Counter (Big Monospaced number e.g. "53" / "Total SKU Terdaftar")
  4. Zone 4: Financial Valuation (Admin: "Rp 99.815.000" / "Nilai Aset Terdaftar →") or Logistics Volume (Staff: "132 Unit" / "Total Barang Masuk →")
- **Kanban Status Board (4 Columns)**:
  - Header: Counter badge (`Stok Kritis`, `Perlu Reorder`, `Stok Aman`, `Mutasi Terkini`) with `↓` chevron
  - Product Cards:
    - Bold Product Title
    - Monospaced SKU Tag
    - Category pill / label
    - Sisa Stok, Defisit, & Fast Action "Restock Masuk" CTA
    - Special Focused Card: Charcoal `#1C1D1F` with white typography for the most critical inventory item.
- **Ledger & Aggregate Table**: Clean minimal white cards (`rounded-2xl`) with hairline borders and tabular data.
