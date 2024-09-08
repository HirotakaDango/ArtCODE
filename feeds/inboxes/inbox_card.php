<div class="container">
  <div class="list-group">
    <?php while ($inbox = $emails->fetchArray(SQLITE3_ASSOC)): ?>
      <?php
        // Query to get the sender and recipient artist names, sender's avatar, and sender's ID
        $query = "
          SELECT
            sender.artist AS sender_artist,
            recipient.artist AS recipient_artist,
            sender.pic AS sender_pic,
            sender.id AS sender_id
          FROM
            users AS sender
          JOIN
            users AS recipient ON recipient.email = :to_email
          WHERE
            sender.email = :email
        ";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':email', $inbox['email'], SQLITE3_TEXT);
        $stmt->bindValue(':to_email', $inbox['to_email'], SQLITE3_TEXT);
        $result = $stmt->execute();
        $artists = $result->fetchArray(SQLITE3_ASSOC);

        // Fetch the sender and recipient artist names, sender's avatar, and sender's ID
        $sender_artist = $artists['sender_artist'];
        $recipient_artist = $artists['recipient_artist'];
        $sender_pic = $artists['sender_pic'] ?: '/icon/profile.svg'; // Default avatar if none provided
        $sender_id = $artists['sender_id'];

        // Format the date and time
        $formatted_date = date("d M Y, H:i", strtotime($inbox['date']));

        // Change sender's name to include "Owner" if sender_id is 1
        if ($sender_id == 1) {
          $sender_artist .= ' (Owner)';
        }
      ?>
      <a href="read.php?id=<?php echo urlencode($inbox['id']); ?>" class="list-group-item list-group-item-action my-1 rounded-4 p-3 border-0 <?php echo $inbox['read'] === 'yes' ? 'bg-body-secondary' : 'bg-body-tertiary'; ?> position-relative">
        <!-- Badge for read/unread status -->
        <span class="position-absolute top-0 end-0 badge rounded-pill bg-<?php echo $inbox['read'] === 'yes' ? 'secondary' : 'primary'; ?>" style="margin: 10px;">
          <?php echo $inbox['read'] === 'yes' ? 'Read' : 'Unread'; ?>
        </span>
        <div class="d-flex align-items-start">
          <!-- Sender Avatar -->
          <img src="/<?php echo $sender_pic; ?>" alt="<?php echo $sender_artist; ?> Avatar" class="rounded-circle me-3" width="60" height="60">
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <h5 class="mb-0 fw-bold"><?php echo $sender_artist; ?></h5>
                <small class="text-muted fw-bold">&lt;<?php echo $inbox['email']; ?>&gt;</small><br>
                <small class="text-muted"><?php echo $formatted_date; ?></small>
              </div>
            </div>
            <h6 class="mb-1"><?php echo $inbox['title']; ?></h6>
            <p class="text-muted mb-1"><?php echo substr($inbox['post'], 0, 150); ?><?php echo strlen($inbox['post']) > 150 ? '...' : ''; ?></p>
            <small class="text-muted">Recipient: <?php echo $recipient_artist; ?> (<?php echo $inbox['to_email']; ?>)</small>
          </div>
        </div>
      </a>
    <?php endwhile; ?>
  </div>
</div>