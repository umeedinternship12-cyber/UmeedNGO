// ─────────────────────────────────────────────────────────────────────────────
// Umeed — Google Apps Script  (deploy as a Web App)
//
// Steps:
//  1. Open your Google Sheet → Extensions → Apps Script
//  2. Paste this entire file into Code.gs (replace any existing code)
//  3. Click Deploy → New deployment → Web app
//       Execute as: Me
//       Who has access: Anyone
//  4. Copy the Web App URL and paste it into api/sheets.php
// ─────────────────────────────────────────────────────────────────────────────

function doPost(e) {
  try {
    var data = JSON.parse(e.postData.contents);
    var type = String(data.type || '');
    var ss   = SpreadsheetApp.getActiveSpreadsheet();
    var now  = Utilities.formatDate(new Date(), 'Asia/Kolkata', 'yyyy-MM-dd HH:mm:ss');

    if (type === 'volunteer') {
      appendRow(ss, 'Volunteers',
        ['Name', 'Email', 'Phone', 'City', 'Availability', 'Skills', 'Message', 'Submitted At'],
        [data.name, data.email, data.phone, data.city, data.availability, data.skills, data.message, now]
      );
    } else if (type === 'internship') {
      appendRow(ss, 'Internships',
        ['Name', 'Email', 'Phone', 'College / University', 'Submitted At'],
        [data.name, data.email, data.phone, data.college, now]
      );
    } else if (type === 'donation') {
      appendRow(ss, 'Donations',
        ['Name', 'Email', 'Phone', 'PAN', 'Amount (Rs)', 'Donation Type', 'Anonymous', 'Message', 'Submitted At'],
        [data.name, data.email, data.phone, data.pan, data.amount,
         data.donation_type, data.anonymous ? 'Yes' : 'No', data.message, now]
      );
    } else {
      return jsonResponse({ok: false, error: 'Unknown type'});
    }

    return jsonResponse({ok: true});
  } catch (err) {
    return jsonResponse({ok: false, error: err.message});
  }
}

function appendRow(ss, sheetName, headers, values) {
  var sheet = ss.getSheetByName(sheetName);
  if (!sheet) {
    sheet = ss.insertSheet(sheetName);
    sheet.appendRow(headers);
    sheet.getRange(1, 1, 1, headers.length)
         .setFontWeight('bold')
         .setBackground('#f3f3f3');
    sheet.setFrozenRows(1);
  }
  sheet.appendRow(values);
}

function jsonResponse(data) {
  return ContentService
    .createTextOutput(JSON.stringify(data))
    .setMimeType(ContentService.MimeType.JSON);
}
