<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="color-scheme" content="light">

    <title>{{ $devotion->title }} | Uzima Milele</title>

    <style>
        .rich-content p {
            margin: 0 0 15px 0;
        }

        .rich-content p:last-child {
            margin-bottom: 0;
        }

        .rich-content ul,
        .rich-content ol {
            margin: 12px 0 16px 22px;
            padding: 0;
        }

        .rich-content li {
            margin-bottom: 7px;
        }

        .rich-content a {
            color: #0083CB;
            text-decoration: underline;
        }

        .rich-content strong {
            font-weight: 700;
            color: #0E3D4F;
        }

        .rich-content em {
            font-style: italic;
        }

        @media only screen and (max-width: 640px) {
            .email-wrapper {
                width: 100% !important;
                max-width: 100% !important;
            }

            .mobile-padding {
                padding-left: 22px !important;
                padding-right: 22px !important;
            }

            .email-title {
                font-size: 27px !important;
                line-height: 1.2 !important;
            }

            .email-body {
                font-size: 16px !important;
                line-height: 1.72 !important;
            }

            .logo-image {
                max-width: 115px !important;
            }

            .footer-logo {
                max-width: 95px !important;
            }

            .cta-button {
                display: block !important;
                width: auto !important;
            }

            .share-button {
                display: block !important;
                margin: 7px 0 !important;
            }
        }
    </style>
</head>

<body
    style="
        margin:0;
        padding:0;
        width:100%;
        background:#f3f5f6;
        font-family:Lato, Arial, Helvetica, sans-serif;
        color:#231F20;
    "
>

@php
    $hasStructuredContent =
        filled($devotion->feature_text) ||
        filled($devotion->lesson) ||
        filled($devotion->scripture_reference) ||
        filled($devotion->scripture_text) ||
        filled($devotion->ellen_white_quote) ||
        filled($devotion->ellen_white_reference);

    $devotionUrl = route(
        'devotions.show',
        $devotion->slug
    );

    $subscriberFirstName = null;
    $subscriberFullName = null;
    $subscriberEmail = null;
    $unsubscribeUrl = null;
    $preferencesUrl = null;

    if (isset($subscriber)) {
        $subscriberFullName = data_get(
            $subscriber,
            'name'
        );

        $subscriberFirstName = data_get(
            $subscriber,
            'first_name'
        );

        if (
            blank($subscriberFirstName) &&
            filled($subscriberFullName)
        ) {
            $subscriberFirstName = trim(
                explode(
                    ' ',
                    trim($subscriberFullName)
                )[0] ?? ''
            );
        }

        $subscriberEmail = data_get(
            $subscriber,
            'email'
        );

        if (
            filled(
                data_get(
                    $subscriber,
                    'unsubscribe_token'
                )
            )
        ) {
            $unsubscribeUrl = route(
                'email-subscribers.unsubscribe',
                $subscriber->unsubscribe_token
            );

            $preferencesUrl = route(
                'email-subscribers.preferences',
                $subscriber->unsubscribe_token
            );
        }
    }

    $greeting = filled($subscriberFirstName)
        ? 'Habari ' . $subscriberFirstName . ','
        : 'Habari,';

    $whatsappShareUrl =
        'https://wa.me/?text=' .
        urlencode(
            $devotion->title .
            ' - Soma tafakari hii kutoka Uzima Milele: ' .
            $devotionUrl
        );

    $facebookShareUrl =
        'https://www.facebook.com/sharer/sharer.php?u=' .
        urlencode($devotionUrl);
@endphp


{{-- PREHEADER --}}
<div
    style="
        display:none;
        max-height:0;
        overflow:hidden;
        opacity:0;
        color:transparent;
        line-height:1px;
    "
>
    {{ $devotion->title }} — Ujumbe wa leo kutoka Uzima Milele.
</div>


<table
    width="100%"
    border="0"
    cellspacing="0"
    cellpadding="0"
    role="presentation"
    style="
        width:100%;
        margin:0;
        padding:0;
        background:#f3f5f6;
    "
>
    <tr>
        <td
            align="center"
            style="
                padding:32px 12px;
            "
        >

            <table
                class="email-wrapper"
                width="620"
                border="0"
                cellspacing="0"
                cellpadding="0"
                role="presentation"
                style="
                    width:100%;
                    max-width:620px;
                    background:#ffffff;
                    border:1px solid #E6E7E9;
                    border-radius:10px;
                    overflow:hidden;
                    box-shadow:0 6px 18px rgba(14,61,79,0.06);
                "
            >

                {{-- CORPORATE HEADER --}}
                <tr>
                    <td
                        class="mobile-padding"
                        style="
                            padding:12px 32px;
                            background:#ffffff;
                        "
                    >

                        <table
                            width="100%"
                            border="0"
                            cellspacing="0"
                            cellpadding="0"
                            role="presentation"
                        >
                            <tr>

                                <td
                                    align="left"
                                    valign="middle"
                                >
                                    <a
                                        href="{{ config('app.url') }}"
                                        style="
                                            display:inline-block;
                                            text-decoration:none;
                                        "
                                    >
                                        <img
                                            class="logo-image"
                                            src="{{ asset('logo.png') }}"
                                            alt="Uzima Milele"
                                            width="115"
                                            style="
                                                display:block;
                                                width:100%;
                                                max-width:115px;
                                                height:auto;
                                                border:0;
                                            "
                                        >
                                    </a>
                                </td>


                                <td
                                    align="right"
                                    valign="middle"
                                >
                                    <table
                                        border="0"
                                        cellspacing="0"
                                        cellpadding="0"
                                        role="presentation"
                                        align="right"
                                    >
                                        <tr>

                                            <td
                                                style="
                                                    width:4px;
                                                    background:#F4B122;
                                                    border-radius:4px 0 0 4px;
                                                    font-size:0;
                                                    line-height:0;
                                                "
                                            >
                                                &nbsp;
                                            </td>

                                            <td
                                                style="
                                                    padding:8px 15px;
                                                    background:#EEF7FB;
                                                    border:1px solid #D7EAF4;
                                                    border-left:0;
                                                    border-radius:0 6px 6px 0;
                                                    color:#0E3D4F;
                                                    font-family:Lato, Arial, Helvetica, sans-serif;
                                                    font-size:10px;
                                                    line-height:1;
                                                    text-transform:uppercase;
                                                    letter-spacing:1.2px;
                                                    font-weight:900;
                                                    white-space:nowrap;
                                                "
                                            >
                                                Tafakari ya Leo
                                            </td>

                                        </tr>
                                    </table>
                                </td>

                            </tr>
                        </table>

                    </td>
                </tr>


                {{-- BRAND DIVIDER --}}
                <tr>
                    <td
                        style="
                            height:4px;
                            background:#0083CB;
                            font-size:0;
                            line-height:0;
                        "
                    >
                        &nbsp;
                    </td>
                </tr>

                <tr>
                    <td
                        style="
                            height:1px;
                            background:#F4B122;
                            font-size:0;
                            line-height:0;
                        "
                    >
                        &nbsp;
                    </td>
                </tr>


                {{-- FEATURED IMAGE --}}
                @if($devotion->image)
                    <tr>
                        <td
                            style="
                                padding:0;
                                background:#ffffff;
                            "
                        >
                            <img
                                src="{{ asset('storage/' . $devotion->image) }}"
                                alt="{{ $devotion->title }}"
                                width="620"
                                style="
                                    display:block;
                                    width:100%;
                                    max-width:620px;
                                    height:auto;
                                    margin:0;
                                    border:0;
                                "
                            >
                        </td>
                    </tr>
                @endif


                {{-- PERSONALIZED INTRODUCTION --}}
                <tr>
                    <td
                        class="mobile-padding"
                        style="
                            padding:28px 40px 20px 40px;
                            background:#ffffff;
                        "
                    >

                        <div
                            style="
                                margin-bottom:16px;
                                color:#0E3D4F;
                                font-family:Lato, Arial, Helvetica, sans-serif;
                                font-size:14px;
                                line-height:1.6;
                                font-weight:600;
                            "
                        >
                            {{ $greeting }}
                        </div>


                        <h1
                            class="email-title"
                            style="
                                margin:0;
                                padding:0;
                                color:#0E3D4F;
                                font-family:Georgia, 'Times New Roman', serif;
                                font-size:30px;
                                line-height:1.2;
                                font-weight:700;
                            "
                        >
                            {{ $devotion->title }}
                        </h1>


                        <div
                            style="
                                margin-top:9px;
                                color:#7a898f;
                                font-family:Lato, Arial, Helvetica, sans-serif;
                                font-size:11px;
                                line-height:1.5;
                            "
                        >
                            {{ optional($devotion->published_at)->format('d M Y') }}
                        </div>

                    </td>
                </tr>


                {{-- SCRIPTURE --}}
                @if(
                    filled($devotion->scripture_reference) ||
                    filled($devotion->scripture_text)
                )
                    <tr>
                        <td
                            class="mobile-padding"
                            style="
                                padding:6px 40px 22px 40px;
                                background:#ffffff;
                            "
                        >
                            <table
                                width="100%"
                                border="0"
                                cellspacing="0"
                                cellpadding="0"
                                role="presentation"
                                style="
                                    width:100%;
                                    background:#f6fafc;
                                    border-left:4px solid #0083CB;
                                    border-radius:5px;
                                "
                            >
                                <tr>
                                    <td
                                        style="
                                            padding:17px 20px;
                                        "
                                    >

                                        @if(filled($devotion->scripture_reference))
                                            <div
                                                style="
                                                    margin-bottom:5px;
                                                    color:#076994;
                                                    font-family:Lato, Arial, Helvetica, sans-serif;
                                                    font-size:11px;
                                                    line-height:1.5;
                                                    font-weight:800;
                                                    text-transform:uppercase;
                                                    letter-spacing:0.6px;
                                                "
                                            >
                                                Neno la Mungu · {{ $devotion->scripture_reference }}
                                            </div>
                                        @endif


                                        @if(filled($devotion->scripture_text))
                                            <div
                                                style="
                                                    color:#0E3D4F;
                                                    font-family:Georgia, 'Times New Roman', serif;
                                                    font-size:15px;
                                                    line-height:1.65;
                                                    font-style:italic;
                                                "
                                            >
                                                “{{ $devotion->scripture_text }}”
                                            </div>
                                        @endif

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif


                {{-- BODY CONTENT --}}
                <tr>
                    <td
                        class="mobile-padding"
                        style="
                            padding:10px 40px 38px 40px;
                            background:#ffffff;
                        "
                    >

                        @if($hasStructuredContent)

                            @if(filled($devotion->feature_text))
                                <div>

                                    <table
                                        width="100%"
                                        border="0"
                                        cellspacing="0"
                                        cellpadding="0"
                                        role="presentation"
                                        style="
                                            margin-bottom:12px;
                                        "
                                    >
                                        <tr>
                                            <td
                                                width="30"
                                                valign="middle"
                                            >
                                                <div
                                                    style="
                                                        width:22px;
                                                        height:2px;
                                                        background:#0083CB;
                                                        font-size:0;
                                                        line-height:0;
                                                    "
                                                >
                                                    &nbsp;
                                                </div>
                                            </td>

                                            <td
                                                valign="middle"
                                                style="
                                                    color:#076994;
                                                    font-family:Lato, Arial, Helvetica, sans-serif;
                                                    font-size:11px;
                                                    line-height:1.5;
                                                    font-weight:800;
                                                    letter-spacing:0.8px;
                                                    text-transform:uppercase;
                                                "
                                            >
                                                Ujumbe wa Leo
                                            </td>
                                        </tr>
                                    </table>


                                    <div
                                        class="email-body rich-content"
                                        style="
                                            color:#37474f;
                                            font-family:Lato, Arial, Helvetica, sans-serif;
                                            font-size:16px;
                                            line-height:1.78;
                                        "
                                    >
                                        {!! $devotion->feature_text !!}
                                    </div>

                                </div>
                            @endif


                            @if(filled($devotion->lesson))
                                <div
                                    style="
                                        margin-top:30px;
                                        padding:21px 22px;
                                        background:#f5f9f4;
                                        border-left:4px solid #54A845;
                                        border-radius:5px;
                                    "
                                >

                                    <div
                                        style="
                                            margin-bottom:9px;
                                            color:#4a8f3e;
                                            font-family:Lato, Arial, Helvetica, sans-serif;
                                            font-size:10px;
                                            line-height:1.5;
                                            font-weight:800;
                                            letter-spacing:0.8px;
                                            text-transform:uppercase;
                                        "
                                    >
                                        Funzo la Leo
                                    </div>


                                    <div
                                        class="email-body rich-content"
                                        style="
                                            color:#37474f;
                                            font-family:Lato, Arial, Helvetica, sans-serif;
                                            font-size:16px;
                                            line-height:1.75;
                                        "
                                    >
                                        {!! $devotion->lesson !!}
                                    </div>

                                </div>
                            @endif


                            @if(
                                filled($devotion->ellen_white_quote) ||
                                filled($devotion->ellen_white_reference)
                            )
                                <div
                                    style="
                                        margin-top:32px;
                                        padding-top:25px;
                                        border-top:1px solid #E6E7E9;
                                    "
                                >

                                    <div
                                        style="
                                            margin-bottom:11px;
                                            color:#076994;
                                            font-family:Lato, Arial, Helvetica, sans-serif;
                                            font-size:10px;
                                            line-height:1.5;
                                            font-weight:800;
                                            letter-spacing:0.8px;
                                            text-transform:uppercase;
                                        "
                                    >
                                        Nukuu ya Ellen G. White
                                    </div>


                                    @if(filled($devotion->ellen_white_quote))
                                        <div
                                            style="
                                                color:#0E3D4F;
                                                font-family:Georgia, 'Times New Roman', serif;
                                                font-size:17px;
                                                line-height:1.75;
                                                font-style:italic;
                                            "
                                        >
                                            “{{ $devotion->ellen_white_quote }}”
                                        </div>
                                    @endif


                                    @if(filled($devotion->ellen_white_reference))
                                        <div
                                            style="
                                                margin-top:11px;
                                                color:#6c7d84;
                                                font-family:Lato, Arial, Helvetica, sans-serif;
                                                font-size:11px;
                                                line-height:1.5;
                                            "
                                        >
                                            {{ $devotion->ellen_white_reference }}
                                        </div>
                                    @endif

                                </div>
                            @endif


                        @elseif(filled($devotion->content))

                            <div
                                class="email-body rich-content"
                                style="
                                    color:#37474f;
                                    font-family:Lato, Arial, Helvetica, sans-serif;
                                    font-size:16px;
                                    line-height:1.78;
                                "
                            >
                                {!! $devotion->content !!}
                            </div>

                        @endif


                        {{-- PRIMARY CTA --}}
                        <table
                            width="100%"
                            border="0"
                            cellspacing="0"
                            cellpadding="0"
                            role="presentation"
                            style="
                                margin-top:38px;
                            "
                        >
                            <tr>
                                <td align="center">

                                    <a
                                        class="cta-button"
                                        href="{{ $devotionUrl }}"
                                        style="
                                            display:inline-block;
                                            min-width:220px;
                                            padding:15px 30px;
                                            background:#0083CB;
                                            color:#ffffff;
                                            text-decoration:none;
                                            border-radius:5px;
                                            font-family:Lato, Arial, Helvetica, sans-serif;
                                            font-size:13px;
                                            line-height:1.2;
                                            font-weight:800;
                                            text-align:center;
                                        "
                                    >
                                        Soma Tafakari Kamili
                                    </a>

                                </td>
                            </tr>
                        </table>


                        {{-- SHARE --}}
                        <div
                            align="center"
                            style="
                                margin-top:24px;
                                padding-top:20px;
                                border-top:1px solid #eef1f2;
                            "
                        >

                            <div
                                style="
                                    margin-bottom:9px;
                                    color:#78888e;
                                    font-family:Lato, Arial, Helvetica, sans-serif;
                                    font-size:10px;
                                    line-height:1.5;
                                    font-weight:700;
                                    text-transform:uppercase;
                                    letter-spacing:0.7px;
                                "
                            >
                                Shiriki Tafakari
                            </div>


                            <table
                                border="0"
                                cellspacing="0"
                                cellpadding="0"
                                role="presentation"
                                align="center"
                            >
                                <tr>

                                    <td style="padding:0 4px;">
                                        <a
                                            class="share-button"
                                            href="{{ $whatsappShareUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            style="
                                                display:inline-block;
                                                padding:8px 14px;
                                                border:1px solid #d9e0e3;
                                                border-radius:5px;
                                                color:#0E3D4F;
                                                background:#ffffff;
                                                font-family:Lato, Arial, Helvetica, sans-serif;
                                                font-size:10px;
                                                line-height:1.2;
                                                font-weight:700;
                                                text-decoration:none;
                                            "
                                        >
                                            WhatsApp
                                        </a>
                                    </td>


                                    <td style="padding:0 4px;">
                                        <a
                                            class="share-button"
                                            href="{{ $facebookShareUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            style="
                                                display:inline-block;
                                                padding:8px 14px;
                                                border:1px solid #d9e0e3;
                                                border-radius:5px;
                                                color:#0E3D4F;
                                                background:#ffffff;
                                                font-family:Lato, Arial, Helvetica, sans-serif;
                                                font-size:10px;
                                                line-height:1.2;
                                                font-weight:700;
                                                text-decoration:none;
                                            "
                                        >
                                            Facebook
                                        </a>
                                    </td>

                                </tr>
                            </table>

                        </div>

                    </td>
                </tr>


                {{-- CORPORATE FOOTER --}}
                <tr>
                    <td
                        class="mobile-padding"
                        align="center"
                        style="
                            padding:22px 40px 24px 40px;
                            background:#f7f9fa;
                            border-top:1px solid #E6E7E9;
                        "
                    >

                        <img
                            class="footer-logo"
                            src="{{ asset('logo.png') }}"
                            alt="Uzima Milele"
                            width="95"
                            style="
                                display:block;
                                width:100%;
                                max-width:95px;
                                height:auto;
                                margin:0 auto;
                                border:0;
                            "
                        >


                        <div
                            style="
                                margin-top:9px;
                                color:#5f7076;
                                font-family:Lato, Arial, Helvetica, sans-serif;
                                font-size:11px;
                                line-height:1.55;
                            "
                        >
                            Huduma ya Mafundisho na Tafakari za Kiroho
                        </div>


                        <table
                            width="46"
                            border="0"
                            cellspacing="0"
                            cellpadding="0"
                            role="presentation"
                            style="
                                width:46px;
                                margin:10px auto 9px auto;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        height:2px;
                                        background:#F4B122;
                                        font-size:0;
                                        line-height:0;
                                    "
                                >
                                    &nbsp;
                                </td>
                            </tr>
                        </table>


                        <div
                            style="
                                max-width:430px;
                                margin:0 auto;
                                color:#7b8a90;
                                font-family:Lato, Arial, Helvetica, sans-serif;
                                font-size:10px;
                                line-height:1.65;
                            "
                        >
                            @if(filled($subscriberFirstName))
                                {{ $subscriberFirstName }}, unapokea ujumbe huu kwa sababu
                                ulijiandikisha kupokea tafakari za Uzima Milele.
                            @else
                                Unapokea ujumbe huu kwa sababu ulijiandikisha
                                kupokea tafakari za Uzima Milele.
                            @endif
                        </div>


                        @if(filled($subscriberEmail))
                            <div
                                style="
                                    margin-top:4px;
                                    color:#9aa6aa;
                                    font-family:Lato, Arial, Helvetica, sans-serif;
                                    font-size:9px;
                                    line-height:1.5;
                                "
                            >
                                {{ $subscriberEmail }}
                            </div>
                        @endif


                        @if(
                            filled($unsubscribeUrl) &&
                            filled($preferencesUrl)
                        )
                            <div
                                style="
                                    margin-top:10px;
                                    font-family:Lato, Arial, Helvetica, sans-serif;
                                    font-size:10px;
                                    line-height:1.6;
                                "
                            >

                               <a
    href="{{ $unsubscribeUrl }}"
    style="
        color:#0083CB;
        text-decoration:underline;
    "
>
    Jiondoe kwenye orodha ya barua pepe / Unsubscribe
</a>

<span
    style="
        padding:0 8px;
        color:#aeb8bc;
    "
>
    |
</span>

<a
    href="{{ $preferencesUrl }}"
    style="
        color:#0083CB;
        text-decoration:underline;
    "
>
    Badili mapendeleo / Manage preferences
</a>

                            </div>
                        @endif

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>