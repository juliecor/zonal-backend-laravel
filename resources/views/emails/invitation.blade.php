<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $appName }} Invitation</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  </head>
  <body style="margin:0; padding:0; background:#EEE9E3; font-family:'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif; color:#1a2640;">

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#EEE9E3; padding:48px 16px;">
      <tr>
        <td align="center">

          <!-- Outer Card -->
          <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;">

            <!-- ── Header ── -->
            <tr>
              <td style="background:linear-gradient(135deg, #0d2461 0%, #1a3a8f 60%, #1e4dba 100%); border-radius:16px 16px 0 0; padding:0; overflow:hidden;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <!-- Decorative left accent bar -->
                    <td width="5" style="background:linear-gradient(180deg,#c9a84c,#f0cc6e,#c9a84c); width:5px; min-width:5px;">&nbsp;</td>

                    <td style="padding:28px 32px;">
                      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                          <!-- App icon + name -->
                          <td valign="middle">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                              <tr>
                                <td valign="middle" style="padding-right:12px;">
                                  <div style="width:38px;height:38px;border:1.5px solid rgba(201,168,76,0.7);border-radius:9px;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.06);">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c9a84c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                      <polygon points="3 9 12 2 21 9 21 22 3 22"/>
                                      <polyline points="9 22 9 12 15 12 15 22"/>
                                    </svg>
                                  </div>
                                </td>
                                <td valign="middle">
                                  <span style="font-family:'DM Sans',sans-serif; font-size:20px; font-weight:600; color:#ffffff; letter-spacing:0.3px;">{{ $appName }}</span>
                                </td>
                              </tr>
                            </table>
                          </td>

                          <!-- Brand logo / name -->
                          <td align="right" valign="middle">
                            @if(!empty($brandLogoUrl))
                              <a href="{{ $brandLink ?? '#' }}" target="_blank" style="text-decoration:none;">
                                <img src="{{ $brandLogoUrl }}" alt="{{ $brandName ?? 'Brand' }}" style="height:28px; display:inline-block; border:0; outline:none; opacity:.9;"/>
                              </a>
                            @elseif(!empty($brandName))
                              <span style="font-size:11px; font-family:'DM Sans',sans-serif; color:rgba(240,230,210,0.75); letter-spacing:0.8px; text-transform:uppercase; font-weight:500;">Powered by {{ $brandName }}</span>
                            @endif
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

            <!-- ── Hero divider line ── -->
            <tr>
              <td style="background:linear-gradient(90deg,#c9a84c,#f0cc6e,#c9a84c); height:2px; font-size:0; line-height:0;">&nbsp;</td>
            </tr>

            <!-- ── Body ── -->
            <tr>
              <td style="background:#ffffff; padding:44px 40px 32px 40px;">

                <!-- Eyebrow label -->
                <p style="margin:0 0 14px; font-size:11px; font-weight:600; letter-spacing:2px; text-transform:uppercase; color:#c9a84c; font-family:'DM Sans',sans-serif;">Team Invitation</p>

                <!-- Headline -->
                <h1 style="margin:0 0 18px; font-family:'Playfair Display', Georgia, serif; font-size:28px; font-weight:700; color:#0d2461; line-height:1.25; letter-spacing:-0.3px;">
                  You're invited to join<br/>{{ $appName }}
                </h1>

                <!-- Divider -->
                <div style="width:48px; height:2px; background:linear-gradient(90deg,#c9a84c,#f0cc6e); border-radius:2px; margin-bottom:20px;"></div>

                <!-- Body copy -->
                <p style="margin:0 0 32px; font-size:15px; color:#475569; line-height:1.7; font-family:'DM Sans',sans-serif; font-weight:300;">
                  An invitation has been sent to you to become part of the <strong style="color:#1a2640; font-weight:500;">{{ $appName }}</strong> platform. Click the button below to accept your invitation and set up your account — it only takes a moment.
                </p>

                <!-- CTA Button -->
                <table role="presentation" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="border-radius:10px; background:linear-gradient(135deg,#0d2461,#1e4dba); box-shadow:0 8px 24px rgba(13,36,97,0.30);">
                      <a href="{{ $inviteUrl }}" target="_blank" style="display:inline-block; padding:14px 32px; font-family:'DM Sans',sans-serif; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none; letter-spacing:0.4px; border-radius:10px;">
                        Accept Invitation &nbsp;→
                      </a>
                    </td>
                  </tr>
                </table>

              </td>
            </tr>

            <!-- ── Fallback link block ── -->
            <tr>
              <td style="background:#faf8f5; border-top:1px solid #ede8e0; border-left:none; border-right:none; padding:20px 40px;">
                <p style="margin:0 0 6px; font-size:12px; color:#94a3b8; font-family:'DM Sans',sans-serif;">Button not working? Copy and paste this link into your browser:</p>
                <p style="margin:0; font-size:12px; color:#1e4dba; word-break:break-all; font-family:'DM Sans',sans-serif; text-decoration:underline; text-underline-offset:2px;">{{ $inviteUrl }}</p>
              </td>
            </tr>

            <!-- ── Footer ── -->
            <tr>
              <td style="background:#0d2461; border-radius:0 0 16px 16px; padding:20px 40px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="font-size:12px; color:rgba(240,234,220,0.55); font-family:'DM Sans',sans-serif; font-weight:300; letter-spacing:0.2px;">
                      © {{ date('Y') }} {{ $appName }} · All rights reserved
                    </td>
                    <td align="right" style="font-size:12px; color:rgba(240,234,220,0.35); font-family:'DM Sans',sans-serif;">
                      This is an automated message
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

          </table>
          <!-- /Outer Card -->

        </td>
      </tr>
    </table>

  </body>
</html>