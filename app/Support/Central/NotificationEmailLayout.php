<?php

declare(strict_types=1);

namespace App\Support\Central;

/**
 * Branded HTML email shell for central notification templates.
 */
class NotificationEmailLayout
{
    /**
     * Wrap notification body content in the platform email layout.
     */
    public static function wrap(
        string $previewText,
        string $greeting,
        string $bodyContent,
        ?string $signOffName = null,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
    ): string {
        $appName = e((string) config('app.name'));
        $logoUrl = e((string) config('notifications.email.logo_url', ''));
        $brandUrl = e((string) config('notifications.email.brand_url', config('app.url')));
        $headerBg = e((string) config('notifications.email.header_bg', '#1e2b2e'));
        $accentColor = e((string) config('notifications.email.accent_color', '#73bc1c'));
        $linkColor = e((string) config('notifications.email.link_color', '#ff641a'));

        $logoBlock = $logoUrl !== ''
            ? <<<HTML
                            <img
                              alt="{$appName}"
                              height="50"
                              src="{$logoUrl}"
                              style="display:block;outline:none;border:none;text-decoration:none;margin:0 auto;"
                            />
            HTML
            : <<<HTML
                            <p style="font-size:20px;line-height:24px;font-weight:800;color:#ffffff;margin:0;">
                              {$appName}
                            </p>
            HTML;

        $actionBlock = '';

        if ($actionUrl !== null && $actionUrl !== '' && $actionLabel !== null && $actionLabel !== '') {
            $actionBlock = <<<HTML
                            <p style="font-size:15px;line-height:24px;color:#525f7f;margin:24px 0 16px;text-align:left;">
                              <a
                                href="{$actionUrl}"
                                style="display:inline-block;background-color:{$linkColor};color:#ffffff;text-decoration:none;font-weight:700;padding:12px 24px;border-radius:6px;"
                                target="_blank"
                              >{$actionLabel}</a>
                            </p>
            HTML;
        }

        $signOff = $signOffName ?? '{{app_name}}';

        return <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" lang="en">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
    <meta name="x-apple-disable-message-reformatting" />
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@1&amp;display=swap" rel="stylesheet" />
  </head>
  <body style="background-color:#f6f9fc;margin:0;padding:0;">
    <div style="display:none;overflow:hidden;line-height:1px;opacity:0;max-height:0;max-width:0;">
      {$previewText}
    </div>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" role="presentation" align="center">
      <tbody>
        <tr>
          <td style="background-color:#f6f9fc;font-family:'Satoshi',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Ubuntu,sans-serif;">
            <table align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="max-width:37.5em;background-color:#ffffff;margin:0 auto;padding:0;">
              <tbody>
                <tr style="width:100%">
                  <td>
                    <table align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="padding:10px 48px;background-color:{$headerBg};text-align:center;">
                      <tbody>
                        <tr>
                          <td>
                            {$logoBlock}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <table align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="padding:40px 48px;border-bottom:5px solid {$accentColor};">
                      <tbody>
                        <tr>
                          <td>
                            <p style="font-size:16px;line-height:24px;font-weight:800;color:#070d09;margin-bottom:8px;margin-top:16px;">
                              {$greeting}
                            </p>
                            {$bodyContent}
                            {$actionBlock}
                            <p style="font-size:15px;line-height:24px;color:#525f7f;margin-bottom:16px;text-align:left;margin-top:16px;">
                              Best regards,<br />{$signOff}.
                            </p>
                            <p style="font-size:13px;line-height:20px;color:#8898aa;margin-top:24px;text-align:left;">
                              <a href="{$brandUrl}" style="color:{$linkColor};text-decoration:none;" target="_blank">{$brandUrl}</a>
                            </p>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
  </body>
</html>
HTML;
    }

    /**
     * Standard paragraph block for template body content.
     */
    public static function paragraph(string $html): string
    {
        return <<<HTML
                            <p style="font-size:15px;line-height:24px;color:#525f7f;margin-bottom:16px;text-align:left;margin-top:16px;">
                              {$html}
                            </p>
        HTML;
    }
}
