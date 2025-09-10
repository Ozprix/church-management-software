<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="font-family: Arial, sans-serif; background:#f8fafc; padding:24px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
        <tr>
          <td style="background:#0ea5e9; padding:16px 24px; color:#ffffff;">
            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
              <tr>
                <td align="left">
                  <img src="{{ asset(config('app.organization_logo', 'images/logo.png')) }}" alt="{{ config('app.organization_name', config('app.name')) }}" height="32" style="display:block;">
                </td>
                <td align="right" style="font-weight:bold;">{{ config('app.organization_name', config('app.name')) }}</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:24px;">
            {{ $slot }}
          </td>
        </tr>
        <tr>
          <td style="background:#f1f5f9; padding:16px 24px; color:#475569; font-size:12px;">
            {{ config('app.organization_address', '') }} • {{ config('app.organization_email', '') }} • {{ config('app.organization_phone', '') }}
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>


