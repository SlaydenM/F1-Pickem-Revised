<x-layouts.app>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/common.css') }}">
        <link rel="stylesheet" href="{{ asset('css/viewPicksStyle.css') }}">
    @endpush

    <div id="page-name">F1 Pick'em</div>

    @if ($message === 'picked')
        <div id="status-message" style="padding: 45px 10px;">Submitted Your Picks!</div>
    @elseif ($status === 'locked')
        <div id="status-message">Picks are locked after<br>Grand Prix</div>
    @else
        <div id="filler"></div>
    @endif

    <div id="welcome-box" class="plate-1">
        <h1>
            Welcome {{ auth()->user()->name }}!<br>
            Your Score Is {{ number_format(auth()->user()->getTotal($year), 2) }}<span>PTS</span>
        </h1>
    </div>

    <div id="standings-box" class="plate-1">
        <h1>Standings</h1>
        <table class="left-panel" id="standings-list">
            <div id="standings-backer"></div>
            <tr id="standings-head" class="standings-entry">
                <td>Pos.</td>
                <td>Name</td>
                <td>Total</td>
            </tr>
            @php
                $position = 1;
                $previousScore = null;
            @endphp
            @foreach ($players as $player)
                <tr class="standings-entry">
                    <td>{{ $previousScore !== null && $entry['score'] === $previousScore ? '' : $position . '.' }}</td>
                    <td>{{ $player['name'] }}</td>
                    <td>{{ number_format($player['score'], 2) }}<span>PTS</span></td>
                </tr>
                @php
                    if ($previousScore === null || $player['score'] !== $previousScore) {
                        $position++;
                    }
                    $previousScore = $player['score'];
                @endphp
            @endforeach
        </table>

        <button id="toggleBtn">→</button>
        <div class="right-panel" id="info-wrapper">
            <div id="main-info">
                <p class='info-head'>Welcome to F1 Pick'em!</p>
                <p>A friendly game around Formula 1.</p>
                <p>Here you can view scores, picks, and results. Check out past races to get a good idea of who to pick!</p>
                <p>This year introduces bonuses, additional driver info, and new design changes!</p>
            </div>
            <div id="scoring-info">
                <p class='info-head'>Scoring Rules:</p>
                <p>1st Pick = +7<span>PTS</span></p>
                <p>10th Pick = +5<span>PTS</span></p>
                <p>{{ $lastPlace }} Pick = +3<span>PTS</span></p>
            </div>
        </div>
    </div>

    <x-layouts.choose-session :selectedSessionKey="$selectedSessionKey" :sessionList="$sessionList" :year="$year">
        <h1>
            Round #{{ $selectedSessionKey % 1000 }} - {{ optional($race)->date_start?->format('F j, Y g:ia') ?? 'TBD' }}<br>
            <i>Formula 1 <u>{{ optional($race)->name ?? 'Race Info Pending' }}</u></i>
        </h1>
    </x-layouts.choose-session>
    

    <div id="results-box" class="plate-1">
        @if ($status === 'unpicked' && $selectedSessionKey === $sessionKey)
            <h1>Place Picks</h1>
            <form action="{{ route('picks.index') }}" method="get" id="button-wrapper">
                <p>Submit your picks before viewing results</p>
                <input type="submit" id="submit-button" class="widget widget-shadow" value="Submit Picks!" />
                <input type="hidden" name="sessionKey" value="{{ $selectedSessionKey }}" />
            </form>
        @else
            <div id="weekly-box">
                <h1>Weekly Picks ({{ $picks->count() }})</h1>
                <div id="plate-2-list">
                    @foreach ($picks as $pickIndex => $pick)
                        @php
                            $player = $pick->user;
                            $score = round($pick->score * $pick->bonus, 2);
                            $rawScore = $pick->bonus ? round($pick->score, 2) : $score;
                            $bonusAmount = ($pick->bonus - 1) * 100;
                            $bonusSign = $bonusAmount >= 0 ? '+' : '-';
                        @endphp
                        <div class="bettor-box">
                            <svg class="bettor-box-poly" viewBox="0 0 100 180" preserveAspectRatio="none">
                                <polygon points="0,0 100,0 40,180 0,180"/>
                            </svg>
                            <div class="bettor-box-info">
                                @if ($pick->user->name)
                                    <h2 class="bettor-box-name">{{ $pick->user->name }}</h2>
                                @else
                                    <h2 class="bettor-box-name">Unknown User</h2>
                                @endif
                                <table class="bet-list">
                                    <tbody>
                                        @foreach ($pick->getPicks() as $betIndex => $driver)
                                            <tr>
                                                {{-- <td class="bet-label">{{ ['1st', '10th', $lastPlace][$betIndex] }}</td> --}}
                                                <td>
                                                    @if ($driver)
                                                        <img src="{{ route('driver.logo', ['year' => 2026, 'filename' => 'f1_44.png']) }}" class="driver-data widget widget-shadow {{ ($correctBets[$pickIndex][$betIndex] ?? 0) ? 'imp' : ''}}" />
                                                    @else
                                                        <span class="no-driver">No Driver</span>
                                                    @endif
                                                    {{--  alt="{{ $driver->name }}" --}}
                                                    {{-- class="driver-data widget widget-shadow {{ ($correctBets[$pickIndex][$betIndex] ?? 0) ? 'imp' : '' --}}
                                                    {{-- <img
                                                        class="driver-data widget widget-shadow {{ ($correctBets[$pickIndex][$betIndex] ?? 0) ? 'imp' : '' }}"
                                                        src="{{ asset() }}"
                                                        alt="{{ $driver->name }}"> --}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="score-container" onclick="toggleBox(event)">
                                    {{ $score }}<span>PTS</span> ▼
                                    <div class="hover-box">
                                        Raw: +{{ $rawScore }}<span>PTS</span><br>
                                        Bonus: {{ $bonusSign }}{{ abs($bonusAmount) }}%<br>
                                        Total: +{{ $score }}<span>PTS</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div id="race-box-wrapper">
                <h1>Results</h1>
                <div id="race-box">
                    <table id="driver-list">
                        @if ($winners->isEmpty())
                            <div id="empty-drivers-message">(No Winners Yet)</div>
                        @else
                            @foreach ($winners as $winner)
                                @php
                                    $position = $loop->iteration;
                                    $important = $position === 1 || $position === 10 || $loop->last ? 'imp' : '';
                                @endphp
                                <tr>
                                    <td>
                                        <p>{{ $position }}.</p>
                                    </td>
                                    <td>
                                        <img
                                            class="driver-data widget widget-shadow {{ $important }}"
                                            src="/logos/{{ $driver->year }}/{{ $driver->getFileName() }}"
                                            alt="{{ $winner->driver->name }}">
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                </div>
            </div>
        @endif
    </div>
    <div id="footer"></div>

    @push('scripts')
        <script>
            window.REFRESH_ROUTE = '{{ route('picks.view') }}';

            function refreshSessionKey(sessionKey) {
                const yearList = document.getElementById('choose-year-list');
                const yearValue = Number(yearList.value);
                const base = yearValue * 1000;
                const round = sessionKey % 1000;
                window.location.href = `${window.REFRESH_ROUTE}?sessionKey=${base + round}`;
            }

            window.onload = () => {
                const urlParams = new URLSearchParams(window.location.search);
                const sessionKey = Number(urlParams.get('sessionKey'));
                if (sessionKey) {
                    document.getElementById('choose-year-list').value = Math.floor(sessionKey / 1000);
                }
                updateStandingsElements();
                togglePanels();
            };

            window.onresize = () => updateStandingsElements();

            function toggleBox(event) {
                event.stopPropagation();

                document.querySelectorAll('.hover-box').forEach(box => {
                    if (box !== event.currentTarget.querySelector('.hover-box')) {
                        box.style.display = 'none';
                    }
                });

                const box = event.currentTarget.querySelector('.hover-box');
                box.style.display = box.style.display === 'block' ? 'none' : 'block';
            }

            const backer = document.getElementById('standings-backer');
            const header = document.getElementById('standings-head');
            const list = document.getElementById('standings-list');
            const plate = document.getElementById('standings-box');
            const info = document.getElementById('info-wrapper');
            function updateStandingsElements() {
                const isPhone = window.matchMedia('(max-width: 767px)').matches;
                backer.style.top = `${header.offsetTop + header.offsetHeight}px`;
                backer.style.left = `${header.offsetLeft + (isPhone ? 30 : 10)}px`;
                backer.style.height = `${list.offsetHeight - 2 * header.offsetHeight}px`;
                backer.style.display = 'block';
                plate.style.height = `${list.offsetHeight + 2 * header.offsetHeight}px`;
            }

            const rightPanel = document.getElementById('info-wrapper');
            const leftPanel = document.getElementById('standings-list');
            const toggleBtn = document.getElementById('toggleBtn');
            let isHidden = false;

            function togglePanels() {
                if (isHidden) {
                    leftPanel.classList.remove('panel-expanded');
                    toggleBtn.classList.remove('btn-moved');
                    updateStandingsElements();
                    setTimeout(() => {
                        rightPanel.classList.remove('panel-hidden');
                        rightPanel.style.position = 'initial';
                        toggleBtn.textContent = '→';
                    }, 100);
                } else {
                    rightPanel.classList.add('panel-hidden');
                    setTimeout(() => {
                        leftPanel.classList.add('panel-expanded');
                        toggleBtn.classList.add('btn-moved');
                        toggleBtn.textContent = '←';
                        rightPanel.style.position = 'absolute';
                        updateStandingsElements();
                    }, 200);
                }
                isHidden = !isHidden;
            }

            toggleBtn?.addEventListener('click', togglePanels);
        </script>
    @endpush
</x-layouts.app>
