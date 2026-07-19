<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Seeder;

class ProblemSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $response = Http::timeout(10)->get('https://codeforces.com/api/problemset.problems');
            
            if ($response->successful()) {
                $data = $response->json();
                $problems = $data['result']['problems'] ?? [];
                
                $count = 0;
                foreach ($problems as $p) {
                    if ($count >= 200) break;
                    
                    $contestId = $p['contestId'] ?? '';
                    $index = $p['index'] ?? '';
                    $title = $contestId . $index . ' - ' . ($p['name'] ?? 'Unknown');
                    $rating = $p['rating'] ?? 800;
                    $tags = implode(',', $p['tags'] ?? []);
                    
                    $exists = DB::selectOne("SELECT COUNT(*) as cnt FROM problems WHERE title = ?", [$title]);
                    if ($exists && $exists->cnt > 0) continue;
                    
                    DB::insert('INSERT INTO problems (title, rating, tags, platform, cf_contest_id, cf_index) VALUES (?, ?, ?, ?, ?, ?)', [
                        $title, $rating, $tags, 'Codeforces', (string)$contestId, $index
                    ]);
                    
                    $count++;
                }
            }
        } catch (\Exception $e) {
        }
        
        $fallbacks = [
            ['title' => '4A - Watermelon', 'rating' => 800, 'tags' => 'implementation,math', 'cf_contest_id' => '4', 'cf_index' => 'A'],
            ['title' => '71A - Way Too Long Words', 'rating' => 800, 'tags' => 'implementation,strings', 'cf_contest_id' => '71', 'cf_index' => 'A'],
            ['title' => '158A - Next Round', 'rating' => 800, 'tags' => 'implementation', 'cf_contest_id' => '158', 'cf_index' => 'A'],
            ['title' => '231A - Team', 'rating' => 800, 'tags' => 'implementation', 'cf_contest_id' => '231', 'cf_index' => 'A'],
            ['title' => '50A - Domino piling', 'rating' => 800, 'tags' => 'implementation,math', 'cf_contest_id' => '50', 'cf_index' => 'A'],
            ['title' => '282A - Bit++', 'rating' => 800, 'tags' => 'implementation', 'cf_contest_id' => '282', 'cf_index' => 'A'],
            ['title' => '69A - Young Physicist', 'rating' => 1000, 'tags' => 'implementation,math', 'cf_contest_id' => '69', 'cf_index' => 'A'],
            ['title' => '263A - Beautiful Matrix', 'rating' => 800, 'tags' => 'implementation', 'cf_contest_id' => '263', 'cf_index' => 'A'],
            ['title' => '266B - Queue at the School', 'rating' => 1000, 'tags' => 'implementation,constructive algorithms', 'cf_contest_id' => '266', 'cf_index' => 'B'],
            ['title' => '43A - Football', 'rating' => 900, 'tags' => 'implementation,strings', 'cf_contest_id' => '43', 'cf_index' => 'A'],
        ];
        
        foreach ($fallbacks as $p) {
            $exists = DB::selectOne("SELECT COUNT(*) as cnt FROM problems WHERE title = ?", [$p['title']]);
            if (!$exists || $exists->cnt == 0) {
                DB::insert('INSERT INTO problems (title, rating, tags, platform, cf_contest_id, cf_index) VALUES (?, ?, ?, ?, ?, ?)', [
                    $p['title'], $p['rating'], $p['tags'], 'Codeforces', $p['cf_contest_id'], $p['cf_index']
                ]);
            }
        }
    }
}