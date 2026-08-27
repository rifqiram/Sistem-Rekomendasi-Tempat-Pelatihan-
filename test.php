<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuestionnaireResponse;
use App\Models\Profile;

$answers = json_encode(['bidang_diminati' => 'Programming', 'metode_pelatihan' => 'Online', 'tingkat_keahlian' => 'Beginner']);
echo $answers;
