<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>New Contact Form Submission</title>
</head>

@php
    $logoUrl = url(\Illuminate\Support\Facades\Vite::asset('resources/js/assets/logo.webp'));
@endphp

<body style="margin:0; padding:0; background:#111; font-family:Arial, Helvetica, sans-serif; color:#eee;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#111; padding:30px 0;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#1a1a1a; border:1px solid #333; border-radius:8px; overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="background:#000; padding:30px;">

                            <img src="{{ $logoUrl }}" alt="Pure Power Logo" style="max-width:220px;">

                        </td>
                    </tr>


                    <!-- Title -->
                    <tr>
                        <td style="padding:30px 40px 10px;">

                            <h2 style="
                            margin:0;
                            color:#f5c542;
                            font-size:26px;
                            font-weight:bold;
                            letter-spacing:1px;
                        ">
                                New Contact Form Submission
                            </h2>

                        </td>
                    </tr>


                    <!-- Details -->
                    <tr>
                        <td style="padding:20px 40px;">

                            <table width="100%" cellpadding="0" cellspacing="0">

                                <tr>
                                    <td style="padding:8px 0; color:#aaa;">
                                        Name
                                    </td>
                                    <td style="padding:8px 0; color:#fff;">
                                        {{ $data['name'] }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:8px 0; color:#aaa;">
                                        Email
                                    </td>
                                    <td style="padding:8px 0; color:#fff;">
                                        {{ $data['email'] }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:8px 0; color:#aaa;">
                                        Phone
                                    </td>
                                    <td style="padding:8px 0; color:#fff;">
                                        {{ $data['phone'] ?: 'Not supplied' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:8px 0; color:#aaa;">
                                        Reason
                                    </td>
                                    <td style="padding:8px 0; color:#fff;">
                                        {{ $data['reason'] }}
                                    </td>
                                </tr>

                            </table>

                        </td>
                    </tr>


                    <!-- Enquiry -->
                    <tr>
                        <td style="
                        padding:20px 40px 35px;
                    ">

                            <h3 style="
                            color:#f5c542;
                            font-size:18px;
                            margin-bottom:10px;
                        ">
                                Message
                            </h3>

                            <div style="
                            background:#000;
                            border-left:4px solid #f5c542;
                            padding:20px;
                            color:#ddd;
                            line-height:1.6;
                            border-radius:4px;
                        ">
                                {!! nl2br(e($data['enquiry'])) !!}
                            </div>

                        </td>
                    </tr>


                    <!-- Footer -->
                    <tr>
                        <td align="center" style="
                            background:#000;
                            padding:20px;
                            color:#777;
                            font-size:12px;
                        ">

                            This message was submitted through the Pure Power website.

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>