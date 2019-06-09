<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
<?php foreach ($listItems as $index => $listItem): ?>
  {
    "@type": "ListItem",
    "position": <?= ($index + 1) ?>,
    "name": "<?= $sanitizer->entities1($listItem->name) ?>",
    "item": "<?= $listItem->item ?>"
  }<?php if ($index !== ($listItems->count() - 1)) { echo ",\n"; } ?>
<?php endforeach; ?>

  ]
}
</script>
