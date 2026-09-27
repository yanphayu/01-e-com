<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'TRINITY')</title>
    <style>
        body { margin: 0; padding: 0; }

        .preheader {
            display: none;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            color: transparent;
        }

        @media (prefers-color-scheme: dark) {
            .mail-page { background-color: #0B1220 !important; }
            .mail-card { background-color: #111C31 !important; border-color: #334155 !important; box-shadow: none !important; }
            .mail-hero { background-color: #0B1220 !important; border-bottom-color: #1E293B !important; }
            .mail-eyebrow, .mail-title, .mail-heading, .mail-digit, .mail-expiry { color: #F8FAFC !important; }
            .mail-copy, .mail-note { color: #CBD5E1 !important; }
            .mail-code-cell { background-color: #172554 !important; border-color: #3B82F6 !important; }
            .mail-divider { border-color: #1E293B !important; }
            .mail-footer { background-color: #0F172A !important; border-top-color: #1E293B !important; }
            .mail-footer-text { color: #94A3B8 !important; }
        }
    </style>
</head>
<body class="mail-page" style="margin: 0; padding: 0; background-color: #F1F5F9; color: #0F172A; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <div class="preheader">@yield('preheader')</div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width: 100%; background-color: #F1F5F9;">
        <tr>
            <td align="center" style="padding: 40px 16px;">
                <table role="presentation" class="mail-card" width="100%" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 560px; background-color: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 40px -28px rgba(15, 23, 42, 0.35);">

                    <tr>
                        <td style="height: 4px; line-height: 4px; font-size: 0; background-color: #2563EB;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td class="mail-hero" align="center" style="padding: 32px 32px 28px; background-color: #0F172A; border-bottom: 1px solid #1E293B;">
                            <p class="mail-eyebrow" style="margin: 0 0 14px; color: #60A5FA; font-family: 'Courier New', Courier, monospace; font-size: 12px; font-weight: 700; letter-spacing: 0.22em;">TRINITY</p>
                            <h1 class="mail-title" style="margin: 0; color: #F8FAFC; font-family: 'Courier New', Courier, monospace; font-size: 24px; line-height: 1.25; letter-spacing: -0.03em;">@yield('headline')</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 36px 32px 32px; text-align: center;">
                            <h2 class="mail-heading" style="margin: 0; color: #0F172A; font-family: 'Courier New', Courier, monospace; font-size: 19px; line-height: 1.35;">@yield('heading')</h2>
                            <p class="mail-copy" style="margin: 14px 0 26px; color: #475569; font-size: 15px; line-height: 1.7;">@yield('copy')</p>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: 0 auto 22px;">
                                <tr>
                                    @foreach (str_split($otp) as $digit)
                                        <td class="mail-code-cell" align="center" style="width: 44px; height: 56px; padding: 0; background-color: #EFF6FF; border: 1px solid #BFDBFE; border-left-width: 0;">
                                            <span class="mail-digit" style="color: #1D4ED8; font-family: 'Courier New', Courier, monospace; font-size: 26px; font-weight: 700; line-height: 56px;">{{ $digit }}</span>
                                        </td>
                                        @if (! $loop->last)
                                            <td style="width: 6px; font-size: 0; line-height: 0;">&nbsp;</td>
                                        @endif
                                    @endforeach
                                </tr>
                            </table>

                            <p class="mail-expiry" style="margin: 0; color: #334155; font-size: 13px; line-height: 1.6;">
                                @yield('expiry')
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 26px 0 0;">
                                <tr>
                                    <td class="mail-divider" style="border-top: 1px solid #E2E8F0; line-height: 0; font-size: 0;">&nbsp;</td>
                                </tr>
                            </table>

                            <p class="mail-note" style="margin: 22px 0 0; color: #64748B; font-size: 13px; line-height: 1.65;">@yield('note')</p>
                        </td>
                    </tr>

                    <tr>
                        <td class="mail-footer" align="center" style="padding: 20px 24px; background-color: #F8FAFC; border-top: 1px solid #E2E8F0;">
                            <p class="mail-footer-text" style="margin: 0; color: #64748B; font-size: 12px; line-height: 1.6;">
                                &copy; {{ date('Y') }} TRINITY. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
