<?php
/**
 * Test de scope de variables con require_once
 */

echo "=== TEST DE SCOPE ===<br><br>";

// PASO 1: Definir variable en primer bloque PHP
$testVar = "Definido en primer bloque";
echo "1. Variable definida: \$testVar = '$testVar'<br>";
?>

<!-- HTML intermedio -->
<p>HTML entre bloques PHP</p>

<?php
// PASO 2: Definir variable en segundo bloque PHP
$additionalJS = <<<JS
<script>
console.log('JavaScript funcionando');
const URL_BASE = 'http://localhost/test';
</script>
JS;

echo "2. Variable \$additionalJS definida (longitud: " . strlen($additionalJS) . " bytes)<br>";
echo "3. isset(\$additionalJS) = " . (isset($additionalJS) ? 'TRUE' : 'FALSE') . "<br>";
echo "4. isset(\$testVar) = " . (isset($testVar) ? 'TRUE' : 'FALSE') . "<br><br>";

// PASO 3: Incluir un archivo que use la variable
echo "5. Incluyendo footer simulado...<br>";

// Crear footer temporal
$footerContent = <<<'PHP'
<hr>
<footer>
    <p>Este es el footer</p>
    <?php if (isset($additionalJS)): ?>
        <p style="color: green;">✓ $additionalJS está disponible en el footer</p>
        <?= $additionalJS ?>
    <?php else: ?>
        <p style="color: red;">✗ $additionalJS NO está disponible en el footer</p>
    <?php endif; ?>
</footer>
PHP;

file_put_contents(__DIR__ . '/test_footer_temp.php', $footerContent);

require_once __DIR__ . '/test_footer_temp.php';

// Limpiar
unlink(__DIR__ . '/test_footer_temp.php');

echo "<br><br>=== FIN DEL TEST ===";
