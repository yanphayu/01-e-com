# TRINITY Design System

Reusable UI primitives and design tokens for the frontend. Components live in
`src/design-system/` and consume the global tokens defined in `src/style.css`.

## Tokens

All tokens are CSS custom properties (see `src/style.css`). A JS mirror is
available in `tokens.js` for programmatic access.

| Group     | Token            | Value (default)        | Usage                          |
|-----------|------------------|------------------------|--------------------------------|
| Color     | `--bg`           | `#fafaf8`              | Page background                |
| Color     | `--surface`      | `#ffffff`              | Cards, inputs, panels          |
| Color     | `--surface-2`    | `#f6f5f2`              | Subtle fills                   |
| Color     | `--text`         | `#1c1b19`              | Primary text                   |
| Color     | `--text-muted`   | `#76726b`              | Secondary text                 |
| Color     | `--border`       | `rgba(28,27,25,.10)`   | Hairline borders               |
| Color     | `--border-strong`| `rgba(28,27,25,.18)`   | Hover borders                  |
| Color     | `--accent`       | `#2f9e6b`              | Primary brand / actions        |
| Color     | `--accent-dark`  | `#247a53`              | Accent hover                   |
| Color     | `--accent-soft`  | `rgba(192,99,63,.10)`  | Soft accent fills              |
| Color     | `--success`      | `#2f9e6b`              | Positive states                |
| Color     | `--danger`       | `#d1493f`              | Errors                         |
| Radius    | `--radius-sm`    | `6px`                  | Inputs, small controls         |
| Radius    | `--radius-md`    | `10px`                 | Cards                          |
| Radius    | `--radius-lg`    | `14px`                 | Large panels, modals           |
| Shadow    | `--shadow-sm/md/lg` | layered soft shadows | Elevation                      |
| Font      | `--font-sans`    | Inter, system-ui       | Body text                      |
| Font      | `--font-serif`   | Playfair Display       | Headings / brand               |

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
