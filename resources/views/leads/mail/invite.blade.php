<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Meeting Invitation</title>
</head>
<body style="margin:0; padding:0; font-family:'Segoe UI', Arial, sans-serif; color:#333;">

  <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; margin:50px auto; border-radius:20px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.25);">
    <tr>
      <!-- Header Section -->
      <td style="background:linear-gradient(90deg, #7B79B8 -0.06%, #246DB5 33.96%, #56A3D9 72.98%, #68C18C 100%); padding:40px 20px; text-align:center; color:#fff;">
        <h1 style="margin:0; font-size:28px; letter-spacing:1px;">Meeting Invitation</h1>
        <p style="margin:10px 0 0; font-size:16px; opacity:0.9;">You're invited to a scheduled meeting</p>
      </td>
    </tr>

    <!-- Main Content -->
    <tr>
      <td style="background:rgba(255,255,255,0.9); backdrop-filter:blur(10px); padding:40px 30px; text-align:center;">
        
        <p style="font-size:16px; color:#444; margin:0 0 25px;">
          Please find the meeting details below:
        </p>

        <!-- Meeting Details Card -->
        <div style="background:linear-gradient(145deg, #f7faff, #eef3fa); border:1px solid #dde7f2; border-radius:12px; padding:20px 25px; display:inline-block; text-align:left; box-shadow:inset 0 0 10px rgba(0,0,0,0.03); margin-bottom:30px;">
          <p style="margin:0 0 10px 0; font-size:16px; line-height:1.6;">
            <strong style="color:#246DB5;">📅 Date:</strong><br>
            {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('M d, Y') }}
          </p>
          <p style="margin:0; font-size:16px; line-height:1.6;">
            <strong style="color:#246DB5;">⏰ Time:</strong><br>
            {{ \Carbon\Carbon::parse($meeting->meeting_time)->format('h:i A') }}
          </p>
        </div>

        <!-- Join Button -->
        <p style="margin:0;">
          <a href="{{ $meeting->join_url }}" 
             style="background: linear-gradient(90deg, #7B79B8 -0.06%, #246DB5 33.96%, #56A3D9 72.98%, #68C18C 100%) !important;
                    color:#fff; padding:14px 36px; text-decoration:none; border-radius:30px;
                    font-weight:600; font-size:16px; letter-spacing:0.5px; display:inline-block;
                    box-shadow:0 4px 10px rgba(36,109,181,0.3); transition:all 0.3s ease;">
            Join Meeting
          </a>
        </p>

      </td>
    </tr>

    <!-- Footer -->
    <tr>
      <td style="background:#f8faff; text-align:center; padding:20px 10px; font-size:14px; color:#666;">
        <p style="margin:0;">Thanks,<br><strong style="color:#246DB5;">CreativeitPark</strong></p>
      </td>
    </tr>
  </table>

</body>
</html>
