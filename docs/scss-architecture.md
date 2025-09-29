# SCSS Architecture

This project uses a layered SCSS architecture that separates concerns for better maintainability and scalability.

## File Structure

```
src/scss/
├── _variables.scss                 # Design system variables
├── components/                     # Component-specific styles
│   ├── _modal-base.scss            # Base modal component
│   ├── _loading-modal.scss         # Loading modal component
│   └── _prompt-modal.scss          # Prompt modal component
├── layout/                         # Structural positioning and sizing
│   └── _modal.scss                 # Modal layout styles
└── visual/                         # Appearance, colors, and effects
    └── _modal.scss                 # Modal visual styles
```

## Architecture Layers

### 1. Variables Layer (`_variables.scss`)

- **Purpose**: Centralized design system values
- **Contents**: Colors, spacing, typography, shadows, z-index, animations
- **Philosophy**: Only includes variables that are actually used in the codebase
- **Benefits**: Single source of truth for design tokens, easy to maintain and update

### 2. Layout Layer (`layout/`)

- **Purpose**: Pure structural positioning and sizing
- **Contents**: Display, position, width, height, margins, padding, flexbox/grid properties
- **Restrictions**: No visual properties (colors, shadows, etc.)
- **Benefits**: Layout changes don't affect visual appearance, easier to debug positioning issues

### 3. Visual Layer (`visual/`)

- **Purpose**: Appearance, colors, typography, and effects
- **Contents**: Background, color, font, border-radius, box-shadow, transitions
- **Restrictions**: No structural properties
- **Benefits**: Visual changes don't affect layout, easier to maintain consistent appearance

### 4. Component Layer (`components/`)

- **Purpose**: Component-specific styles that extend base components
- **Contents**: Component variations, specific styling overrides
- **Pattern**: Uses `@extend` to inherit from base components
- **Benefits**: Combines layout and visual layers through base components, promotes code reuse

## Naming Conventions

### File Naming
- **Component-specific naming**: Files are named by component (e.g., `_modal.scss`) within their architectural layer
- **Consistent structure**: Each component has corresponding files in layout, visual, and components layers

### CSS Class Naming
- **BEM methodology**: CSS classes follow Block__Element--Modifier pattern
- **Consistent prefixes**: All modal classes use `wc-ai-rr-` prefix
- **Example**: `.wc-ai-rr-modal__content--loading`

## Usage Patterns

### Base Components
```scss
// In component files
.wc-ai-rr-loading-modal {
  @extend .wc-ai-rr-modal;  // Inherit base modal styles
  // Add component-specific styles
}
```

### Variables Access
```scss
// Access design tokens using map-get
@use "../variables" as tokens;

.my-element {
  color: map-get(tokens.$colors, text-primary);
  padding: map-get(tokens.$spacing, lg);
}
```

### Import Patterns
```scss
// Use @use with namespacing to avoid conflicts
@use "layout/modal" as layout;
@use "visual/modal" as visual;
```

### Layered Architecture
```scss
// Base component combines layout and visual layers
@use "../variables";
@use "../layout/modal" as layout;
@use "../visual/modal" as visual;
```

## Benefits

### Separation of Concerns
- **Clear distinction** between structure and appearance
- **Independent modification** of layout vs visual properties
- **Easier debugging** when issues are isolated to specific layers

### Maintainability
- **Easy to modify** colors, spacing, or layout independently
- **Centralized design tokens** ensure consistency across components
- **Clear file organization** makes it easy to find and update styles

### Scalability
- **Easy to add new components** following the same patterns
- **Consistent architecture** across all components
- **Reusable base components** reduce code duplication

### Performance
- **Only includes variables and styles** that are actually used
- **Efficient compilation** with organized import structure
- **Smaller bundle sizes** due to unused code elimination

## Best Practices

### When Adding New Components

1. **Create base component** in `components/` folder
2. **Add layout styles** in `layout/` folder
3. **Add visual styles** in `visual/` folder
4. **Use consistent naming** following established patterns
5. **Extend base components** using `@extend` for variations

### When Modifying Existing Components

1. **Identify the layer** that needs changes (layout vs visual)
2. **Make changes in the appropriate layer** file
3. **Test across all component variations** to ensure consistency
4. **Update variables** if new design tokens are needed

### Variable Management

1. **Add new variables** only when actually needed
2. **Use existing variables** before creating new ones
3. **Remove unused variables** during cleanup
4. **Group related variables** logically in the variables file

## Examples

### Adding a New Button Component

1. **Create `components/_button-base.scss`**:
```scss
@use "../variables" as tokens;
@use "../layout/button" as layout;
@use "../visual/button" as visual;

.wc-ai-rr-button {
  // Base button styles
}
```

2. **Create `layout/_button.scss`**:
```scss
@use "../variables" as tokens;

.wc-ai-rr-button {
  display: inline-block;
  padding: map-get(tokens.$spacing, sm) map-get(tokens.$spacing, lg);
  // Other layout properties
}
```

3. **Create `visual/_button.scss`**:
```scss
@use "../variables" as tokens;

.wc-ai-rr-button {
  background: map-get(tokens.$colors, primary);
  color: map-get(tokens.$colors, background);
  border-radius: map-get(tokens.$border-radius, base);
  // Other visual properties
}
```

4. **Create component variations**:
```scss
.wc-ai-rr-button--secondary {
  @extend .wc-ai-rr-button;
  background: map-get(tokens.$colors, gray-100);
  color: map-get(tokens.$colors, text-primary);
}
```

This architecture ensures that the codebase remains maintainable, scalable, and consistent as it grows.
