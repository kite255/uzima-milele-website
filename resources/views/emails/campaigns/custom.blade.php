<!DOCTYPE html>
<html lang="sw">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $campaign->subject }}</title>
</head>

<body
    style="
        margin:0;
        padding:0;
        background:#f4f5f7;
        font-family:Arial, Helvetica, sans-serif;
        color:#231F20;
    "
>

    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="background:#f4f5f7;"
    >
        <tr>
            <td
                align="center"
                style="padding:30px 15px;"
            >

                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    style="
                        max-width:650px;
                        background:#ffffff;
                        border-radius:16px;
                        overflow:hidden;
                    "
                >

                    {{-- HEADER --}}
                    <tr>
                        <td
                            style="
                                padding:24px 30px;
                                background:#0E3D4F;
                                text-align:center;
                            "
                        >
                            <img
                                src="{{ asset('logo.png') }}"
                                alt="Uzima Milele"
                                style="
                                    max-height:60px;
                                    max-width:180px;
                                "
                            >
                        </td>
                    </tr>

                    {{-- BODY --}}
                    <tr>
                        <td
                            style="
                                padding:35px 30px;
                                font-size:16px;
                                line-height:1.7;
                            "
                        >

                            @php
                                $recipientName = trim(
                                    (string) ($recipient->name ?? '')
                                );

                                $firstName = filled($recipientName)
                                    ? explode(' ', $recipientName)[0]
                                    : null;
                            @endphp

                            @if ($firstName)
                                <p
                                    style="
                                        margin:0 0 20px;
                                        font-weight:700;
                                    "
                                >
                                    Habari {{ $firstName }},
                                </p>
                            @endif

                            <div>
                                {!! $campaign->content !!}
                            </div>

                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td
                            style="
                                padding:25px 30px;
                                background:#f8fafb;
                                text-align:center;
                                font-size:12px;
                                line-height:1.6;
                                color:#6b7280;
                            "
                        >
                            <p style="margin:0 0 8px;">
                                Uzima Milele
                            </p>

                            <p style="margin:0;">
                                Umeipokea barua pepe hii kwa sababu
                                umejiandikisha kupokea taarifa kutoka
                                Uzima Milele.
                            </p>

                            @if (
                                isset($subscriber) &&
                                filled($subscriber?->unsubscribe_token)
                            )
                                <p style="margin:14px 0 0;">

                                    <a
                                        href="{{ route(
                                            'email-subscribers.unsubscribe',
                                            $subscriber->unsubscribe_token
                                        ) }}"
                                        style="
                                            color:#0083CB;
                                            text-decoration:underline;
                                        "
                                    >
                                        Jiondoe / Unsubscribe
                                    </a>

                                    &nbsp; | &nbsp;

                                    <a
                                        href="{{ route(
                                            'email-subscribers.preferences',
                                            $subscriber->unsubscribe_token
                                        ) }}"
                                        style="
                                            color:#0083CB;
                                            text-decoration:underline;
                                        "
                                    >
                                        Badili mapendeleo / Manage preferences
                                    </a>

                                </p>
                            @endif
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

    {{-- EMAIL OPEN TRACKING PIXEL --}}
    @if (
        isset($recipient) &&
        filled($recipient?->tracking_token)
    )
        <img
            src="{{ route(
                'email-campaigns.open',
                $recipient->tracking_token
            ) }}"
            width="1"
            height="1"
            alt=""
            style="
                display:block;
                width:1px;
                height:1px;
                border:0;
                margin:0;
                padding:0;
            "
        >
    @endif

</body>

</html>