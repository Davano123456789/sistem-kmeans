<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Mahasiswa;

// Jalankan K-Means dinamis (seperti di controller kita tadi)
$allMahasiswa = Mahasiswa::with('nilaiKuesioner')->whereHas('nilaiKuesioner')->get();
$k = 4;
$features = ['a1','b1','d3','a2','b3','d1','a3','b4','d2','a4','b2','d4'];

$centroids = [];
for ($i = 0; $i < $k; $i++) {
    $mhs = $allMahasiswa[$i];
    foreach ($features as $f) {
        $centroids[$i][$f] = $mhs->nilaiKuesioner->$f ?? 0;
    }
}

$maxIterations = 10;
for ($iter = 1; $iter <= $maxIterations; $iter++) {
    $clusters = array_fill(0, $k, []);
    foreach ($allMahasiswa as $mhs) {
        $distances = [];
        foreach ($centroids as $cIndex => $centroid) {
            $sum = 0;
            foreach ($features as $f) {
                $val = $mhs->nilaiKuesioner->$f ?? 0;
                $sum += pow($val - $centroid[$f], 2);
            }
            $distances[$cIndex] = sqrt($sum);
        }
        $minDistance = min($distances);
        $assignedCluster = array_search($minDistance, $distances);
        $clusters[$assignedCluster][] = $mhs;
    }
    
    $newCentroids = [];
    foreach ($clusters as $cIndex => $members) {
        if (count($members) > 0) {
            foreach ($features as $f) {
                $sum = 0;
                foreach ($members as $m) {
                    $sum += $m->nilaiKuesioner->$f ?? 0;
                }
                $newCentroids[$cIndex][$f] = $sum / count($members);
            }
        } else {
            $newCentroids[$cIndex] = $centroids[$cIndex];
        }
    }
    
    if ($centroids === $newCentroids) {
        break;
    }
    $centroids = $newCentroids;
}

// Ambil anggota Cluster 1 (index 0)
$membersC1 = $clusters[0];
echo "Cluster 1 Members Count: " . count($membersC1) . "\n";
echo "Centroid a1: " . $centroids[0]['a1'] . "\n\n";

$sumA1 = 0;
foreach ($membersC1 as $idx => $m) {
    $val = $m->nilaiKuesioner->a1;
    $sumA1 += $val;
    echo ($idx + 1) . ". " . $m->nama . " (a1 = $val)\n";
}

echo "\nTotal sum of a1 in Cluster 1: $sumA1\n";
echo "Calculation: $sumA1 / " . count($membersC1) . " = " . ($sumA1 / count($membersC1)) . "\n";
