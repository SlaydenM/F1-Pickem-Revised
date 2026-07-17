<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Pick;
use App\Models\Race;
use App\Models\User;
use App\Models\Winner;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PickemService
{
    /**
     * Calculates the year based on session (getYear)
     */
    public function getYear(int $sessionKey): int
    {
        return floor($sessionKey / 1000) + 2023;
    }

    /**
     * Returns the current session key based on current date (getSessionKey)
     */
    public function getSessionKey(): int
    {
        // Add 4 hours to current time to find the active/next race
        $targetDate = Carbon::now('America/Chicago')->addHours(4);

        $nextRace = Race::where('date_start', '>=', $targetDate)
            ->min('session_key');
            // ->orderBy('session_key', 'asc')
            // ->first();

        if ($nextRace) {
            return $nextRace->session_key;
        }

        // Fallback: Return the very last session key if season is over
        return Race::max('session_key') ?? 1000;
    }

    /**
     * Returns the latest session key for a given session key (getLatestSessionKey)
     */
    public function getLatestSessionKey(int $sessionKey): int
    {
        $yearKey = intdiv($sessionKey, 1000) * 1000;

        // Get the latest session key via the winners table
        return Winner::where('session_key', '>=', $sessionKey)
            ->max('session_key') ?? $sessionKey;
            // ->orderBy('session_key', 'desc')
            // ->first()?->session_key ?? $yearKey;
    }

    /**
     * List of yearly drivers (getDrivers)
     */
    public function getDrivers(int $year): Collection
    {
        return Driver::where('year', $year)->get();
    }

    /**
     * Player with ‘picks’ array (getPlayer)
     */
    public function getPlayer(int $userId): ?User
    {
        // Eager load the picks relationship to avoid N+1 queries
        return User::with('picks')->find($userId);
    }

    /**
     * List of players with total scores sorted descending (getPlayers)
     */
    public function getPlayers(int $year): Collection
    {
        $players = User::with(['picks' => function ($query) use ($year) {
            $startKey = ($year - 2023) * 1000;
            $endKey = $startKey + 999;
            $query->whereBetween('session_key', [$startKey, $endKey]);
        }])->get();

        // Map over players to append their total score, then sort
        return $players->map(function ($player) use ($year) {
            $player->total_score = $player->getTotal($year);
            return $player;
        })->sortByDesc('total_score')->values();
    }

    /**
     * List of players with session scores (getPicks)
     */
    public function getPicks(int $sessionKey): Collection
    {
        return Pick::with('user', 'd1', 'd2', 'd3')
            ->where('session_key', $sessionKey)
            ->get();
    
        // return User::with(['picks' => function ($query) use ($sessionKey) {
        //     $query->where('session_key', $sessionKey);
        // }])->whereHas('picks', function ($query) use ($sessionKey) {
        //     $query->where('session_key', $sessionKey);
        // })->get();
    }

    /**
     * List of winners for session (getWinners)
     */
    public function getWinners(int $sessionKey): Collection
    {
        return Winner::with('driver')
            ->where('session_key', $sessionKey)
            ->orderBy('position', 'asc')
            ->get();
    }

    /**
     * Fetch from API and add to winners (insertWinners)
     */
    public function insertWinners(int $sessionKey): void
    {
        // Check if we already have winners for this session
        if (Winner::where('session_key', $sessionKey)->exists()) {
            return; 
        }

        $year = $this->getYear($sessionKey);
        $raceKey = $sessionKey % 1000;
        
        $response = Http::withUserAgent('F1-Data-Fetcher/2.0')
            ->get("https://api.jolpi.ca/ergast/f1/{$year}/{$raceKey}/results.json");

        if (!$response->successful()) {
            return;
        }

        $races = $response->json('MRData.RaceTable.Races');
        if (empty($races)) return;

        $results = $races[0]['Results'] ?? [];

        // Fetch all active drivers for the year to map F1 numbers to your DB IDs
        $dbDrivers = Driver::where('year', $year)->get()->keyBy('number');

        foreach ($results as $result) {
            $f1Number = (int) $result['number'];
            $position = (int) $result['position'];

            // Find the corresponding driver ID in your database
            $driver = $dbDrivers->get($f1Number);

            if ($driver) {
                Winner::create([
                    'driver_id' => $driver->id,
                    'position' => $position,
                    'session_key' => $sessionKey
                ]);
            }
        }
    }

    /**
     * Fetch API and update DB Schedule (updateScedule)
     */
    public function updateSchedule(int $year): void
    {
        $yearKey = ($year - 2023) * 1000;
        
        $response = Http::withUserAgent('F1-Schedule-App/2.0')
            ->get("https://api.jolpi.ca/ergast/f1/{$year}.json");

        if (!$response->successful()) return;

        $races = $response->json('MRData.RaceTable.Races', []);

        $apiSessionKeys = [
            'FirstPractice' => 'FP1', 'SecondPractice' => 'FP2',
            'ThirdPractice' => 'FP3', 'Qualifying' => 'Q',
            'SprintQualifying' => 'SQ', 'SprintShootout' => 'SQ',
            'Sprint' => 'S', 'GrandPrix' => 'G'
        ];

        foreach ($races as $race) {
            $sessionKey = $yearKey + (int)$race['round'];
            $raceName = $race['raceName'];

            // Grand Prix
            if (isset($race['date'])) {
                $time = $race['time'] ?? '00:00:00Z';
                $this->upsertRace($sessionKey, 'G', $raceName, $race['date'] . 'T' . $time);
            }

            // Other Sessions
            foreach ($apiSessionKeys as $apiKey => $type) {
                if (isset($race[$apiKey])) {
                    $time = $race[$apiKey]['time'] ?? '00:00:00Z';
                    $this->upsertRace($sessionKey, $type, $raceName, $race[$apiKey]['date'] . 'T' . $time);
                }
            }
        }
    }

    private function upsertRace($sessionKey, $type, $name, $dateStart)
    {
        Race::updateOrCreate(
            ['session_key' => $sessionKey, 'type' => $type],
            ['name' => $name, 'date_start' => Carbon::parse($dateStart)]
        );
    }

    /**
     * Orchestrates the scoring update (updateScores)
     */
    public function updateScores(int $sessionKey): void
    {
        $winners = $this->getWinners($sessionKey);
        if ($winners->isEmpty()) return;

        $players = $this->getPicks($sessionKey);
        
        $this->evaluatePicks($winners, $players, $sessionKey);
    }

    /**
     * Core logic to evaluate picks and save to DB (evaluatePicks)
     */
    public function evaluatePicks(Collection $winners, Collection $players, int $sessionKey): void
    {
        // Map the results to find which driver IDs took which positions
        $firstPlaceId = $winners->where('position', 1)->first()?->driver_id;
        $tenthPlaceId = $winners->where('position', 10)->first()?->driver_id;
        $lastPlaceId = $winners->sortByDesc('position')->first()?->driver_id;

        foreach ($players as $player) {
            $pick = $player->picks->first(); // The pick for this session
            if (!$pick) continue;

            $score = 0;

            // d1 represents the bet for 1st place
            if ($pick->d1_id === $firstPlaceId) $score += 7;
            
            // d2 represents the bet for 10th place
            if ($pick->d2_id === $tenthPlaceId) $score += 5;
            
            // d3 represents the bet for last place
            if ($pick->d3_id === $lastPlaceId) $score += 3;

            // Save using the User model method
            $player->setScore($sessionKey, $score);
        }
    }
    /**
     * Recalculates scores for a session (recalculateScores)
     */
    public function getCorrectBets(Collection $winners, Collection $picks): array
    {
        if (empty($winners)) // Do not evaluate if winners are not yet out
            return [];
        
        $correctBets = [];
        foreach ($picks as $pick) {
            $currBets = [0, 0, 0]; // [1st place, 10th place, last place]
            if ($pick->d1_id == $winners[0]->driver_id) $currBets[0] = 1;
            if ($pick->d2_id == $winners[9]->driver_id) $currBets[1] = 1;
            if ($pick->d3_id == $winners[count($winners) - 1]->driver_id) $currBets[2] = 1;
            $correctBets[] = $currBets;
        }
        return $correctBets;
    }

    /**
     * Get race for a specific session (getRace)
     */
    public function getRace(int $sessionKey): ?object
    {
        return Race::where('session_key', $sessionKey)->where('type', 'G')->first();
    }
}