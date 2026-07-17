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
    // ── Year helpers ─────────────────────────────────────────────────────────

    public function getYear(int $sessionKey): int
    {
        return floor($sessionKey / 1000) + 2023;
    }

    public function getSessionKey(): int
    {
        $targetDate = Carbon::now('America/Chicago')->addHours(4);

        $nextRace = Race::where('date_start', '>=', $targetDate)->min('session_key');

        if ($nextRace) {
            return $nextRace;
        }

        return Race::max('session_key') ?? 1000;
    }

    public function getLatestSessionKey(int $sessionKey): int
    {
        return Winner::where('session_key', '<=', $sessionKey)
            ->max('session_key') ?? $sessionKey;
    }

    // ── Data fetchers ────────────────────────────────────────────────────────

    public function getDrivers(int $year): Collection
    {
        return Driver::where('year', $year)->get();
    }

    /** Alias used by older controller code */
    public function getContestants(int $year): Collection
    {
        return $this->getDrivers($year);
    }

    public function getPlayer(int $userId): ?User
    {
        return User::with('picks')->find($userId);
    }

    public function getPlayers(int $year): Collection
    {
        $players = User::with(['picks' => function ($query) use ($year) {
            $startKey = ($year - 2023) * 1000;
            $endKey   = $startKey + 999;
            $query->whereBetween('session_key', [$startKey, $endKey]);
        }])->get();

        return $players->map(function ($player) use ($year) {
            $player->total_score = $player->getTotal($year);
            return $player;
        })->sortByDesc('total_score')->values();
    }

    public function getPicks(int $sessionKey): Collection
    {
        return Pick::with('user', 'd1', 'd2', 'd3')
            ->where('session_key', $sessionKey)
            ->get();
    }

    public function getWinners(int $sessionKey): Collection
    {
        return Winner::with('driver')
            ->where('session_key', $sessionKey)
            ->orderBy('position', 'asc')
            ->get();
    }

    /** All Race rows for a weekend (ordered chronologically — used for bonus tiers) */
    public function getSessionRaces(int $sessionKey): Collection
    {
        return Race::where('session_key', $sessionKey)
            ->orderBy('date_start')
            ->get();
    }

    /** Alias matching the old controller call */
    public function getMainRace(int $sessionKey): ?object
    {
        return $this->getRace($sessionKey);
    }

    public function getRace(int $sessionKey): ?object
    {
        return Race::where('session_key', $sessionKey)->where('type', 'G')->first();
    }

    // ── Scoring ──────────────────────────────────────────────────────────────

    public function updateScores(int $sessionKey): void
    {
        $winners = $this->getWinners($sessionKey);
        if ($winners->isEmpty()) return;

        $players = $this->getPicks($sessionKey);
        $this->evaluatePicks($winners, $players, $sessionKey);
    }

    /** Alias matching the old controller call */
    public function recalculateScores(int $sessionKey): void
    {
        $this->updateScores($sessionKey);
    }

    public function evaluatePicks(Collection $winners, Collection $players, int $sessionKey): void
    {
        $firstPlaceId = $winners->where('position', 1)->first()?->driver_id;
        $tenthPlaceId = $winners->where('position', 10)->first()?->driver_id;
        $lastPlaceId  = $winners->sortByDesc('position')->first()?->driver_id;

        foreach ($players as $player) {
            $pick = $player->picks->first();
            if (! $pick) continue;

            $score = 0;
            if ($pick->d1_id === $firstPlaceId) $score += 7;
            if ($pick->d2_id === $tenthPlaceId) $score += 5;
            if ($pick->d3_id === $lastPlaceId)  $score += 3;

            $player->setScore($sessionKey, $score);
        }
    }

    public function getCorrectBets(Collection $winners, Collection $picks): array
    {
        if ($winners->isEmpty()) return [];

        $first = $winners->where('position', 1)->first()?->driver_id;
        $tenth = $winners->where('position', 10)->first()?->driver_id;
        $last  = $winners->sortByDesc('position')->first()?->driver_id;

        $correctBets = [];
        foreach ($picks as $pick) {
            $correctBets[] = [
                $pick->d1_id == $first ? 1 : 0,
                $pick->d2_id == $tenth ? 1 : 0,
                $pick->d3_id == $last  ? 1 : 0,
            ];
        }
        return $correctBets;
    }

    // ── API sync ─────────────────────────────────────────────────────────────

    public function insertWinners(int $sessionKey): void
    {
        if (Winner::where('session_key', $sessionKey)->exists()) return;

        $year    = $this->getYear($sessionKey);
        $raceKey = $sessionKey % 1000;

        $response = Http::withUserAgent('F1-Data-Fetcher/2.0')
            ->get("https://api.jolpi.ca/ergast/f1/{$year}/{$raceKey}/results.json");

        if (! $response->successful()) return;

        $races = $response->json('MRData.RaceTable.Races');
        if (empty($races)) return;

        $results  = $races[0]['Results'] ?? [];
        $dbDrivers = Driver::where('year', $year)->get()->keyBy('number');

        foreach ($results as $result) {
            $f1Number = (int) $result['number'];
            $position = (int) $result['position'];
            $driver   = $dbDrivers->get($f1Number);

            if ($driver) {
                Winner::create([
                    'driver_id'   => $driver->id,
                    'position'    => $position,
                    'session_key' => $sessionKey,
                ]);
            }
        }
    }

    public function updateSchedule(int $year): void
    {
        $yearKey = ($year - 2023) * 1000;

        $response = Http::withUserAgent('F1-Schedule-App/2.0')
            ->get("https://api.jolpi.ca/ergast/f1/{$year}.json");

        if (! $response->successful()) return;

        $races = $response->json('MRData.RaceTable.Races', []);

        $apiSessionKeys = [
            'FirstPractice'    => 'FP1', 'SecondPractice' => 'FP2',
            'ThirdPractice'    => 'FP3', 'Qualifying'     => 'Q',
            'SprintQualifying' => 'SQ',  'SprintShootout' => 'SQ',
            'Sprint'           => 'S',   'GrandPrix'      => 'G',
        ];

        foreach ($races as $race) {
            $sessionKey = $yearKey + (int) $race['round'];
            $raceName   = $race['raceName'];

            if (isset($race['date'])) {
                $time = $race['time'] ?? '00:00:00Z';
                $this->upsertRace($sessionKey, 'G', $raceName, $race['date'] . 'T' . $time);
            }

            foreach ($apiSessionKeys as $apiKey => $type) {
                if (isset($race[$apiKey])) {
                    $time = $race[$apiKey]['time'] ?? '00:00:00Z';
                    $this->upsertRace($sessionKey, $type, $raceName, $race[$apiKey]['date'] . 'T' . $time);
                }
            }
        }
    }

    private function upsertRace($sessionKey, $type, $name, $dateStart): void
    {
        Race::updateOrCreate(
            ['session_key' => $sessionKey, 'type' => $type],
            ['name' => $name, 'date_start' => Carbon::parse($dateStart)]
        );
    }
}
