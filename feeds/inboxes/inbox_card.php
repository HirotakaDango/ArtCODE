    <div class="container">
      <div class="list-group">
        <?php while ($email = $emails->fetchArray(SQLITE3_ASSOC)): ?>
          <a href="read.php?id=<?php echo urlencode($email['id']); ?>" class="list-group-item text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> list-group-item-action my-1 rounded-4 border-0 <?php echo $email['read'] === 'yes' ? 'link-body-emphasis bg-body-secondary' : 'link-body-emphasis bg-body-tertiary'; ?>">
            <h5 class="fw-medium"><?php echo $email['title']; ?></h5>
            <h6 class="mt-3 mb-4 small text-truncate"><?php echo $email['post']; ?></h6>
            <small><?php echo date("l, d F, Y H:i:s", strtotime($email['date'])); ?></small>
          </a>
        <?php endwhile; ?>
      </div>
    </div>