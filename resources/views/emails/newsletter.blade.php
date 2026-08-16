<div style="background:#f1f5f9;padding:32px 16px;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background:#0a192f;padding:18px 24px;">
                            <span style="color:#f8fafc;font-size:18px;font-weight:bold;">GitPR</span>
                            <span style="color:#22d3ee;font-size:12px;">[ CLI ]</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <div style="font-size:14px;color:#334155;line-height:1.6;">
                                {!! $htmlBody !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;">
                            <a href="{{ $unsubscribeUrl }}" style="color:#1a80d4;">{{ $strings['mail_newsletter_unsubscribe'] }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
