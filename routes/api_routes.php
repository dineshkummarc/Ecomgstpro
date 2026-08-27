<?php

// routes/api.php
// Add these routes to your existing api.php

use App\Http\Controllers\GstrParserController;

Route::prefix('gstr')->group(function () {
    // Single file parse
    Route::post('/parse', [GstrParserController::class, 'parse']);
    // Batch parse (multiple files)
    Route::post('/parse-batch', [GstrParserController::class, 'parseBatch']);
});
