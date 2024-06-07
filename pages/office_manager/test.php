



<?php if ($approvedReport['step1_approved'] == 1) : ?>
  <?php if (!empty($approvedReport['report_data_step1'])) : ?>
    <p class="card-text text-center">
      <span class="badge bg-label-success">Report Submitted</span>
    </p>
    <!-- Request Date -->
    <p class="card-text">Request Date:
      <?php echo date('d-M-Y', strtotime($approvedReport['created_at'])); ?>
    </p>
    <?php if (!empty($approvedReport['attachment_step1'])) : ?>
      <p class="card-text">Attachment: <a class="text-primary fw-bold" href="<?php echo htmlspecialchars($approvedReport['attachment_step1']); ?>" target="_blank">View Document</a></p>
    <?php endif; ?>

    <?php if ($approvedReport['step2_approved'] == 0) : ?>
      <?php if (empty($approvedReport['attachment_step2'])) : ?>

        <a href="view_report_step1.php?id=<?php echo $approvedReport['id']; ?>" class="btn btn-info w-100 mb-1">Edit Report</a>
        <button type="button" class="btn btn-secondary w-100" data-bs-toggle="modal" data-bs-target="#requeststep2">Request Step 2</button>
      <?php else : ?>
        <button type="button" class="btn btn-secondary w-100" disabled>Request Step 2 (Pending)</button>
      <?php endif; ?>
    <?php else : ?>
      <?php if ($approvedReport['step3_approved'] == 0) : ?>

        <?php if ($approvedReport['attachment_step3'] == null) : ?>
          <a href="create_report_step2.php?id=<?php echo $approvedReport['id']; ?>" class="btn btn-primary w-100">Create Report Step 2</a>
        <?php else : ?>
          <a href="view_report_step2.php?id=<?php echo $approvedReport['id']; ?>" class="btn btn-primary w-100 mb-2">Edit Report</a>
          <button type="button" class="btn btn-secondary w-100" data-bs-toggle="modal" data-bs-target="#requeststep3">Request Step 3</button>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>
  <?php else : ?>
    <p class="card-text text-center">
      <span class="badge bg-label-success">Approved</span>
    </p>
    <!-- Request Date -->
    <p class="card-text">Request Date:
      <?php echo date('d-M-Y', strtotime($approvedReport['created_at'])); ?>
    </p>
    <?php if (!empty($approvedReport['attachment_step1'])) : ?>
      <p class="card-text">Attachment: <a class="text-primary fw-bold" href="<?php echo htmlspecialchars($approvedReport['attachment_step1']); ?>" target="_blank">View Document</a></p>
    <?php endif; ?>
    <a href="create_report_step1.php?id=<?php echo $approvedReport['id']; ?>" class="btn btn-primary w-100">Create Report Step 1</a>
  <?php endif; ?>
<?php else : ?>
  <p class="card-text text-center">
    <span class="badge bg-label-warning">Pending</span>
  </p>
  <!-- Request Date -->
  <p class="card-text">Request Date:
    <?php echo date('d-M-Y', strtotime($approvedReport['created_at'])); ?>
  </p>
  <?php if (!empty($approvedReport['attachment_step1'])) : ?>
    <p class="card-text">Attachment: <a class="text-primary fw-bold" href="<?php echo htmlspecialchars($approvedReport['attachment_step1']); ?>" target="_blank">View Document</a></p>
  <?php endif; ?>
  <button type="button" class="btn btn-secondary w-100" disabled>Create Report Step 1</button>
<?php endif; ?>
