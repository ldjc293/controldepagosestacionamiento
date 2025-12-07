<?php
/**
 * Comprehensive Test Script for Control Assignment Functionality
 *
 * Tests both new user registrations and existing user control management
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to prevent header issues
ob_start();

// Include required files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';
require_once __DIR__ . '/app/models/Control.php';
require_once __DIR__ . '/app/models/Apartamento.php';
require_once __DIR__ . '/app/helpers/ValidationHelper.php';

// HTML Header
echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test Control Assignment Functionality</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css' rel='stylesheet'>";
echo "<style>
    .test-section { margin-bottom: 2rem; }
    .test-result { margin-top: 1rem; }
    .success { color: #198754; }
    .error { color: #dc3545; }
    .warning { color: #ffc107; }
    .info { color: #0dcaf0; }
</style>";
echo "</head><body>";
echo "<div class='container-fluid mt-4'>";

echo "<h1>🅿️ Test Control Assignment Functionality</h1>";
echo "<p class='lead'>Comprehensive testing of control assignment for new and existing users</p>";

// Test data
$testUserEmail = 'test_control_user_' . time() . '@example.com';
$testUserName = 'Test Control User';
$testPassword = 'Test123!';

// Track test results
$testResults = [];
$totalTests = 0;
$passedTests = 0;

function logTest($testName, $result, $message = '', $details = '') {
    global $testResults, $totalTests, $passedTests;
    $totalTests++;

    if ($result) {
        $passedTests++;
        $status = 'success';
        $icon = 'check-circle';
    } else {
        $status = 'error';
        $icon = 'x-circle';
    }

    $testResults[] = [
        'name' => $testName,
        'result' => $result,
        'message' => $message,
        'details' => $details,
        'status' => $status,
        'icon' => $icon
    ];
}

function displayTestResults() {
    global $testResults, $totalTests, $passedTests;

    echo "<div class='card mt-4'>";
    echo "<div class='card-header d-flex justify-content-between align-items-center'>";
    echo "<h5 class='mb-0'><i class='bi bi-clipboard-check'></i> Test Results Summary</h5>";
    echo "<span class='badge bg-" . ($passedTests == $totalTests ? 'success' : 'warning') . "'>{$passedTests}/{$totalTests} Passed</span>";
    echo "</div>";
    echo "<div class='card-body'>";

    foreach ($testResults as $test) {
        echo "<div class='alert alert-{$test['status']} d-flex align-items-start'>";
        echo "<i class='bi bi-{$test['icon']} me-2 mt-1'></i>";
        echo "<div>";
        echo "<strong>{$test['name']}</strong>";
        if ($test['message']) {
            echo "<br><small>{$test['message']}</small>";
        }
        if ($test['details']) {
            echo "<br><code class='small'>{$test['details']}</code>";
        }
        echo "</div>";
        echo "</div>";
    }

    echo "</div></div>";
}

// ==================== SETUP PHASE ====================

echo "<div class='test-section'>";
echo "<h2>🔧 Setup Phase</h2>";

// Test 1: Ensure controls table has data
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 1: Verify Controls Table</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    $controlCount = Database::fetchOne("SELECT COUNT(*) as count FROM controles_estacionamiento")['count'];
    if ($controlCount >= 500) {
        logTest("Controls Table Check", true, "Found {$controlCount} controls in database");
        echo "<div class='alert alert-success'>✅ Found {$controlCount} controls in database</div>";
    } else {
        logTest("Controls Table Check", false, "Expected 500 controls, found {$controlCount}");
        echo "<div class='alert alert-warning'>⚠️ Only {$controlCount} controls found. Creating missing controls...</div>";

        // Create controls if missing
        $created = Control::crearControlesIniciales();
        echo "<div class='alert alert-info'>Created {$created} controls</div>";
    }
} catch (Exception $e) {
    logTest("Controls Table Check", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error checking controls: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

// Test 2: Create test user
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 2: Create Test User</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    // Check if test user already exists
    $existingUser = Usuario::findByEmail($testUserEmail);
    if ($existingUser) {
        logTest("Create Test User", false, "Test user already exists");
        echo "<div class='alert alert-warning'>⚠️ Test user already exists, skipping creation</div>";
        $testUser = $existingUser;
    } else {
        // Create new user
        $userId = Usuario::create([
            'nombre_completo' => $testUserName,
            'email' => $testUserEmail,
            'password' => $testPassword,
            'rol' => 'cliente',
            'activo' => 1
        ]);

        if ($userId) {
            $testUser = Usuario::findById($userId);
            logTest("Create Test User", true, "Created test user: {$testUser->email}");
            echo "<div class='alert alert-success'>✅ Created test user: {$testUser->email}</div>";
        } else {
            logTest("Create Test User", false, "Failed to create test user");
            echo "<div class='alert alert-danger'>❌ Failed to create test user</div>";
            exit;
        }
    }
} catch (Exception $e) {
    logTest("Create Test User", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error creating user: " . $e->getMessage() . "</div>";
    exit;
}

echo "</div></div>";

// Test 3: Create/assign test apartment
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 3: Assign Test Apartment</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    // Check if user already has apartment
    $existingApartment = Database::fetchOne(
        "SELECT au.*, a.bloque, a.numero_apartamento FROM apartamento_usuario au
         JOIN apartamentos a ON a.id = au.apartamento_id
         WHERE au.usuario_id = ? AND au.activo = 1",
        [$testUser->id]
    );

    if ($existingApartment) {
        logTest("Assign Test Apartment", true, "User already has apartment: {$existingApartment['bloque']}-{$existingApartment['numero_apartamento']}");
        echo "<div class='alert alert-success'>✅ User already has apartment: {$existingApartment['bloque']}-{$existingApartment['numero_apartamento']}</div>";
        $apartamentoUsuarioId = $existingApartment['id'];
    } else {
        // Find available apartment
        $availableApartment = Database::fetchOne(
            "SELECT a.* FROM apartamentos a
             LEFT JOIN apartamento_usuario au ON au.apartamento_id = a.id AND au.activo = 1
             WHERE au.id IS NULL AND a.activo = 1
             LIMIT 1"
        );

        if (!$availableApartment) {
            logTest("Assign Test Apartment", false, "No available apartments found");
            echo "<div class='alert alert-danger'>❌ No available apartments found</div>";
            exit;
        }

        // Assign apartment to user
        $apartamentoUsuarioId = Database::insert(
            "INSERT INTO apartamento_usuario (usuario_id, apartamento_id, cantidad_controles, activo, fecha_asignacion)
             VALUES (?, ?, 2, 1, NOW())",
            [$testUser->id, $availableApartment['id']]
        );

        if ($apartamentoUsuarioId) {
            logTest("Assign Test Apartment", true, "Assigned apartment: {$availableApartment['bloque']}-{$availableApartment['numero_apartamento']}");
            echo "<div class='alert alert-success'>✅ Assigned apartment: {$availableApartment['bloque']}-{$availableApartment['numero_apartamento']}</div>";
        } else {
            logTest("Assign Test Apartment", false, "Failed to assign apartment");
            echo "<div class='alert alert-danger'>❌ Failed to assign apartment</div>";
            exit;
        }
    }
} catch (Exception $e) {
    logTest("Assign Test Apartment", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error assigning apartment: " . $e->getMessage() . "</div>";
    exit;
}

echo "</div></div>";

echo "</div>";

// ==================== NEW USER REGISTRATION TESTS ====================

echo "<div class='test-section'>";
echo "<h2>🆕 New User Registration Tests</h2>";

// Test 4: Assign initial controls to new user
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 4: Assign Initial Controls to New User</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    // Get available controls
    $availableControls = Control::getVacios();
    if (count($availableControls) < 2) {
        logTest("Assign Initial Controls", false, "Not enough available controls (need 2, have " . count($availableControls) . ")");
        echo "<div class='alert alert-danger'>❌ Not enough available controls</div>";
    } else {
        // Assign 2 controls
        $assigned = 0;
        $assignedControls = [];

        foreach ($availableControls as $controlData) {
            if ($assigned >= 2) break;

            $control = new Control();
            $control->id = $controlData['id'];
            $control->numero_control_completo = $controlData['numero_control_completo'];

            if ($control->asignar($apartamentoUsuarioId, 1)) { // operador_id = 1 (admin)
                $assigned++;
                $assignedControls[] = $control->numero_control_completo;
            }
        }

        if ($assigned == 2) {
            logTest("Assign Initial Controls", true, "Assigned 2 controls: " . implode(', ', $assignedControls));
            echo "<div class='alert alert-success'>✅ Assigned 2 controls: " . implode(', ', $assignedControls) . "</div>";
        } else {
            logTest("Assign Initial Controls", false, "Only assigned {$assigned} controls");
            echo "<div class='alert alert-warning'>⚠️ Only assigned {$assigned} controls</div>";
        }
    }
} catch (Exception $e) {
    logTest("Assign Initial Controls", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error assigning controls: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

// Test 5: Verify control assignment in database
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 5: Verify Control Assignment in Database</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    $userControls = Control::getByApartamentoUsuario($apartamentoUsuarioId);
    $controlCount = count($userControls);

    if ($controlCount >= 2) {
        logTest("Verify Control Assignment", true, "User has {$controlCount} controls assigned");
        echo "<div class='alert alert-success'>✅ User has {$controlCount} controls assigned</div>";

        echo "<div class='table-responsive mt-3'>";
        echo "<table class='table table-sm'>";
        echo "<thead><tr><th>Control</th><th>Estado</th><th>Fecha Asignación</th></tr></thead>";
        echo "<tbody>";
        foreach ($userControls as $control) {
            echo "<tr>";
            echo "<td>{$control->numero_control_completo}</td>";
            echo "<td><span class='badge bg-success'>{$control->estado}</span></td>";
            echo "<td>{$control->fecha_asignacion}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "</div>";
    } else {
        logTest("Verify Control Assignment", false, "Expected at least 2 controls, found {$controlCount}");
        echo "<div class='alert alert-danger'>❌ Expected at least 2 controls, found {$controlCount}</div>";
    }
} catch (Exception $e) {
    logTest("Verify Control Assignment", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error verifying controls: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

echo "</div>";

// ==================== EXISTING USER CONTROL MANAGEMENT TESTS ====================

echo "<div class='test-section'>";
echo "<h2>👤 Existing User Control Management Tests</h2>";

// Test 6: Add additional control to existing user
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 6: Add Additional Control to Existing User</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    $initialCount = count(Control::getByApartamentoUsuario($apartamentoUsuarioId));

    // Find another available control
    $availableControl = Control::getVacios(1); // Prefer receptor A
    if (empty($availableControl)) {
        $availableControl = Control::getVacios(); // Any receptor
    }

    if (!empty($availableControl)) {
        $control = new Control();
        $control->id = $availableControl[0]['id'];
        $control->numero_control_completo = $availableControl[0]['numero_control_completo'];

        if ($control->asignar($apartamentoUsuarioId, 1)) {
            $newCount = count(Control::getByApartamentoUsuario($apartamentoUsuarioId));
            logTest("Add Additional Control", true, "Added control {$control->numero_control_completo}. Count: {$initialCount} → {$newCount}");
            echo "<div class='alert alert-success'>✅ Added control {$control->numero_control_completo}. Count: {$initialCount} → {$newCount}</div>";
        } else {
            logTest("Add Additional Control", false, "Failed to assign additional control");
            echo "<div class='alert alert-danger'>❌ Failed to assign additional control</div>";
        }
    } else {
        logTest("Add Additional Control", false, "No available controls found");
        echo "<div class='alert alert-warning'>⚠️ No available controls found</div>";
    }
} catch (Exception $e) {
    logTest("Add Additional Control", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error adding control: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

// Test 7: Remove control from existing user
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 7: Remove Control from Existing User</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    $userControls = Control::getByApartamentoUsuario($apartamentoUsuarioId);
    if (count($userControls) > 0) {
        $controlToRemove = $userControls[0];
        $initialCount = count($userControls);

        if ($controlToRemove->desasignar('Test removal', 1)) {
            $newCount = count(Control::getByApartamentoUsuario($apartamentoUsuarioId));
            logTest("Remove Control", true, "Removed control {$controlToRemove->numero_control_completo}. Count: {$initialCount} → {$newCount}");
            echo "<div class='alert alert-success'>✅ Removed control {$controlToRemove->numero_control_completo}. Count: {$initialCount} → {$newCount}</div>";
        } else {
            logTest("Remove Control", false, "Failed to remove control");
            echo "<div class='alert alert-danger'>❌ Failed to remove control</div>";
        }
    } else {
        logTest("Remove Control", false, "No controls to remove");
        echo "<div class='alert alert-warning'>⚠️ No controls to remove</div>";
    }
} catch (Exception $e) {
    logTest("Remove Control", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error removing control: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

// Test 8: Change control state
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 8: Change Control State</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    $userControls = Control::getByApartamentoUsuario($apartamentoUsuarioId);
    if (count($userControls) > 0) {
        $control = $userControls[0];

        if ($control->cambiarEstado('bloqueado', 'Test blocking', 1)) {
            logTest("Change Control State", true, "Changed control {$control->numero_control_completo} to 'bloqueado'");
            echo "<div class='alert alert-success'>✅ Changed control {$control->numero_control_completo} to 'bloqueado'</div>";

            // Test unblocking
            if ($control->desbloquear(1)) {
                logTest("Unblock Control", true, "Successfully unblocked control {$control->numero_control_completo}");
                echo "<div class='alert alert-success'>✅ Successfully unblocked control {$control->numero_control_completo}</div>";
            } else {
                logTest("Unblock Control", false, "Failed to unblock control");
                echo "<div class='alert alert-danger'>❌ Failed to unblock control</div>";
            }
        } else {
            logTest("Change Control State", false, "Failed to change control state");
            echo "<div class='alert alert-danger'>❌ Failed to change control state</div>";
        }
    } else {
        logTest("Change Control State", false, "No controls available for state change");
        echo "<div class='alert alert-warning'>⚠️ No controls available for state change</div>";
    }
} catch (Exception $e) {
    logTest("Change Control State", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error changing state: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

echo "</div>";

// ==================== EDGE CASES AND ERROR HANDLING ====================

echo "<div class='test-section'>";
echo "<h2>⚠️ Edge Cases and Error Handling</h2>";

// Test 9: Try to assign already assigned control
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 9: Attempt to Assign Already Assigned Control</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    $userControls = Control::getByApartamentoUsuario($apartamentoUsuarioId);
    if (count($userControls) > 0) {
        $control = $userControls[0];

        // Try to assign the same control again
        $result = $control->asignar($apartamentoUsuarioId, 1);

        if (!$result) {
            logTest("Assign Already Assigned Control", true, "Correctly prevented assignment of already assigned control {$control->numero_control_completo}");
            echo "<div class='alert alert-success'>✅ Correctly prevented assignment of already assigned control {$control->numero_control_completo}</div>";
        } else {
            logTest("Assign Already Assigned Control", false, "Incorrectly allowed assignment of already assigned control");
            echo "<div class='alert alert-danger'>❌ Incorrectly allowed assignment of already assigned control</div>";
        }
    } else {
        logTest("Assign Already Assigned Control", false, "No controls available for test");
        echo "<div class='alert alert-warning'>⚠️ No controls available for test</div>";
    }
} catch (Exception $e) {
    logTest("Assign Already Assigned Control", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error in test: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

// Test 10: Try to remove control from different user (operators can remove any control)
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 10: Operator Can Remove Control from Any User</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    // Find a control assigned to a different user
    $otherUserControl = Database::fetchOne(
        "SELECT c.* FROM controles_estacionamiento c
         WHERE c.apartamento_usuario_id != ? AND c.apartamento_usuario_id IS NOT NULL
         LIMIT 1",
        [$apartamentoUsuarioId]
    );

    if ($otherUserControl) {
        $control = new Control();
        $control->id = $otherUserControl['id'];
        $control->numero_control_completo = $otherUserControl['numero_control_completo'];

        // Try to remove it (operators should be able to remove controls from any user)
        $result = $control->desasignar('Operator removal test', 1);

        if ($result) {
            logTest("Operator Remove Control from Any User", true, "Operator successfully removed control {$control->numero_control_completo} from different user");
            echo "<div class='alert alert-success'>✅ Operator successfully removed control {$control->numero_control_completo} from different user</div>";

            // Re-assign it back for cleanup
            $control->asignar($otherUserControl['apartamento_usuario_id'], 1);
        } else {
            logTest("Operator Remove Control from Any User", false, "Failed to remove control from different user");
            echo "<div class='alert alert-danger'>❌ Failed to remove control from different user</div>";
        }
    } else {
        logTest("Operator Remove Control from Any User", true, "No other user controls found (operators can manage all controls)");
        echo "<div class='alert alert-info'>ℹ️ No other user controls found (operators can manage all controls)</div>";
    }
} catch (Exception $e) {
    logTest("Operator Remove Control from Any User", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error in test: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

// Test 11: Test control search functionality
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 11: Control Search Functionality</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    $userControls = Control::getByApartamentoUsuario($apartamentoUsuarioId);
    if (count($userControls) > 0) {
        $searchTerm = $userControls[0]->numero_control_completo;
        $searchResults = Control::buscar($searchTerm);

        if (count($searchResults) > 0) {
            $found = false;
            foreach ($searchResults as $result) {
                if ($result['numero_control_completo'] === $searchTerm) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                logTest("Control Search", true, "Successfully found control {$searchTerm} in search results");
                echo "<div class='alert alert-success'>✅ Successfully found control {$searchTerm} in search results</div>";
            } else {
                logTest("Control Search", false, "Control {$searchTerm} not found in search results");
                echo "<div class='alert alert-danger'>❌ Control {$searchTerm} not found in search results</div>";
            }
        } else {
            logTest("Control Search", false, "No search results found");
            echo "<div class='alert alert-danger'>❌ No search results found</div>";
        }
    } else {
        logTest("Control Search", false, "No controls available for search test");
        echo "<div class='alert alert-warning'>⚠️ No controls available for search test</div>";
    }
} catch (Exception $e) {
    logTest("Control Search", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error in search test: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

echo "</div>";

// ==================== CLEANUP ====================

echo "<div class='test-section'>";
echo "<h2>🧹 Cleanup</h2>";

// Test 12: Remove all test controls
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 12: Remove All Test Controls</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    $userControls = Control::getByApartamentoUsuario($apartamentoUsuarioId);
    $removed = 0;

    foreach ($userControls as $control) {
        if ($control->desasignar('Test cleanup', 1)) {
            $removed++;
        }
    }

    logTest("Remove Test Controls", true, "Removed {$removed} test controls");
    echo "<div class='alert alert-success'>✅ Removed {$removed} test controls</div>";
} catch (Exception $e) {
    logTest("Remove Test Controls", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error removing test controls: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

// Test 13: Remove test apartment assignment
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'>Test 13: Remove Test Apartment Assignment</h5>";
echo "</div>";
echo "<div class='card-body'>";

try {
    $result = Database::execute(
        "UPDATE apartamento_usuario SET activo = 0 WHERE usuario_id = ? AND activo = 1",
        [$testUser->id]
    );

    if ($result) {
        logTest("Remove Test Apartment", true, "Deactivated apartment assignment for test user");
        echo "<div class='alert alert-success'>✅ Deactivated apartment assignment for test user</div>";
    } else {
        logTest("Remove Test Apartment", false, "Failed to remove apartment assignment");
        echo "<div class='alert alert-warning'>⚠️ Failed to remove apartment assignment</div>";
    }
} catch (Exception $e) {
    logTest("Remove Test Apartment", false, "Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>❌ Error removing apartment: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

// Test 14: Optionally remove test user (commented out for safety)
// echo "<div class='card mb-3'>";
// echo "<div class='card-header'>";
// echo "<h5 class='mb-0'>Test 14: Remove Test User</h5>";
// echo "</div>";
// echo "<div class='card-body'>";

// try {
//     $result = Database::execute("DELETE FROM usuarios WHERE id = ?", [$testUser->id]);

//     if ($result) {
//         logTest("Remove Test User", true, "Removed test user");
//         echo "<div class='alert alert-success'>✅ Removed test user</div>";
//     } else {
//         logTest("Remove Test User", false, "Failed to remove test user");
//         echo "<div class='alert alert-warning'>⚠️ Failed to remove test user</div>";
//     }
// } catch (Exception $e) {
//     logTest("Remove Test User", false, "Error: " . $e->getMessage());
//     echo "<div class='alert alert-danger'>❌ Error removing user: " . $e->getMessage() . "</div>";
// }

// echo "</div></div>";

echo "</div>";

// Display final results
displayTestResults();

// Summary
echo "<div class='card mt-4'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'><i class='bi bi-bar-chart'></i> Final Summary</h5>";
echo "</div>";
echo "<div class='card-body'>";
echo "<div class='row'>";
echo "<div class='col-md-6'>";
echo "<h6>Test Coverage</h6>";
echo "<ul>";
echo "<li>✅ New user registration with control assignment</li>";
echo "<li>✅ Existing user control management (add/remove)</li>";
echo "<li>✅ Control state changes (block/unblock)</li>";
echo "<li>✅ Edge cases and error handling</li>";
echo "<li>✅ Database state verification</li>";
echo "<li>✅ Search functionality</li>";
echo "</ul>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<h6>Database Tables Tested</h6>";
echo "<ul>";
echo "<li>✅ controles_estacionamiento</li>";
echo "<li>✅ apartamento_usuario</li>";
echo "<li>✅ usuarios</li>";
echo "<li>✅ apartamentos</li>";
echo "</ul>";
echo "<h6>Models Tested</h6>";
echo "<ul>";
echo "<li>✅ Control</li>";
echo "<li>✅ Usuario</li>";
echo "</ul>";
echo "</div>";
echo "</div>";
echo "</div></div>";

echo "</div></body></html>";