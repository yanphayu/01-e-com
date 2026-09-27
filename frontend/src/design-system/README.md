# TRINITY Design System

Reusable UI primitives and design tokens for the frontend. Components live in
`src/design-system/` and consume the global tokens defined in `src/style.css`.

## Tokens

All tokens are CSS custom properties (see `src/style.css`). A JS mirror is
available in `tokens.js` for programmatic access.

| Group     | Token            | Value (default)                         | Usage                          |
|-----------|------------------|-----------------------------------------|--------------------------------|
| Color     | `--bg`           | `#F8FAFC`                               | Page background                |
| Color     | `--surface`      | `#FFFFFF`                               | Cards, inputs, panels          |
| Color     | `--surface-2`    | `#F1F5F9`                               | Subtle fills                   |
| Color     | `--surface-3`    | `#E2E8F0`                               | Strong fills and borders       |
| Color     | `--text`         | `#0F172A`                               | Primary text                   |
| Color     | `--text-muted`   | `#64748B`                               | Secondary text                 |
| Color     | `--border`       | `rgba(15,23,42,.11)`                    | Hairline borders               |
| Color     | `--border-strong`| `rgba(15,23,42,.22)`                    | Hover borders                  |
| Color     | `--accent`       | `#2563EB`                               | Primary brand / actions        |
| Color     | `--accent-dark`  | `#1D4ED8`                               | Accent hover                   |
| Color     | `--accent-soft`  | `rgba(37,99,235,.10)`                   | Soft accent fills              |
| Color     | `--success`      | `#059669`                               | Positive states                |
| Color     | `--danger`       | `#DC2626`                               | Errors                         |
| Color     | `--warning`      | `#D97706`                               | Warnings                       |
| Color     | `--info`         | `#0284C7`                               | Informational states           |
| Radius    | `--radius-sm`    | `9px`                                   | Inputs, small controls         |
| Radius    | `--radius-md`    | `14px`                                  | Cards                          |
| Radius    | `--radius-lg`    | `18px`                                  | Large panels, modals           |
| Shadow    | `--shadow-sm/md/lg` | layered soft shadows                    | Elevation                      |
| Font      | `--font-sans`    | system stack                            | Body text                      |
| Font      | `--font-mono`    | JetBrainsMono NF, JetBrains Mono Nerd Font | Headings, code, labels       |

Change the look of the whole app by editing these variables in one place.

## Components

Import from the barrel: `import { BaseButton, BaseInput } from '../design-system'`

### BaseButton
Props: `variant` (`primary` | `ghost` | `secondary`), `size` (`sm` | `md` | `lg`),
`block`, `loading`, `disabled`, `type`, `to` (renders a `RouterLink` when set).

```vue
<BaseButton variant="primary" block :loading="loading" @click="submit">Save</BaseButton>
<BaseButton :to="{ name: 'login' }">Sign in</BaseButton>
```

### BaseInput
Supports `v-model`, `label`, `type`, `placeholder`, `autocomplete`, `error`,
and a `#suffix` slot (e.g. for a password visibility toggle).

```vue
<BaseInput v-model="email" label="Email" type="email" />
<BaseInput v-model="pw" :type="show ? 'text' : 'password'">
  <template #suffix><button class="toggle">…</button></template>
</BaseInput>
```

### BaseCard
Props: `tag`, `variant` (`default` | `flat` | `soft`), `padded`.

### BaseBadge
Props: `variant` (`neutral` | `accent` | `success` | `danger`), `size` (`sm` | `md`).

### BaseModal
`v-model` (open state), `title`, `size` (`sm` | `md` | `lg`), slots: `header`,
default, `footer`. Closes on overlay click and the × button.

## Usage in this project

`LoginForm` and `RegisterForm` already use `BaseInput` + `BaseButton`. New
views should build on these primitives to stay consistent.
