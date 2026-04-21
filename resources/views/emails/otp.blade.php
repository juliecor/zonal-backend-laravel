<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $appName }} Verification Code</title>
  </head>
  <body style="margin:0; background:#f7f7fb; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; color:#0f1f38;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="padding:24px;">
      <tr>
        <td align="center">
          <table role="presentation" cellpadding="0" cellspacing="0" width="560" style="max-width:560px; background:#ffffff; border:1px solid #ececf3; border-radius:12px; overflow:hidden; box-shadow:0 8px 24px rgba(15,31,56,0.06);">
            <tr><td style="padding:20px 24px; background:#1e3a8a; color:#fff; font-weight:700;">{{ $appName }}</td></tr>
            <tr>
              <td style="padding:24px;">
                <h1 style="margin:0 0 12px; font-size:18px;">Verify your email</h1>
                <p style="margin:0 0 16px; color:#36455a;">Use the 6‑digit code below to complete your sign up. It expires in 10 minutes.</p>
                <div style="display:inline-block; font-size:28px; font-weight:700; letter-spacing:6px; padding:10px 16px; border-radius:10px; background:#f3f6ff; color:#1e3a8a; border:1px solid #e3e9ff;">
                  {{ $code }}
                </div>
                <p style="margin:18px 0 0; font-size:12px; color:#6b7585;">If you didn’t request this, you can ignore this email.</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
  </html>
