<?php

namespace App\Http\Controllers;

use App\Services\PickemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pick;
use App\Http\Controllers\Race;

class PickController extends Controller
{
    public function __construct(private PickemService $service)
    {
        // Authentication is handled by route middleware.
    }

    public function index(Request $request)
    {
        $sessionKey = $request->query('sessionKey') ?: $this->service->getSessionKey();
        $year = $this->service->getYear($sessionKey);
        $races = $this->service->getSessionRaces($sessionKey);
        $mainRace = $this->service->getMainRace($sessionKey);
        $contestants = $this->service->getContestants($year);

        $bonus = 1.0;
        $race = $mainRace;
        if ($races->isNotEmpty()) {
            $now = now('US/Central');
            foreach ($races as $index => $sessionRace) {
                if ($now->lt($sessionRace->date_start)) {
                    $bonuses = [1.50, 1.25, 1.10, 1.00, 0.50];
                    $bonus = $bonuses[$index] ?? 1.0;
                    break;
                }
            }
        }

        $sessionKey = (int) $sessionKey;
        $lastPlace = $this->service->getContestants($year)->last()?->number ?: 20;

        return view('picks.set', compact('sessionKey', 'year', 'races', 'mainRace', 'contestants', 'bonus', 'picks', 'lastPlace'));
    }

    public function submit(Request $request)
    {
        $bettor = $request->input('bettor');
        $payload = json_decode($bettor, true);

        if (! is_array($payload) || empty($payload['bets']) || count($payload['bets']) !== 3) {
            return response()->json(['error' => 'Invalid payload'], 422);
        }

        $bets = array_map('intval', $payload['bets']);
        if (in_array(0, $bets, true)) {
            return response()->json(['error' => 'Missing bets'], 422);
        }

        $sessionKey = $this->service->getSessionKey();
        $pick = Pick::updateOrCreate(
            ['user_id' => Auth::id(), 'session_key' => $sessionKey],
            [
                'd1_id' => $bets[0],
                'd2_id' => $bets[1],
                'd3_id' => $bets[2],
                'bonus' => floatval($payload['bonus'] ?? 1.0),
                'score' => 0,
            ]
        );

        $this->service->recalculateScores($sessionKey);

        return response()->json(['status' => 'Successfully added new entry']);
    }

    public function view(Request $request)
    {
        $sessionKey = 3006;//$this->service->getSessionKey(); // The true session key for the upcoming weekend
        $year = $this->service->getYear((int) $sessionKey);
        $picks = $this->service->getPicks($sessionKey);
        $winners = $this->service->getWinners($sessionKey);
        $players = $this->service->getPlayers($sessionKey);
        $correctBets = $this->service->getCorrectBets($winners, $players);
        // $selectedSessionKey = $this->service->getSessionKey(); // The session key that defines the view
        $selectedSessionKey = $request->query('sessionKey') ?: $sessionKey; // The session key that defines the view
        $race = $this->service->getRace($sessionKey);
        // $standings = $this->service->getTotalsForYear(intdiv($sessionKey, 1000) * 1000);
        // $sessionList = $this->service->getSessionKeysForYear($sessionKey);

        $selectedLatestSessionKey = $this->service->getLatestSessionKey($selectedSessionKey);
        $selectedFirstSessionKey = intdiv($selectedSessionKey, 1000) * 1000 + 1;
        $sessionList = $selectedLatestSessionKey > $selectedFirstSessionKey ? range($selectedFirstSessionKey, $selectedLatestSessionKey) : [$selectedFirstSessionKey];
        // $currentPick = Pick::where('user_id', Auth::id())
        //     ->where('session_key', $sessionKey)
        //     ->first();
        $status = 'picked';//$currentPick ? 'picked' : 'unpicked';
        $message = "";
        // if ($sessionKey !== $currentSessionKey) {
        //     $status = 'history';
        // }

        $lastPlace = $this->service->getDrivers($year)->last()?->position ?: 20;

        return view('picks.view', compact('sessionKey', 'selectedSessionKey', 'sessionList', 'year', 'picks', 'players', 'race', 'winners', 'correctBets', 'players', 'status', 'message', 'lastPlace'));
    }
}
