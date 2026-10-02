<?php
/**
 * Renders events.xml through events.xsl on the server using PHP's XSLTProcessor.
 * This demonstrates the XML -> DTD -> XSLT -> HTML pipeline.
 */
$pageTitle = 'XML Events (XSLT)';
$activeNav = 'events';
require __DIR__ . '/includes/header.php';

$xmlPath = __DIR__ . '/xml/events.xml';
$xslPath = __DIR__ . '/xml/events.xsl';
$dtdPath = __DIR__ . '/xml/events.dtd';

$message = '';
$validationOk = false;

// Validate XML against DTD using libxml
$prevErr = libxml_use_internal_errors(true);
$xml = new DOMDocument();
$xml->validateOnParse = true;
$loaded = @$xml->load($xmlPath, LIBXML_DTDLOAD);
if ($loaded) {
    // Try DTD validation
    $validationOk = @$xml->validate();
}
$errors = [];
foreach (libxml_get_errors() as $e) {
    $errors[] = trim($e->message);
}
libxml_clear_errors();
libxml_use_internal_errors($prevErr);

// Apply XSLT
$rendered = '';
if ($loaded) {
    $xsl = new DOMDocument();
    $xsl->load($xslPath);
    $proc = new XSLTProcessor();
    $proc->importStylesheet($xsl);
    $rendered = $proc->transformToXml($xml);
}
?>
<section class="container section">
  <span class="hero-eyebrow">XML · DTD · XSLT Pipeline</span>
  <h1>Campus Events via XML/XSLT</h1>
  <p class="lead">This page renders <code>events.xml</code> using <code>events.xsl</code>, with the XML validated against <code>events.dtd</code> server-side via PHP's <code>XSLTProcessor</code>.</p>

  <div class="xml-status">
    <div class="xml-status-card">
      <h4>DTD Validation</h4>
      <?php if ($validationOk): ?>
        <span class="badge badge-success">✓ Valid against events.dtd</span>
      <?php else: ?>
        <span class="badge badge-error">✗ Validation issues</span>
        <?php if ($errors): ?><ul class="xml-errors"><?php foreach ($errors as $er) echo '<li>'.e($er).'</li>'; ?></ul><?php endif; ?>
      <?php endif; ?>
    </div>
    <div class="xml-status-card">
      <h4>Files</h4>
      <p>📄 <code>xml/events.xml</code></p>
      <p>📐 <code>xml/events.dtd</code></p>
      <p>🔄 <code>xml/events.xsl</code></p>
    </div>
  </div>

  <h2>Transformed Output</h2>
  <?php if ($rendered): ?>
    <div class="xml-rendered"><?php echo $rendered; ?></div>
  <?php else: ?>
    <p class="empty-state">XSLT transformation could not be performed.</p>
  <?php endif; ?>

  <h2>Raw XML Source</h2>
  <pre class="code-block"><?php echo e(file_get_contents($xmlPath)); ?></pre>

  <h2>DTD Source</h2>
  <pre class="code-block"><?php echo e(file_get_contents($dtdPath)); ?></pre>

  <h2>XSLT Source</h2>
  <pre class="code-block"><?php echo e(file_get_contents($xslPath)); ?></pre>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
