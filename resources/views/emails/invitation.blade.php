<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $appName }} Invitation</title>
  </head>
  <body style="margin:0; background:#f5f0eb; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color:#0f1f38;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f5f0eb; padding:32px 12px;">
      <tr>
        <td align="center">
          <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="max-width:600px; background:#ffffff; border:1px solid #e8e0d8; border-radius:14px; box-shadow:0 4px 24px rgba(15,31,56,0.06); overflow:hidden;">
            <!-- Header -->
            <tr>
              <td style="background:#1e3a8a; padding:20px 24px; color:#f5f0eb;">
                <table role="presentation" width="100%">
                  <tr>
                    <td style="font-size:0; line-height:0;" width="36" valign="middle">
                      <!-- Simple inline logo mark -->
                      <div style="width:30px;height:30px;border:1.5px solid #c9a84c;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;color:#c9a84c;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <polygon points="3 9 12 2 21 9 21 22 3 22"/>
                          <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                      </div>
                    </td>
                    <td style="padding-left:10px; font-size:18px; font-weight:700; font-family: 'Segoe UI', Roboto, Arial, sans-serif; letter-spacing:.2px;" valign="middle">
                      {{ $appName }}
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

            <!-- Body -->
            <tr>
              <td style="padding:28px 24px 8px 24px;">
                <h1 style="margin:0 0 10px; font-size:22px; color:#0f1f38;">You're invited to join {{ $appName }} 🎉</h1>
                <p style="margin:0; color:#36455a; line-height:1.6;">
                  Click the button below to accept your invitation and create your account.
                </p>
              </td>
            </tr>

            <!-- CTA -->
            <tr>
              <td style="padding:18px 24px 8px 24px;">
                <a href="{{ $inviteUrl }}" style="display:inline-block; background:#1e3a8a; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:600; box-shadow:0 6px 18px rgba(30,58,138,0.25);">Accept Invitation</a>
              </td>
            </tr>

            <!-- Fallback link -->
            <tr>
              <td style="padding:18px 24px 24px 24px;">
                <p style="margin:0; font-size:12px; color:#6b7585;">If the button doesn't work, paste this link into your browser:</p>
                <p style="margin:8px 0 0; font-size:12px; color:#1e3a8a; word-break:break-all; text-decoration:underline;">
                  {{ $inviteUrl }}
                </p>
              </td>
            </tr>

            <!-- Footer -->
            <tr>
              <td style="background:#faf7f3; border-top:1px solid #efe7df; padding:16px 24px; font-size:12px; color:#6b7585;">
                © {{ date('Y') }} {{ $appName }} · All rights reserved
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
  </html>
