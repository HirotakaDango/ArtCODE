<?php
// Set the content type to XML
header("Content-Type: application/rss+xml; charset=UTF-8");

// Automatically detect the current server URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$serverUrl = $protocol . "://" . $_SERVER['HTTP_HOST'];

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Fetch the latest 48 images
$stmt = $db->prepare("SELECT * FROM images ORDER BY id DESC LIMIT 48");
$result = $stmt->execute();

// Start the RSS feed
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>
<rss version="2.0">
  <channel>
    <title>ArtCODE RSS Feed</title>
    <link><?php echo $serverUrl; ?></link>
    <description>Latest artwork from ArtCODE</description>
    <language>en-us</language>
    
    <?php while ($image = $result->fetchArray(SQLITE3_ASSOC)): ?>
    <?php
        // Get user details
        $image_email = $image['email'] ?? '';
        $stmt = $db->prepare("SELECT pic, artist FROM users WHERE email = :email");
        $stmt->bindValue(':email', $image_email, SQLITE3_TEXT);
        $userQuery = $stmt->execute();
        $user = $userQuery ? $userQuery->fetchArray(SQLITE3_ASSOC) : null;
        $userArtist = $user['artist'] ?? 'Unknown Artist';
    ?>
    <item>
      <title><![CDATA[<?php echo $image['title']; ?>]]></title>
      <link><?php echo $serverUrl . "/image.php?artworkid=" . $image['id']; ?></link>
      <description><![CDATA[<?php echo $image['imgdesc']; ?>]]></description>
      <author><![CDATA[<?php echo $userArtist; ?>]]></author>
      <pubDate><?php echo date(DATE_RSS, strtotime($image['created_at'] ?? 'now')); ?></pubDate>
      <guid isPermaLink="true"><?php echo $serverUrl . "/image.php?artworkid=" . $image['id']; ?></guid>
      <enclosure url="<?php echo $serverUrl . "/thumbnails/" . $image['filename']; ?>" type="image/jpeg" />
    </item>
    <?php endwhile; ?>
  </channel>
</rss>
