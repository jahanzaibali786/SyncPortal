<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Meeting Invitation</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Arial, sans-serif;
      background-color: #121820;
      color: #eaeaea;
    }

    .container {
      max-width: 600px;
      margin: 50px auto;
      background-color: #1e2630;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }

    .header {
      background-color: #f57c00;
      padding: 25px 20px;
      text-align: center;
      color: #fff;
    }

    .logo {
      max-width: 130px;
      margin-bottom: 10px;
    }

    .header h1 {
      margin: 0;
      font-size: 26px;
      letter-spacing: 0.5px;
    }

    .content {
      padding: 35px 30px;
      text-align: left;
      color: #eaeaea;
      font-size: 15px;
      line-height: 1.7;
    }

    .details {
      background-color: #121820;
      border: 1px solid #2c3440;
      border-radius: 12px;
      padding: 20px 25px;
      margin: 25px 0;
    }

    .details p {
      margin: 8px 0;
      font-size: 15px;
    }

    .details strong {
      color: #f57c00;
    }

    .button {
      display: inline-block;
      background-color: #f57c00;
      color: #fff !important;
      text-decoration: none;
      padding: 12px 32px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 15px;
      letter-spacing: 0.3px;
      box-shadow: 0 4px 10px rgba(245, 124, 0, 0.4);
      transition: all 0.3s ease;
    }

    .button:hover {
      background-color: #ff8c1a;
    }

    .footer {
      background-color: #121820;
      text-align: center;
      padding: 20px 10px;
      font-size: 13px;
      color: #aaa;
      border-top: 1px solid #2c3440;
    }

    a {
      color: #f57c00;
    }

    @media only screen and (max-width: 600px) {
      .content {
        padding: 25px 20px;
      }

      .button {
        width: 100%;
        text-align: center;
      }
    }
  </style>
</head>

<body>

  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center">
        <div class="container">

          <!-- Header -->
          <div class="header" style="background-color:#f57c00; padding:25px 20px; text-align:center; color:#fff;">
            <img src="{{ companyOrGlobalSetting()->logo_url }}" alt=""
              style="max-width:130px; margin-bottom:10px; display:block; margin-left:auto; margin-right:auto;">
              {{-- <img src="https://creativeitpark.org/frontend/images/images/CIP Black.png"
              style="max-width:130px; margin-bottom:10px; display:block; margin-left:auto; margin-right:auto;"> --}}
            <h1 style="margin:0; font-size:26px; letter-spacing:0.5px;">Meeting Invitation</h1>
            <p style="margin-top:8px; font-size:15px; opacity:0.9;">You're invited to join our scheduled meeting</p>
          </div>


          <!-- Body -->
          <div class="content">
            <p>Hi,</p>
            <p>I hope you’re doing well. You’re invited to join our scheduled meeting as per the details below.</p>

            <div class="details">
              <p><strong>📅 Date:</strong> {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('l, M d, Y') }}</p>
              <p><strong>⏰ Time:</strong> {{ \Carbon\Carbon::parse($meeting->meeting_time)->format('h:i A T') }}</p>
              <p><strong>💻 Platform:</strong> Google Meet</p>
              <p><strong>🔗 Meeting Link:</strong> <a href="{{ $meeting->join_url }}"
                  target="_blank" style="color: white;">{{ $meeting->join_url }}</a></p>
              @if(!empty($meeting->agenda))
                <p><strong>📝 Agenda:</strong> {{ $meeting->agenda }}</p>
              @endif
            </div>

            <p>Please confirm your availability or let me know if you’d like to reschedule. Looking forward to
              connecting with you.</p>

            <p style="margin-top:30px;">
              <a href="{{ $meeting->join_url }}" class="button">Join Meeting</a>
            </p>

            <p style="margin-top:35px;">
              Best regards,<br>
              <strong style="color:#f57c00;">Muhammad Arslan Alvi</strong><br>
              Business Development Lead<br>
              Creative IT Park<br>
              <a style="color: white;" href="https://www.creativeitpark.org" target="_blank">www.creativeitpark.org</a>
            </p>
          </div>

          <!-- Footer -->
          <div class="footer">
            <p>© {{ date('Y') }} Creative IT Park. All rights reserved.</p>
          </div>

        </div>
      </td>
    </tr>
  </table>

</body>

</html>