<?xml version="1.0" encoding="UTF-8"?>
<!--
    XSLT stylesheet: when students.xml is opened directly in a browser,
    this transforms the raw XML into a formatted HTML table.
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <xsl:template match="/university">
    <html>
      <head>
        <title>Student Records (XML)</title>
        <style>
          body  { font-family: Segoe UI, Arial, sans-serif; background:#f4f6fb; margin:0; padding:32px; color:#1f2937; }
          h1    { margin:0 0 4px; color:#1e3a8a; }
          p     { margin:0 0 20px; color:#6b7280; }
          table { border-collapse:collapse; width:100%; background:#fff; box-shadow:0 2px 10px rgba(0,0,0,.08); border-radius:8px; overflow:hidden; }
          th    { background:#1e3a8a; color:#fff; text-align:left; padding:12px; font-size:14px; }
          td    { padding:11px 12px; border-bottom:1px solid #e5e7eb; font-size:14px; }
          tr:nth-child(even) td { background:#f9fafb; }
          .tag  { background:#e0e7ff; color:#3730a3; padding:2px 8px; border-radius:10px; font-size:12px; font-weight:600; }
          a     { color:#1e3a8a; }
        </style>
      </head>
      <body>
        <h1><xsl:value-of select="@name"/></h1>
        <p>
          Student records stored in XML &#8212;
          <xsl:value-of select="students/@count"/> record(s), generated on
          <xsl:value-of select="@generated"/> &#183;
          <a href="../xml_view.php">Back to application</a>
        </p>
        <table>
          <tr>
            <th>#</th><th>Register No.</th><th>Name</th><th>Course</th>
            <th>Semester</th><th>Email ID</th><th>Contact No.</th>
          </tr>
          <xsl:for-each select="students/student">
            <xsl:sort select="regNo"/>
            <tr>
              <td><xsl:value-of select="position()"/></td>
              <td><strong><xsl:value-of select="regNo"/></strong></td>
              <td><xsl:value-of select="name"/></td>
              <td><span class="tag"><xsl:value-of select="course"/></span></td>
              <td><xsl:value-of select="semester"/></td>
              <td><xsl:value-of select="email"/></td>
              <td><xsl:value-of select="contactNumber"/></td>
            </tr>
          </xsl:for-each>
        </table>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
