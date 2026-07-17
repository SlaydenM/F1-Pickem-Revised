import { useState, useEffect, useCallback } from "react";
import { Trophy, Clock, ChevronDown, Flag, User, CheckCircle, RotateCcw, Lock } from "lucide-react";
import logoImg from "../imports/7A59FD0D-3A50-4DDE-B25C-3F6AD3C122D7.PNG";
import bgSvg from "../imports/slanted-gradient_2_.svg";

// Vite glob — picks up every f1_*.png in the imports folder at build time
const driverImageModules = import.meta.glob("../imports/f1_*.png", {
  eager: true,
  import: "default",
}) as Record<string, string>;

function getDriverImg(number: number): string | undefined {
  return driverImageModules[`../imports/f1_${number}.png`];
}

// ─── Types ────────────────────────────────────────────────────────────────────
type Page = "home" | "next-race" | "past-races" | "rules";
type YearKey = "2026" | "2025";

interface Driver {
  id: number;
  name: string;
  team: string;
  number: number;
  color: string;
}

interface Pick {
  first: Driver | null;
  tenth: Driver | null;
  last: Driver | null;
}

// ─── Data ─────────────────────────────────────────────────────────────────────
const DRIVERS: Driver[] = [
  { id: 1,  name: "Verstappen", team: "Red Bull",     number: 1,  color: "#3671C6" },
  { id: 4,  name: "Norris",     team: "McLaren",      number: 4,  color: "#FF8000" },
  { id: 16, name: "Leclerc",    team: "Ferrari",      number: 16, color: "#E8002D" },
  { id: 44, name: "Hamilton",   team: "Mercedes",     number: 44, color: "#27F4D2" },
  { id: 55, name: "Sainz",      team: "Williams",     number: 55, color: "#37BEDD" },
  { id: 63, name: "Russell",    team: "Mercedes",     number: 63, color: "#27F4D2" },
  { id: 11, name: "Pérez",      team: "Red Bull",     number: 11, color: "#3671C6" },
  { id: 14, name: "Alonso",     team: "Aston Martin", number: 14, color: "#229971" },
  { id: 10, name: "Gasly",      team: "Alpine",       number: 10, color: "#FF87BC" },
  { id: 31, name: "Ocon",       team: "Haas",         number: 31, color: "#B6BABD" },
  { id: 23, name: "Albon",      team: "Williams",     number: 23, color: "#37BEDD" },
  { id: 18, name: "Stroll",     team: "Aston Martin", number: 18, color: "#229971" },
  { id: 77, name: "Bottas",     team: "Kick Sauber",  number: 77, color: "#52E252" },
  { id: 24, name: "Zhou",       team: "Kick Sauber",  number: 24, color: "#52E252" },
  { id: 20, name: "Magnussen",  team: "Haas",         number: 20, color: "#B6BABD" },
  { id: 22, name: "Tsunoda",    team: "RB",           number: 22, color: "#6692FF" },
  { id: 3,  name: "Ricciardo",  team: "RB",           number: 3,  color: "#6692FF" },
  { id: 27, name: "Hülkenberg", team: "Kick Sauber",  number: 27, color: "#52E252" },
  { id: 38, name: "Bearman",    team: "Haas",         number: 38, color: "#B6BABD" },
  { id: 6,  name: "Hadjar",     team: "RB",           number: 6,  color: "#6692FF" },
];

const STANDINGS = [
  { rank: 1, name: "Slayden", points: 142, prev: 1 },
  { rank: 2, name: "Evan",    points: 128, prev: 3 },
  { rank: 3, name: "Cullen",  points: 115, prev: 2 },
  { rank: 4, name: "Marcus",  points: 98,  prev: 4 },
  { rank: 5, name: "Priya",   points: 87,  prev: 6 },
  { rank: 6, name: "Diego",   points: 74,  prev: 5 },
  { rank: 7, name: "Yuki",    points: 61,  prev: 7 },
  { rank: 8, name: "Amara",   points: 52,  prev: 8 },
];

const NEXT_RACE = {
  name: "Monaco Grand Prix",
  round: 6,
  date: "May 25, 2026",
  location: "Circuit de Monaco",
  targetDate: new Date("2026-05-25T13:00:00Z"),
};

const OTHER_PICKS = [
  { player: "Slayden", first: DRIVERS[0], tenth: DRIVERS[15], last: DRIVERS[18] },
  { player: "Cullen",  first: DRIVERS[2], tenth: DRIVERS[10], last: DRIVERS[19] },
  { player: "Marcus",  first: DRIVERS[1], tenth: DRIVERS[8],  last: DRIVERS[17] },
];

const PAST_RACES: Record<YearKey, {
  round: number; name: string; date: string;
  results: string[];
  picks: { player: string; first: string; tenth: string; last: string }[];
}[]> = {
  "2026": [
    {
      round: 1, name: "Bahrain GP", date: "Mar 2, 2026",
      results: ["Verstappen","Norris","Leclerc","Hamilton","Sainz","Russell","Pérez","Alonso","Gasly","Tsunoda","Albon","Stroll","Bottas","Zhou","Magnussen","Ricciardo","Hülkenberg","Bearman","Hadjar","Ocon"],
      picks: [
        { player: "Slayden", first: "Verstappen", tenth: "Tsunoda",   last: "Ocon"    },
        { player: "Evan",    first: "Norris",      tenth: "Gasly",     last: "Hadjar"  },
        { player: "Cullen",  first: "Verstappen",  tenth: "Albon",     last: "Ocon"    },
        { player: "Marcus",  first: "Leclerc",     tenth: "Tsunoda",   last: "Bearman" },
      ],
    },
    {
      round: 2, name: "Saudi Arabian GP", date: "Mar 16, 2026",
      results: ["Norris","Verstappen","Hamilton","Leclerc","Russell","Sainz","Alonso","Pérez","Gasly","Albon","Tsunoda","Stroll","Zhou","Bottas","Ricciardo","Magnussen","Ocon","Hülkenberg","Bearman","Hadjar"],
      picks: [
        { player: "Slayden", first: "Norris",      tenth: "Albon",   last: "Hadjar"     },
        { player: "Evan",    first: "Verstappen",  tenth: "Gasly",   last: "Bearman"    },
        { player: "Cullen",  first: "Norris",      tenth: "Tsunoda", last: "Hadjar"     },
        { player: "Marcus",  first: "Hamilton",    tenth: "Albon",   last: "Hülkenberg" },
      ],
    },
    {
      round: 3, name: "Australian GP", date: "Mar 30, 2026",
      results: ["Hamilton","Leclerc","Norris","Russell","Verstappen","Alonso","Sainz","Pérez","Tsunoda","Albon","Gasly","Stroll","Bottas","Ricciardo","Magnussen","Zhou","Ocon","Hülkenberg","Hadjar","Bearman"],
      picks: [
        { player: "Slayden", first: "Hamilton",   tenth: "Albon",   last: "Bearman"    },
        { player: "Evan",    first: "Leclerc",    tenth: "Tsunoda", last: "Hadjar"     },
        { player: "Cullen",  first: "Hamilton",   tenth: "Gasly",   last: "Bearman"    },
        { player: "Marcus",  first: "Norris",     tenth: "Albon",   last: "Hülkenberg" },
      ],
    },
    {
      round: 4, name: "Japanese GP", date: "Apr 6, 2026",
      results: ["Verstappen","Hamilton","Norris","Leclerc","Russell","Sainz","Pérez","Alonso","Tsunoda","Gasly","Albon","Stroll","Bottas","Zhou","Magnussen","Ricciardo","Hülkenberg","Ocon","Bearman","Hadjar"],
      picks: [
        { player: "Slayden", first: "Verstappen", tenth: "Gasly",   last: "Hadjar"  },
        { player: "Evan",    first: "Hamilton",   tenth: "Tsunoda", last: "Bearman" },
        { player: "Cullen",  first: "Verstappen", tenth: "Albon",   last: "Hadjar"  },
        { player: "Marcus",  first: "Norris",     tenth: "Tsunoda", last: "Ocon"    },
      ],
    },
    {
      round: 5, name: "Chinese GP", date: "Apr 20, 2026",
      results: ["Leclerc","Norris","Russell","Hamilton","Verstappen","Sainz","Alonso","Pérez","Gasly","Tsunoda","Stroll","Albon","Bottas","Magnussen","Zhou","Ricciardo","Ocon","Hülkenberg","Hadjar","Bearman"],
      picks: [
        { player: "Slayden", first: "Leclerc",  tenth: "Tsunoda", last: "Bearman" },
        { player: "Evan",    first: "Norris",   tenth: "Gasly",   last: "Hadjar"  },
        { player: "Cullen",  first: "Russell",  tenth: "Tsunoda", last: "Bearman" },
        { player: "Marcus",  first: "Hamilton", tenth: "Albon",   last: "Ocon"    },
      ],
    },
  ],
  "2025": [
    {
      round: 1, name: "Bahrain GP", date: "Mar 3, 2025",
      results: ["Verstappen","Leclerc","Norris","Hamilton","Russell","Sainz","Alonso","Pérez","Tsunoda","Gasly","Albon","Stroll","Bottas","Zhou","Magnussen","Ricciardo","Hülkenberg","Ocon","Bearman","Hadjar"],
      picks: [
        { player: "Slayden", first: "Verstappen", tenth: "Gasly",   last: "Hadjar"  },
        { player: "Evan",    first: "Leclerc",    tenth: "Tsunoda", last: "Bearman" },
        { player: "Cullen",  first: "Verstappen", tenth: "Albon",   last: "Hadjar"  },
        { player: "Marcus",  first: "Hamilton",   tenth: "Gasly",   last: "Ocon"    },
      ],
    },
    {
      round: 2, name: "Saudi Arabian GP", date: "Mar 17, 2025",
      results: ["Hamilton","Norris","Verstappen","Russell","Leclerc","Sainz","Pérez","Alonso","Tsunoda","Gasly","Albon","Stroll","Bottas","Magnussen","Zhou","Ricciardo","Ocon","Hülkenberg","Bearman","Hadjar"],
      picks: [
        { player: "Slayden", first: "Hamilton",    tenth: "Gasly",   last: "Hadjar"     },
        { player: "Evan",    first: "Norris",      tenth: "Tsunoda", last: "Bearman"    },
        { player: "Cullen",  first: "Verstappen",  tenth: "Albon",   last: "Ocon"       },
        { player: "Marcus",  first: "Leclerc",     tenth: "Gasly",   last: "Hülkenberg" },
      ],
    },
  ],
};

// ─── Fixed SVG background ─────────────────────────────────────────────────────
function FixedBackground() {
  return (
    <div className="fixed -z-10 pointer-events-none" style={{ top: 0, left: 0, width: "100vw", height: "100vw" }}>
      <img
        src={bgSvg}
        alt=""
        className="w-full"
        style={{ objectFit: "fill" }}
        draggable={false}
      />
    </div>
  );
}

// ─── Driver card (image-based with text fallback) ─────────────────────────────
function DriverCard({
  driver,
  draggable,
  onDragStart,
  picked,
  size = "md",
  correct,
}: {
  driver: Driver;
  draggable?: boolean;
  onDragStart?: (e: React.DragEvent) => void;
  picked?: boolean;
  size?: "sm" | "md" | "lg";
  correct?: boolean;
}) {
  const img = getDriverImg(driver.number);
  const w = size === "lg" ? 180 : size === "sm" ? 120 : 150;
  const h = Math.round(w * (600 / 900)); // 3:2 ratio → 2/3 of width

  return (
    <div
      className={`flex-shrink-0 transition-all duration-150 ${
        draggable ? "cursor-grab active:cursor-grabbing hover:scale-105 hover:z-10" : ""
      } ${picked ? "opacity-35 saturate-0" : ""}`}
      style={{ width: w }}
    >
      <div
        draggable={draggable}
        onDragStart={onDragStart}
        className="relative overflow-hidden"
        style={{
          width: w,
          height: h,
          borderRadius: 2,
          background: "#1e1e1e",
          border: `1px solid rgba(255,255,255,0.08)`,
        }}
      >
        {img ? (
          <img
            src={img}
            alt={driver.name}
            className="w-full h-full object-cover object-top rounded-[0px]"
            draggable={false}
          />
        ) : (
          <div className="w-full h-full flex flex-col justify-between p-2"
            style={{ borderLeft: `3px solid ${driver.color}` }}>
            <span className="font-['JetBrains_Mono'] font-bold text-xs" style={{ color: driver.color }}>
              #{driver.number}
            </span>
            <div>
              <div className="font-['Barlow_Condensed'] font-black italic text-white text-sm leading-tight">{driver.name}</div>
              <div className="font-['Inter'] text-[10px] text-white/50 uppercase tracking-wider">{driver.team}</div>
            </div>
          </div>
        )}
        {/* Team color top accent */}
        <div className="absolute inset-x-0 top-0 h-[3px]" style={{ background: driver.color }} />
      </div>
      {/* Green correct bar — renders below the card */}
      <div
        className="transition-all duration-300"
        style={{
          height: 4,
          borderRadius: "0 0 2px 2px",
          background: correct ? "#22c55e" : "transparent",
          boxShadow: correct ? "0 0 8px rgba(34,197,94,0.6)" : "none",
          marginTop: 2,
        }}
      />
    </div>
  );
}

// ─── Shelf standings row ───────────────────────────────────────────────────────
// Shelf clip-path on the content; bottom border only for clean look
function StandingRow({
  entry, isMe, index,
}: {
  entry: typeof STANDINGS[0];
  isMe: boolean;
  index: number;
}) {
  const bgColor = isMe ? "#1f0500" : index % 2 === 0 ? "#232323" : "#1c1c1c";
  // Shelf: top-right at 100%, bottom-right pulled in 16px
  const clipPath = "polygon(16px 0%, 100% 0%, calc(100% - 16px) 100%, 0% 100%)";

  return (
    <div
      className="flex items-center gap-5 px-4 py-3"
      style={{
        clipPath,
        background: bgColor,
        borderBottom: isMe
          ? "1px solid rgba(225,6,0,0.4)"
          : "1px solid rgba(255,255,255,0.06)",
      }}
    >
      {/* Rank */}
      <div
        className={`font-['Barlow_Condensed'] font-black italic text-3xl w-8 text-center leading-none flex-shrink-0 ${
          entry.rank === 1 ? "text-[#FFD700]"
          : entry.rank === 2 ? "text-[#C0C0C0]"
          : entry.rank === 3 ? "text-[#CD7F32]"
          : "text-[#BBBBBB]"
        }`}
      >
        {entry.rank}
      </div>

      {/* Name */}
      <div className="flex-1 min-w-0">
        <span className={`font-['Barlow_Condensed'] font-bold uppercase tracking-wide text-base ${isMe ? "text-white" : "text-[#BBBBBB]"}`}>
          {entry.name}
        </span>
        {isMe && (
          <span className="ml-2 font-['JetBrains_Mono'] text-[10px] text-[#E10600] tracking-widest">YOU</span>
        )}
      </div>

      {/* Trend */}
      <div className="w-8 text-center flex-shrink-0">
        {entry.rank < entry.prev && (
          <span className="font-['JetBrains_Mono'] text-xs text-green-400">▲{entry.prev - entry.rank}</span>
        )}
        {entry.rank > entry.prev && (
          <span className="font-['JetBrains_Mono'] text-xs text-red-400">▼{entry.rank - entry.prev}</span>
        )}
      </div>

      {/* Points */}
      <div className="font-['JetBrains_Mono'] font-bold text-sm tabular-nums flex-shrink-0 text-right w-12 mr-5">
        <span className={isMe ? "text-[#E10600]" : "text-[#BBBBBB]"}>{entry.points}</span>
        <span className="text-[7px] text-[#BBBBBB] tracking-wider pl-1">PTS</span>
      </div>
    </div>
  );
}

// ─── Nav ──────────────────────────────────────────────────────────────────────
function Nav({ page, setPage }: { page: Page; setPage: (p: Page) => void }) {
  const tabs: { key: Page; label: string }[] = [
    { key: "home",       label: "Home"       },
    { key: "next-race",  label: "Next Race"  },
    { key: "past-races", label: "Past Races" },
    { key: "rules",      label: "Rules"      },
  ];

  return (
    <header className="fixed top-0 left-0 right-0 z-50 border-b border-white/[0.3] bg-black"
      style={{ height: 72, background: "rgba(8,8,8,0.95)", backdropFilter: "blur(12px)" }}>
      <div className="max-w-7xl mx-auto h-full flex items-center px-6 bg-black bg-[#000000]">
        {/* Logo image */}
        <div className="mr-auto flex-shrink-0">
          <img
            src={logoImg}
            alt="F1 Pick'em"
            className="h-15 w-auto object-contain"
            draggable={false}
          />
        </div>

        {/* Tabs */}
        <nav className="flex items-stretch h-full">
          {tabs.map((t) => {
            const isActive = page === t.key;
            return (
              <button
                key={t.key}
                onClick={() => setPage(t.key)}
                className={`relative px-6 font-['Barlow_Condensed'] font-bold text-base uppercase tracking-widest transition-all duration-150 cursor-pointer ${
                  isActive ? "text-white" : "text-[#BBBBBB] hover:text-white"
                }`}
              >
                {isActive && (
                  <>
                    <div className="absolute inset-0 bg-[#E10600]/[0.07]" />
                    <div
                      className="absolute inset-x-0 bottom-0 h-[3px] bg-[#E10600]"
                      style={{ clipPath: "polygon(6px 0%,100% 0%,calc(100% - 6px) 100%,0% 100%)" }}
                    />
                  </>
                )}
                <span className="relative">{t.label}</span>
              </button>
            );
          })}
        </nav>

        {/* Avatar */}
        <div className="ml-auto pl-6 flex items-center gap-3">
          <span className="font-['Inter'] text-sm text-[#BBBBBB]">Evan</span>
          <div
            className="w-9 h-9 bg-[#2a2a2a] border border-white/10 flex items-center justify-center"
            style={{ clipPath: "polygon(6px 0%,100% 0%,calc(100% - 6px) 100%,0% 100%)" }}
          >
            <User size={16} className="text-[#BBBBBB]" />
          </div>
        </div>
      </div>
    </header>
  );
}

// ─── Countdown hook ────────────────────────────────────────────────────────────
function useCountdown(target: Date) {
  const calc = useCallback(() => {
    const diff = target.getTime() - Date.now();
    if (diff <= 0) return { d: 0, h: 0, m: 0, s: 0 };
    const d = Math.floor(diff / 86400000);
    const h = Math.floor((diff % 86400000) / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    return { d, h, m, s };
  }, [target]);

  const [time, setTime] = useState(calc);
  useEffect(() => {
    const id = setInterval(() => setTime(calc()), 1000);
    return () => clearInterval(id);
  }, [calc]);
  return time;
}

// ─── HOME PAGE ────────────────────────────────────────────────────────────────
function HomePage() {
  const countdown = useCountdown(NEXT_RACE.targetDate);
  const currentUser = "Evan";
  const me = STANDINGS.find((s) => s.name === currentUser)!;
  const picksSubmitted = false; // toggle to show submitted state

  return (
    <div className="min-h-screen pt-[72px]">
      <div className="max-w-7xl mx-auto px-6 py-8">
        <div className="grid grid-cols-[1fr_380px] gap-6 items-start">

          {/* ── LEFT: Standings ─────────────────────────────────────────── */}
          <section>
            <div className="flex items-center gap-4 mb-5">
              <div className="w-1 h-8 bg-[#E10600]" />
              <div>
                <h1 className="font-['Barlow_Condensed'] font-black italic text-3xl text-white tracking-tight uppercase leading-none">
                  Season Standings
                </h1>
                <div className="font-['JetBrains_Mono'] text-[#BBBBBB] text-[10px] tracking-wider mt-0.5">
                  2026 · Round {NEXT_RACE.round - 1} of 24 complete
                </div>
              </div>
            </div>

            {/* Column headers */}
            <div className="flex items-center gap-5 px-4 pb-2">
              <div className="w-8 font-['JetBrains_Mono'] text-[10px] text-[#BBBBBB] uppercase tracking-widest text-center">#</div>
              <div className="flex-1 font-['JetBrains_Mono'] text-[10px] text-[#BBBBBB] uppercase tracking-widest">Player</div>
              <div className="w-8" />
              <div className="font-['JetBrains_Mono'] text-[10px] text-[#BBBBBB] uppercase tracking-widest w-24 text-right">Points</div>
            </div>

            <div className="space-y-[3px]">
              {STANDINGS.map((s, i) => (
                <StandingRow key={s.name} entry={s} isMe={s.name === currentUser} index={i} />
              ))}
            </div>
          </section>

          {/* ── RIGHT: Action Center ─────────────────────────────────────── */}
          <section className="flex flex-col gap-4">

            {/* Countdown */}
            <div className="bg-[#1c1c1c] border border-white/[0.07] p-5"
              style={{ borderRadius: 2 }}>
              <div className="flex items-center gap-2 mb-3">
                <Clock size={13} className="text-[#E10600]" />
                <span className="font-['Barlow_Condensed'] font-bold uppercase tracking-widest text-xs text-[#BBBBBB]">Next Race</span>
              </div>
              <div className="font-['Barlow_Condensed'] font-black italic text-white text-lg uppercase leading-tight mb-0.5">
                {NEXT_RACE.name}
              </div>
              <div className="font-['JetBrains_Mono'] text-[#BBBBBB] text-[10px] mb-4">
                {NEXT_RACE.location} · {NEXT_RACE.date}
              </div>
              <div className="grid grid-cols-4 gap-2">
                {[
                  { val: countdown.d, label: "DAYS" },
                  { val: countdown.h, label: "HRS"  },
                  { val: countdown.m, label: "MIN"  },
                  { val: countdown.s, label: "SEC"  },
                ].map(({ val, label }) => (
                  <div key={label} className="bg-[#141414] p-2.5 text-center" style={{ borderRadius: 2 }}>
                    <div className="font-['Barlow_Condensed'] font-black text-2xl text-white leading-none tabular-nums">
                      {String(val).padStart(2, "0")}
                    </div>
                    <div className="font-['JetBrains_Mono'] text-[#BBBBBB] text-[9px] tracking-widest mt-1">{label}</div>
                  </div>
                ))}
              </div>
            </div>

            {/* My Season */}
            <div className="bg-[#1c1c1c] border border-white/[0.07] p-5" style={{ borderRadius: 2 }}>
              <div className="font-['Barlow_Condensed'] font-bold uppercase tracking-widest text-xs text-[#BBBBBB] mb-3">My Season</div>
              <div className="grid grid-cols-3 gap-2">
                {[
                  { val: me.points,           label: "TOTAL PTS" },
                  { val: `#${me.rank}`,        label: "RANK"      },
                  { val: NEXT_RACE.round - 1,  label: "RACES"     },
                ].map(({ val, label }) => (
                  <div key={label} className="bg-[#141414] p-3 text-center" style={{ borderRadius: 2 }}>
                    <div className="font-['Barlow_Condensed'] font-black italic text-[#E10600] text-2xl leading-none">
                      {val}
                    </div>
                    <div className="font-['JetBrains_Mono'] text-[#BBBBBB] text-[9px] tracking-widest mt-1">{label}</div>
                  </div>
                ))}
              </div>
            </div>

            {/* Picks status */}
            {picksSubmitted ? (
              <div className="bg-[#0d1a0d] border border-green-900/40 p-5" style={{ borderRadius: 2 }}>
                <div className="flex items-center gap-2 mb-1">
                  <CheckCircle size={14} className="text-green-400" />
                  <span className="font-['Barlow_Condensed'] font-black italic text-white text-base uppercase">Picks Submitted</span>
                </div>
                <div className="font-['Inter'] text-green-800 text-sm">
                  Your predictions for {NEXT_RACE.name} are locked in.
                </div>
              </div>
            ) : (
              <div className="relative border border-[#E10600]/20 p-5 overflow-hidden" style={{ background: "#160500", borderRadius: 2 }}>
                <div className="absolute left-0 inset-y-0 w-[3px] bg-[#E10600]" />
                <div className="pl-2">
                  <div className="font-['Barlow_Condensed'] font-black italic text-white text-base uppercase mb-1">
                    Picks Not Submitted
                  </div>
                  <div className="font-['Inter'] text-[#BBBBBB] text-sm mb-3">
                    Lock in your {NEXT_RACE.name} predictions before race day.
                  </div>
                  <div className="flex items-center gap-2 text-[#E10600] font-['Barlow_Condensed'] font-bold uppercase tracking-widest text-xs">
                    <Flag size={12} />
                    <span>Head to Next Race →</span>
                  </div>
                </div>
              </div>
            )}
          </section>
        </div>
      </div>
    </div>
  );
}

// ─── NEXT RACE PAGE ───────────────────────────────────────────────────────────
function NextRacePage() {
  const [picks, setPicks] = useState<Pick>({ first: null, tenth: null, last: null });
  const [submitted, setSubmitted] = useState(false);
  const [dragDriver, setDragDriver] = useState<Driver | null>(null);
  const [dragOver, setDragOver] = useState<keyof Pick | null>(null);
  const countdown = useCountdown(NEXT_RACE.targetDate);

  const pickedIds = new Set(
    [picks.first?.id, picks.tenth?.id, picks.last?.id].filter(Boolean)
  );

  function handleDrop(slot: keyof Pick) {
    if (!dragDriver) return;
    const prev = picks[slot];
    const newPicks = { ...picks };
    const existingSlot = (Object.keys(picks) as (keyof Pick)[]).find(
      (k) => picks[k]?.id === dragDriver.id
    );
    if (existingSlot) newPicks[existingSlot] = prev;
    newPicks[slot] = dragDriver;
    setPicks(newPicks);
    setDragDriver(null);
    setDragOver(null);
  }

  function clearSlot(slot: keyof Pick) {
    setPicks((p) => ({ ...p, [slot]: null }));
  }

  const canSubmit = picks.first && picks.tenth && picks.last;

  const slots: { key: keyof Pick; label: string; sublabel: string; accentColor: string }[] = [
    { key: "first", label: "1ST PLACE",  sublabel: "Race Winner", accentColor: "#FFD700" },
    { key: "tenth", label: "10TH PLACE", sublabel: "Points Edge",  accentColor: "#E10600" },
    { key: "last",  label: "LAST PLACE", sublabel: "Backmarker",   accentColor: "#555555" },
  ];

  return (
    <div className="min-h-screen pt-[72px]">
      <div className="max-w-7xl mx-auto px-6 py-8">
        {/* Race banner */}
        <div className="relative bg-[#1c1c1c] border border-white/[0.07] px-6 py-4 mb-6 flex items-center gap-6 overflow-hidden"
          style={{ clipPath: "polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0% 100%)" }}>
          <div>
            <div className="font-['JetBrains_Mono'] text-[#E10600] text-[10px] tracking-widest uppercase mb-0.5">Round {NEXT_RACE.round}</div>
            <div className="font-['Barlow_Condensed'] font-black italic text-white text-2xl uppercase">{NEXT_RACE.name}</div>
            <div className="font-['JetBrains_Mono'] text-[#BBBBBB] text-xs">{NEXT_RACE.location} · {NEXT_RACE.date}</div>
          </div>
          <div className="ml-auto flex gap-4">
            {[{ v: countdown.d, l: "DAYS" }, { v: countdown.h, l: "HRS" }, { v: countdown.m, l: "MIN" }].map(({ v, l }) => (
              <div key={l} className="text-center">
                <div className="font-['Barlow_Condensed'] font-black text-2xl text-white leading-none tabular-nums">
                  {String(v).padStart(2, "0")}
                </div>
                <div className="font-['JetBrains_Mono'] text-[#BBBBBB] text-[9px] tracking-widest">{l}</div>
              </div>
            ))}
          </div>
        </div>

        {!submitted ? (
          <>
            {/* Place Picks title */}
            <div className="flex items-center gap-3 mb-5">
              <div className="w-1 h-8 bg-[#E10600]" />
              <h2 className="font-['Barlow_Condensed'] font-black italic text-3xl text-white uppercase tracking-tight">
                Place Picks
              </h2>
            </div>

            {/* Drop zone wrapper with bonus indicator */}
            {(() => {
              const totalHours = countdown.d * 24 + countdown.h;
              const bonus =
                totalHours >= 7 * 24 ? { label: "EARLY BIRD",  mult: "+50%", color: "#22c55e"  } :
                totalHours >= 3 * 24 ? { label: "EARLY",       mult: "+25%", color: "#86efac"  } :
                totalHours >= 24     ? { label: "BONUS",        mult: "+10%", color: "#fbbf24"  } :
                                       { label: "LATE PENALTY", mult: "−50%", color: "#E10600"  };
              return (
                <div
                  className="relative mb-5 border border-white/[0.08] overflow-hidden"
                  style={{ borderRadius: 2, background: "rgba(15,15,15,0.7)" }}
                >
                  {/* Bonus header strip */}
                  <div
                    className="flex items-center justify-between px-5 py-2.5 border-b border-white/[0.07]"
                    style={{ background: "rgba(0,0,0,0.3)" }}
                  >
                    <span className="font-['JetBrains_Mono'] text-[#BBBBBB] text-[10px] tracking-widest uppercase">
                      Submission Bonus
                    </span>
                    <div className="flex items-center gap-2">
                      <span className="font-['JetBrains_Mono'] text-[10px] text-[#BBBBBB] tracking-wider">
                        {totalHours >= 7 * 24 ? `${countdown.d}d ${countdown.h}h remaining` :
                         totalHours >= 24     ? `${countdown.d}d ${countdown.h}h remaining` :
                         totalHours > 0       ? `${countdown.h}h ${countdown.m}m remaining` :
                                                "Race has started"}
                      </span>
                      <div
                        className="font-['Barlow_Condensed'] font-black italic text-base px-3 py-0.5"
                        style={{
                          color: bonus.color,
                          background: bonus.color + "18",
                          border: `1px solid ${bonus.color}44`,
                          borderRadius: 2,
                        }}
                      >
                        {bonus.mult}
                      </div>
                      <span
                        className="font-['JetBrains_Mono'] text-[10px] tracking-widest uppercase"
                        style={{ color: bonus.color }}
                      >
                        {bonus.label.replace(/^[+−]\d+% /, "")}
                      </span>
                    </div>
                  </div>

                  {/* Drop zones inside wrapper */}
                  <div className="flex gap-6 justify-center py-6 px-5">
              {slots.map(({ key, label }) => {
                const driver = picks[key];
                const isOver = dragOver === key;

                
                const w = 120;
                const h = Math.round(w * (600 / 900)); // 3:2 ratio → 2/3 of width
                return (
                  <div key={key} className="flex flex-col items-center gap-2">
                    {/* Slot label — plain white, no accent color */}
                    <div className="font-['Barlow_Condensed'] font-black italic text-white text-sm uppercase tracking-widest">
                      {label}
                    </div>
                    {/* Drop zone — exactly sm card size (120×80) */}
                    <div
                      onDragOver={(e) => { e.preventDefault(); setDragOver(key); }}
                      onDragLeave={() => setDragOver(null)}
                      onDrop={() => handleDrop(key)}
                      style={{ width: 150, height: 100 }}
                    >
                      {driver ? (
                        /* Drag out of slot → clears it and returns driver to grid */
                        <div
                          draggable
                          className="cursor-grab active:cursor-grabbing"
                          onDragStart={(e) => {
                            e.dataTransfer.effectAllowed = "move";
                            setDragDriver(driver);
                            clearSlot(key);
                          }}
                          onDragEnd={() => setDragDriver(null)}
                        >
                          <DriverCard driver={driver} size="md" />
                        </div>
                      ) : (
                        <div
                          className={`w-full h-full flex items-center justify-center font-['JetBrains_Mono'] text-[10px] tracking-widest transition-all duration-150 ${
                            isOver
                              ? "border-2 border-dashed border-[#E10600] text-[#E10600] bg-[#E10600]/[0.05]"
                              : "border-2 border-dashed border-white/40 text-[#BBBBBB]"
                          }`}
                          style={{ borderRadius: 2 }}
                        >
                          {isOver ? "DROP" : "· · ·"}
                        </div>
                      )}
                    </div>
                  </div>
                );
              })}
                  </div>{/* end drop zones flex */}
                </div>
              );
            })()}{/* end bonus wrapper IIFE */}

            {/* Submit */}
            <div className="flex justify-end mb-6">
              <button
                disabled={!canSubmit}
                onClick={() => canSubmit && setSubmitted(true)}
                className={`font-['Barlow_Condensed'] font-black italic uppercase text-lg px-10 py-3 transition-all duration-150 ${
                  canSubmit
                    ? "bg-[#E10600] text-white hover:bg-[#ff0a00] cursor-pointer"
                    : "bg-[#232323] text-[#BBBBBB] cursor-not-allowed"
                }`}
                style={{ clipPath: "polygon(12px 0%,100% 0%,calc(100% - 12px) 100%,0% 100%)" }}
              >
                <Lock size={14} className="inline mr-2 mb-0.5" />
                Lock In Picks
              </button>
            </div>

            {/* Driver grid */}
            <div>
              <div className="font-['Barlow_Condensed'] font-bold uppercase tracking-widest text-sm text-[#BBBBBB] mb-4">
                2026 Driver Grid — drag to predict
              </div>
              <div className="flex flex-wrap gap-3">
                {DRIVERS.map((d) => (
                  <DriverCard
                    key={d.id}
                    driver={d}
                    size="md"
                    draggable
                    picked={pickedIds.has(d.id)}
                    onDragStart={(e) => {
                      e.dataTransfer.effectAllowed = "move";
                      setDragDriver(d);
                    }}
                  />
                ))}
              </div>
            </div>
          </>
        ) : (
          /* ── Submitted state ── */
          <div>
            <div
              className="flex items-center gap-3 mb-6 bg-[#0d1a0d] border border-green-900/40 px-5 py-4"
              style={{ clipPath: "polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0% 100%)" }}
            >
              <CheckCircle size={20} className="text-green-400 flex-shrink-0" />
              <div>
                <div className="font-['Barlow_Condensed'] font-black italic text-white text-lg uppercase">Picks locked in!</div>
                <div className="font-['Inter'] text-green-800 text-sm">Your predictions for the Monaco Grand Prix are confirmed.</div>
              </div>
              <button onClick={() => setSubmitted(false)}
                className="ml-auto font-['JetBrains_Mono'] text-xs text-[#BBBBBB] hover:text-white transition-colors cursor-pointer underline">
                edit
              </button>
            </div>

            {/* My picks */}
            <div className="mb-6">
              <div className="font-['Barlow_Condensed'] font-bold text-sm uppercase tracking-widest text-[#BBBBBB] mb-3">Your Picks</div>
              <div className="grid grid-cols-3 gap-4">
                {slots.map(({ key, label, accentColor }) => {
                  const d = picks[key];
                  if (!d) return null;
                  return (
                    <div key={key} className="relative bg-[#1c1c1c] border border-white/[0.07] overflow-hidden"
                      style={{ clipPath: "polygon(10px 0%,100% 0%,calc(100% - 10px) 100%,0% 100%)" }}>
                      <div className="absolute inset-x-0 top-0 h-[3px]" style={{ background: accentColor }} />
                      <div className="p-4">
                        <div className="font-['JetBrains_Mono'] text-[10px] tracking-widest uppercase mb-3" style={{ color: accentColor }}>{label}</div>
                        <DriverCard driver={d} size="lg" />
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>

            {/* Other players */}
            <div>
              <div className="font-['Barlow_Condensed'] font-bold text-sm uppercase tracking-widest text-[#BBBBBB] mb-3">Other Players&apos; Picks</div>
              <div className="space-y-3">
                {OTHER_PICKS.map((p) => (
                  <div key={p.player} className="relative bg-[#1c1c1c] border border-white/[0.07] overflow-hidden"
                    style={{ clipPath: "polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0% 100%)" }}>
                    <div className="px-5 py-4">
                      <div className="font-['Barlow_Condensed'] font-bold uppercase text-[#BBBBBB] text-sm tracking-wider mb-3">{p.player}</div>
                      <div className="flex gap-4">
                        {[
                          { slot: "1ST",  driver: p.first, color: "#FFD700" },
                          { slot: "10TH", driver: p.tenth, color: "#E10600" },
                          { slot: "LAST", driver: p.last,  color: "#BBBBBB" },
                        ].map(({ slot, driver, color }) => (
                          <div key={slot} className="flex flex-col gap-1.5">
                            <span className="font-['JetBrains_Mono'] text-[10px] tracking-wider" style={{ color }}>{slot}</span>
                            <DriverCard driver={driver} size="sm" />
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

// ─── PAST RACES PAGE ──────────────────────────────────────────────────────────
function PastRacesPage() {
  const [year, setYear] = useState<YearKey>("2026");
  const [yearOpen, setYearOpen] = useState(false);
  const [selectedRound, setSelectedRound] = useState(0);

  const races = PAST_RACES[year];
  const race = races[selectedRound];

  return (
    <div className="min-h-screen pt-[72px]">
      <div className="max-w-7xl mx-auto px-6 py-8">
        <div className="flex items-center gap-4 mb-6">
          <div className="w-1 h-10 bg-[#E10600]" />
          <h1 className="font-['Barlow_Condensed'] font-black italic text-4xl text-white tracking-tight uppercase">
            Past Races
          </h1>
          <div className="relative ml-auto">
            <button
              onClick={() => setYearOpen((o) => !o)}
              className="flex items-center gap-3 bg-[#232323] border border-white/[0.08] px-4 py-2 font-['Barlow_Condensed'] font-bold uppercase tracking-widest text-sm text-white hover:border-white/20 transition-all cursor-pointer"
              style={{ clipPath: "polygon(6px 0%,100% 0%,calc(100% - 6px) 100%,0% 100%)" }}
            >
              {year} Season <ChevronDown size={14} className={`transition-transform ${yearOpen ? "rotate-180" : ""}`} />
            </button>
            {yearOpen && (
              <div className="absolute right-0 top-full mt-1 bg-[#232323] border border-white/[0.08] z-20">
                {(["2026", "2025"] as YearKey[]).map((y) => (
                  <button key={y} onClick={() => { setYear(y); setYearOpen(false); setSelectedRound(0); }}
                    className={`block w-full text-left px-5 py-3 font-['Barlow_Condensed'] font-bold uppercase tracking-widest text-sm cursor-pointer transition-colors ${
                      y === year ? "text-[#E10600] bg-[#E10600]/10" : "text-[#BBBBBB] hover:text-white hover:bg-[#2a2a2a]"
                    }`}>
                    {y} Season
                  </button>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Round tabs */}
        <div className="flex gap-2 flex-wrap mb-6">
          {races.map((r, i) => (
            <button
              key={r.round}
              onClick={() => setSelectedRound(i)}
              className={`font-['Barlow_Condensed'] font-bold uppercase tracking-widest text-xs px-4 py-2 transition-all duration-150 cursor-pointer ${
                selectedRound === i ? "bg-[#E10600] text-white" : "bg-[#232323] text-[#BBBBBB] hover:text-white hover:bg-[#2a2a2a]"
              }`}
              style={{ clipPath: "polygon(6px 0%,100% 0%,calc(100% - 6px) 100%,0% 100%)" }}
            >
              R{r.round} · {r.name.replace(" GP", "")}
            </button>
          ))}
        </div>

        {/* Race banner */}
        <div className="relative bg-[#1c1c1c] border border-white/[0.07] px-6 py-4 mb-6 flex items-center justify-between overflow-hidden"
          style={{ clipPath: "polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0% 100%)" }}>
          <div>
            <div className="font-['JetBrains_Mono'] text-[#E10600] text-[10px] tracking-widest uppercase mb-0.5">Round {race.round}</div>
            <div className="font-['Barlow_Condensed'] font-black italic text-white text-2xl uppercase">{race.name}</div>
            <div className="font-['JetBrains_Mono'] text-[#BBBBBB] text-xs">{race.date}</div>
          </div>
          <div className="flex items-center gap-2">
            <Flag size={14} className="text-[#E10600]" />
            <span className="font-['Barlow_Condensed'] font-bold uppercase text-sm text-[#BBBBBB] tracking-widest">Official Results</span>
          </div>
        </div>

        <div className="grid grid-cols-2 gap-6">
          {/* Predictions */}
          <div>
            <div className="font-['Barlow_Condensed'] font-bold uppercase tracking-widest text-sm text-[#BBBBBB] mb-3 flex items-center gap-2">
              <User size={12} className="text-[#E10600]" /> Player Predictions
            </div>
            <div className="space-y-3">
              {race.picks.map((pick) => (
                <div key={pick.player} className="relative bg-[#1c1c1c] border border-white/[0.07] overflow-hidden"
                  style={{ clipPath: "polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0% 100%)" }}>
                  <div className="px-4 py-3">
                    {/* Player name */}
                    {(() => {
                      const slotDefs = [
                        { slot: "1ST",  name: pick.first,  pos: 1,  pts: 25 },
                        { slot: "10TH", name: pick.tenth,  pos: 10, pts: 10 },
                        { slot: "LAST", name: pick.last,   pos: 20, pts: 5  },
                      ];
                      const bonusMap: Record<string, { mult: number; label: string; date: string; time: string }> = {
                        Slayden: { mult: 1.50, label: "EARLY BIRD", date: "June 2, 2026",  time: "11:23 AM" },
                        Evan:    { mult: 1.25, label: "EARLY",      date: "June 1, 2026",  time: "2:45 PM"  },
                        Cullen:  { mult: 1.10, label: "BONUS",      date: "June 3, 2026",  time: "9:07 AM"  },
                        Marcus:  { mult: 0.50, label: "LATE",       date: "June 2, 2026",  time: "11:58 PM" },
                      };
                      const bonus = bonusMap[pick.player] ?? { mult: 1.0, label: "—", date: race.date, time: "—" };
                      const isNeg = bonus.mult < 1;
                      const scored = slotDefs.map(({ slot, name, pos, pts }) => {
                        const actualPos = race.results.indexOf(name) + 1;
                        const correct =
                          (pos === 1  && actualPos === 1) ||
                          (pos === 10 && actualPos === 10) ||
                          (pos === 20 && actualPos === race.results.length);
                        const earned = correct ? pts : 0;
                        const d = DRIVERS.find((dr) => dr.name === name);
                        return { slot, name, correct, earned, d };
                      });
                      const baseTotal = scored.reduce((sum, s) => sum + s.earned, 0);
                      const boostedTotal = baseTotal > 0 ? Math.round(baseTotal * bonus.mult) : 0;

                      return (
                        <div className="flex gap-4">
                          {/* LEFT — driver cards */}
                          <div className="flex gap-3 flex-col">
                            <div className="font-['Barlow_Condensed'] font-bold uppercase text-[#BBBBBB] text-sm tracking-wider mb-3">{pick.player}</div>
                            <div className="flex gap-3 flex-row">
                              {scored.map(({ slot, name, correct, earned, d }) => {
                                const boosted = earned > 0 ? Math.round(earned * bonus.mult) : 0;
                                return (
                                  <div key={slot} className="flex flex-col gap-1">
                                    <span className="font-['JetBrains_Mono'] text-[10px] tracking-wider text-white">{slot}</span>
                                    {d ? (
                                      <DriverCard driver={d} size="sm" correct={correct} />
                                    ) : (
                                      <div className="w-[120px] h-[80px] bg-[#2a2a2a] flex items-center justify-center font-['Inter'] text-[10px] text-[#BBBBBB]" style={{ borderRadius: 2 }}>
                                        {name}
                                      </div>
                                    )}
                                    {earned > 0 ? (
                                      <div className="flex flex-col items-center gap-0.5 mt-0.5">
                                        <div className="flex items-baseline gap-1 font-['JetBrains_Mono'] tabular-nums">
                                          <span className="text-[10px] text-green-400">+{earned}</span>
                                          <span className="text-[7px] text-[#BBBBBB] tracking-wider">PTS</span>
                                          <span className={`text-[9px] font-bold ${isNeg ? "text-red-400" : "text-yellow-400"}`}>{isNeg ? "-50%" : `+${Math.round((bonus.mult - 1) * 100)}%`}</span>
                                        </div>
                                        <div className="flex items-baseline font-['JetBrains_Mono'] tabular-nums">
                                          <span className="text-[11px] font-bold text-green-300">+{boosted}</span>
                                          <span className="text-[7px] text-[#BBBBBB] tracking-wider pl-1">PTS</span>
                                        </div>
                                      </div>
                                    ) : (
                                      <div className="flex items-baseline justify-center font-['JetBrains_Mono'] tabular-nums mt-0.5">
                                        <span className="text-[10px] text-[#BBBBBB]">0</span>
                                        <span className="text-[7px] text-[#BBBBBB] tracking-wider pl-1">PTS</span>
                                      </div>
                                    )}
                                  </div>
                                );
                              })}
                            </div>
                          </div>

                          {/* RIGHT — submission info + round total */}
                          <div className="flex flex-col justify-between flex-1 pl-3 border-l border-white/[0.06]">
                            <div className="flex flex-col gap-1">
                              <span className="font-['JetBrains_Mono'] text-[10px] text-[#BBBBBB] tracking-wider uppercase">Submitted on:</span>
                              <span className="font-['JetBrains_Mono'] text-[11px] text-white tracking-wide">{bonus.date}</span>
                              <span className="font-['JetBrains_Mono'] text-[11px] text-white tracking-wide">{bonus.time}</span>
                              <span
                                className={`font-['Barlow_Condensed'] font-black italic text-sm uppercase tracking-widest ${isNeg ? "text-red-400" : "text-yellow-400"}`}
                              >
                                {bonus.label}
                              </span>
                            </div>
                            {/* Round total anchored to bottom */}
                            <div className="flex flex-col justify-between items-baseline gap-1.5 mt-2">
                              <span className="font-['JetBrains_Mono'] text-[10px] text-[#BBBBBB] tracking-wider">ROUND TOTAL:</span>
                              <div>
                                <span className={`font-['Barlow_Condensed'] font-black italic text-base leading-none ${boostedTotal > 0 ? "text-[#E10600]" : "text-[#BBBBBB]"}`}>
                                  {boostedTotal > 0 ? `+${boostedTotal}` : "0"}
                                </span>
                                <span className="text-[7px] text-[#BBBBBB] font-['JetBrains_Mono'] tracking-wider pl-1">PTS</span>
                              </div>
                            </div>
                          </div>
                        </div>
                      );
                    })()}
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Official results */}
          <div>
            <div className="font-['Barlow_Condensed'] font-bold uppercase tracking-widest text-sm text-[#BBBBBB] mb-3 flex items-center gap-2">
              <Trophy size={12} className="text-[#E10600]" /> Official Classification
            </div>
            <div className="space-y-[2px]">
              {race.results.map((name, i) => {
                const pos = i + 1;
                const d = DRIVERS.find((dr) => dr.name === name);
                const anyPicked = race.picks.some(
                  (p) =>
                    (pos === 1 && p.first === name) ||
                    (pos === 10 && p.tenth === name) ||
                    (pos === race.results.length && p.last === name)
                );
                const isKey = pos === 1 || pos === 10 || pos === race.results.length;
                return (
                  <div key={name}
                    className={`flex items-center gap-3 px-3 py-2 ${isKey ? "bg-[#252525]" : "bg-[#181818]"}`}
                    style={{ clipPath: "polygon(6px 0%,100% 0%,calc(100% - 6px) 100%,0% 100%)" }}>
                    <span className={"font-['Barlow_Condensed'] font-black italic text-lg w-7 text-right leading-none flex-shrink-0 text-[#BBBBBB]"}>{pos}</span>
                    {d && <div className="w-0.5 h-5 flex-shrink-0" style={{ background: d.color }} />}
                    <span className="font-['Barlow_Condensed'] font-bold uppercase text-sm flex-1 text-[#BBBBBB]">{name}</span>
                    {d && <span className="font-['Inter'] text-[#BBBBBB] text-xs">{d.team}</span>}
                    {/* {anyPicked && <span className="font-['JetBrains_Mono'] text-[9px] text-[#E10600] tracking-wider ml-1">PICKED</span>} */}
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── RULES PAGE ───────────────────────────────────────────────────────────────
function RulesPage() {
  const sections = [
    {
      title: "Overview",
      content: [
        "F1 Pick'em is a season-long prediction game played alongside the Formula 1 calendar.",
        "Each race weekend, participants submit exactly three picks before the race start.",
        "Points accumulate over the season to determine the overall champion.",
      ],
    },
    {
      title: "How to Submit Picks",
      content: [
        "Navigate to the Next Race page before qualifying ends.",
        "Drag driver cards from the grid into the three prediction slots.",
        "Slot 1: Your predicted race winner (1st Place).",
        "Slot 2: Your predicted driver to finish 10th (Points Edge).",
        "Slot 3: Your predicted last-place finisher (Backmarker).",
        "Click Lock In Picks — predictions cannot be changed after submission.",
        "The deadline is the official race start time (formation lap begins).",
      ],
    },
    {
      title: "Scoring System",
      content: [
        "Correct 1st Place prediction: +25 points",
        "Correct 10th Place prediction: +10 points",
        "Correct Last Place prediction: +5 points",
        "Bonus — all three correct in one race: +15 bonus points",
        "No points are awarded for partial matches (e.g. picking 2nd instead of 1st).",
        "DNS / DNQ drivers are excluded from scoring; picks against them score zero.",
      ],
    },
    {
      title: "Tiebreakers",
      content: [
        "In the event of equal season points, the player with more 1st-place correct picks wins.",
        "Secondary tiebreaker: most correct 10th-place picks.",
        "Tertiary tiebreaker: most correct last-place picks.",
        "If still tied, earliest submission timestamp wins.",
      ],
    },
    {
      title: "Fairness & Conduct",
      content: [
        "All picks must be submitted independently — sharing answers before lockout is unsportsmanlike.",
        "Race results are taken from the official FIA classification after stewards' decisions.",
        "Appeals about scoring must be raised within 48 hours of results publication.",
        "The commissioner's ruling on disputed picks is final.",
      ],
    },
  ];

  return (
    <div className="min-h-screen pt-[72px]">
      <div className="max-w-3xl mx-auto px-6 py-8">
        <div className="flex items-center gap-4 mb-8">
          <div className="w-1 h-10 bg-[#E10600]" />
          <h1 className="font-['Barlow_Condensed'] font-black italic text-4xl text-white tracking-tight uppercase">
            Rules & Scoring
          </h1>
        </div>
        <div className="space-y-4">
          {sections.map((s) => (
            <div key={s.title} className="relative bg-[#1c1c1c] border border-white/[0.07] overflow-hidden">
              <div className="absolute left-0 inset-y-0 w-[3px] bg-[#E10600]" />
              <div className="px-6 py-5">
                <h2 className="font-['Barlow_Condensed'] font-black italic text-[#E10600] text-xl uppercase tracking-wide mb-3">
                  {s.title}
                </h2>
                <ul className="space-y-2">
                  {s.content.map((line, i) => (
                    <li key={i} className="flex items-start gap-3">
                      <span className="font-['JetBrains_Mono'] text-[#E10600] text-xs mt-1 flex-shrink-0">›</span>
                      <span className="font-['Inter'] text-[#BBBBBB] text-sm leading-relaxed">{line}</span>
                    </li>
                  ))}
                </ul>
              </div>
            </div>
          ))}

          {/* Quick ref */}
          <div className="relative bg-[#100000] border border-[#E10600]/25 overflow-hidden">
            <div className="px-6 py-5">
              <h2 className="font-['Barlow_Condensed'] font-black italic text-white text-xl uppercase tracking-wide mb-4">
                Quick Reference
              </h2>
              <div className="grid grid-cols-2 gap-3">
                {[
                  { label: "1st Place correct",  pts: "+25" },
                  { label: "10th Place correct", pts: "+10" },
                  { label: "Last Place correct", pts: "+5"  },
                  { label: "All three correct",  pts: "+15 bonus" },
                ].map(({ label, pts }) => (
                  <div key={label}
                    className="flex items-center justify-between bg-[#1c1c1c] px-4 py-3"
                    style={{ clipPath: "polygon(6px 0%,100% 0%,calc(100% - 6px) 100%,0% 100%)" }}>
                    <span className="font-['Inter'] text-[#BBBBBB] text-sm">{label}</span>
                    <span className="font-['Barlow_Condensed'] font-black italic text-[#E10600] text-lg">{pts}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Root ─────────────────────────────────────────────────────────────────────
export default function App() {
  const [page, setPage] = useState<Page>("home");
  return (
    <>
      <FixedBackground />
      <div className="min-h-screen text-foreground" style={{ fontFamily: "Inter, sans-serif" }}>
        <Nav page={page} setPage={setPage} />
        {page === "home"       && <HomePage />}
        {page === "next-race"  && <NextRacePage />}
        {page === "past-races" && <PastRacesPage />}
        {page === "rules"      && <RulesPage />}
      </div>
    </>
  );
}
