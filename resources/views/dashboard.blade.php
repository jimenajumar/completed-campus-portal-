@extends('layouts.app')
@section('title', 'Dashboard - ACES')
@section('content')

<!-- WELCOME BANNER -->
<section class="relative flex min-h-[390px] items-center overflow-hidden rounded-2xl bg-[#102B59] shadow-xl">
    <div class="absolute inset-0">
        <img src="{{ asset('images/aces building.png') }}"
             alt="ACES Tagum College Campus"
             class="h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-[#102B59]/95 via-[#102B59]/75 to-[#102B59]/35"></div>
    </div>

    <div class="relative z-10 max-w-3xl px-8 py-16 text-white md:px-14">
        <p class="mb-3 text-sm font-semibold uppercase tracking-[0.25em] text-[#D6B45B]">
            ACES Tagum College Inc.
        </p>

        <h1 class="text-4xl font-extrabold leading-tight md:text-6xl">
            WELCOME BACK
        </h1>

        <div class="my-5 h-1 w-20 rounded-full bg-[#D6B45B]"></div>

        <p class="max-w-xl text-lg leading-relaxed text-slate-100 md:text-xl">
            Your Student Journey Starts Here.
        </p>


<!-- inspiring QUOTE -->

    <div class="mb-4 flex items-center gap-2">
        <span class="text-2xl">✨</span>
        <h2 class="text-lg font-bold">Daily Inspiration</h2>
    </div>

    <div id="quoteLoading" class="flex items-center gap-3">
        <div class="h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-white"></div>
        <p class="text-sm text-white/80">Finding today's inspiration...</p>
    </div>

    <div id="quoteContent" class="hidden">
        <p id="dailyQuote" class="text-lg italic leading-relaxed"></p>

        <p id="quoteAuthor" class="mt-4 text-sm font-semibold text-[#D6B45B]"></p>
    </div>
</section>



<!-- CAMPUS HIGHLIGHTS SLIDER -->
<section class="mt-6 overflow-hidden rounded-2xl border border-white/20
                bg-[#102B59] p-6 text-white shadow-xl backdrop-blur-md">

    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-bold">Campus Highlights</h2>
        <span class="text-xs text-[#E8D9AE]">Latest updates</span>
    </div>

    <div class="slider-viewport min-h-40">
        <div id="campusSlider" class="slider-track">

            <!-- Slide 1: Events -->
            <div class="campus-slide rounded-xl bg-[#173F91] p-6">
                <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-blue-100">
                    Latest Events
                </p>

                <div id="latestEvent">
                    <h3 class="text-2xl font-bold">Loading events...</h3>
                    <p class="mt-2 text-sm text-blue-50">
                        Please wait while we load campus events.
                    </p>
                </div>
            </div>

            <!-- Slide 2: Weather -->
            <div class="campus-slide rounded-xl bg-[#1B4778] p-6">
                <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-blue-100">
                    Today's Weather — Tagum City
                </p>

                <div id="campusWeather">
                    <h3 class="text-2xl font-bold">Loading weather...</h3>
                    <p class="mt-2 text-sm text-blue-50">
                        Getting current weather conditions.
                    </p>
                </div>
            </div>

            <!-- Slide 3: Announcements -->
            <div class="campus-slide rounded-xl bg-[#173F91] p-6">
                <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-blue-100">
                    Announcements
                </p>

                <div id="latestAnnouncement">
                    <h3 class="text-2xl font-bold">Loading announcements...</h3>
                    <p class="mt-2 text-sm text-blue-50">
                        Please wait while we load the latest announcement.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Slider indicators -->
    <div class="mt-4 flex justify-center gap-2">
        <button class="slider-dot h-2.5 w-2.5 rounded-full bg-white"
                onclick="showCampusSlide(0)"
                aria-label="Show events"></button>

        <button class="slider-dot h-2.5 w-2.5 rounded-full bg-white/40"
                onclick="showCampusSlide(1)"
                aria-label="Show weather"></button>

        <button class="slider-dot h-2.5 w-2.5 rounded-full bg-white/40"
                onclick="showCampusSlide(2)"
                aria-label="Show announcements"></button>
    </div>
</section>


<!-- SCHOOL CALENDAR -->
<section class="mt-6 rounded-2xl border border-slate-200
                bg-white p-6 text-slate-800 shadow-lg">

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm font-medium text-[#173F91]">
                ACES Tagum College
            </p>
            <h2 class="text-2xl font-bold">School Calendar</h2>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="changeMonth(-1)"
                    class="rounded-lg bg-[#E8EEF8] px-3 py-2 font-bold text-[#102B59] hover:bg-[#D7E2F3]">
                &lt;
            </button>

            <h3 id="calendarMonth" class="min-w-32 text-center font-bold"></h3>

            <button onclick="changeMonth(1)"
                    class="rounded-lg bg-[#E8EEF8] px-3 py-2 font-bold text-[#102B59] hover:bg-[#D7E2F3]">
                &gt;
            </button>
        </div>
    </div>

    <!-- Calendar grid -->
    <div class="grid grid-cols-7 gap-2 text-center">
        <div class="text-sm font-bold text-slate-500">Sun</div>
        <div class="text-sm font-bold text-slate-500">Mon</div>
        <div class="text-sm font-bold text-slate-500">Tue</div>
        <div class="text-sm font-bold text-slate-500">Wed</div>
        <div class="text-sm font-bold text-slate-500">Thu</div>
        <div class="text-sm font-bold text-slate-500">Fri</div>
        <div class="text-sm font-bold text-slate-500">Sat</div>
    </div>

    <div id="calendarDays" class="mt-2 grid grid-cols-7 gap-2"></div>

    <!-- Calendar legend -->
    <div class="mt-5 flex flex-wrap gap-4 text-xs text-slate-600">
        <span>
            <span class="mr-1 inline-block h-3 w-3 rounded-full bg-rose-500"></span>
            Holiday
        </span>

        <span>
            <span class="mr-1 inline-block h-3 w-3 rounded-full bg-[#173F91]"></span>
            School Event
        </span>

        <span>
            <span class="mr-1 inline-block h-3 w-3 rounded-full bg-emerald-500"></span>
            Today
        </span>
    </div>

    <!-- Selected date details -->
    <div class="mt-5 rounded-xl bg-[#F3F5F9] p-4">
        <h3 id="selectedDate" class="font-bold text-[#102B59]">
            Select a date
        </h3>

        <div id="dateEvents" class="mt-2 text-sm text-slate-600">
            Click a date to see its events and holidays.
        </div>
    </div>
</section>


<script>

// ========================================
// CAMPUS HIGHLIGHTS SLIDER
// ========================================

let currentCampusSlide = 0;

const campusSlider = document.getElementById("campusSlider");
const campusSlides = document.querySelectorAll(".campus-slide");
const campusDots = document.querySelectorAll(".slider-dot");

function showCampusSlide(index) {
    if (!campusSlider || campusSlides.length === 0) return;

    currentCampusSlide = index;

    // Slide horizontally
    campusSlider.style.transform =
        `translateX(-${index * 100}%)`;

    // Update navigation dots
    campusDots.forEach((dot, i) => {
        dot.classList.toggle("bg-white", i === index);
        dot.classList.toggle("bg-white/40", i !== index);
    });
}

// Automatically slide every 5 seconds
setInterval(() => {
    showCampusSlide(
        (currentCampusSlide + 1) % campusSlides.length
    );
}, 5000);


// ========================================
// FETCH EVENTS FROM LARAVEL API
// ========================================

async function loadCampusEvents() {
    const token = sessionStorage.getItem('campus_token');

    if (!token) {
        console.error('Login token not found.');
        return;
    }

    try {
        const response = await fetch('/api/events', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (!response.ok) {
            console.error('Events request failed:', response.status);
            return;
        }

        const events = await response.json();

        console.log('Campus events:', events);

        const eventContainer = document.getElementById('latestEvent');

        if (events.length > 0) {
            const event = events[0];

            eventContainer.innerHTML = `
                <h3 class="text-2xl font-bold">${event.title}</h3>

                <p class="mt-2 text-sm text-blue-50">
                    ${event.description || 'No description available.'}
                </p>

                <p class="mt-3 text-xs text-blue-100">
                    Venue: ${event.venue || 'To be announced'}
                </p>

                <p class="mt-1 text-xs text-blue-100">
                    Date: ${new Date(event.starts_at).toLocaleDateString()}
                </p>
            `;

        } else {
            eventContainer.innerHTML = `
                <h3 class="text-2xl font-bold">No upcoming events</h3>

                <p class="mt-2 text-sm text-blue-50">
                    Check back for new campus activities.
                </p>
            `;
        }

    } catch (error) {
        console.error('Error loading events:', error);
    }
}


// ========================================
// FETCH ANNOUNCEMENTS FROM LARAVEL API
// ========================================

async function loadCampusAnnouncements() {
    const announcementContainer = document.getElementById('latestAnnouncement');
    const token = sessionStorage.getItem('campus_token');

    try {
        const response = await fetch('/api/announcements', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (!response.ok) {
            throw new Error('Failed to load announcements.');
        }

        const announcements = await response.json();

        if (announcements.length > 0) {
            const announcement = announcements[0];

            announcementContainer.innerHTML = `
                <h3 class="text-2xl font-bold">
                    ${announcement.title}
                </h3>

                <p class="mt-2 text-sm text-blue-50">
                    ${announcement.content}
                </p>

                <p class="mt-3 text-xs text-blue-100">
                    Category: ${announcement.category || 'General'}
                </p>
            `;

        } else {
            announcementContainer.innerHTML = `
                <h3 class="text-2xl font-bold">No announcements yet.</h3>

                <p class="mt-2 text-sm text-blue-50">
                    Check back later for school updates.
                </p>
            `;
        }

    } catch (error) {
        announcementContainer.innerHTML = `
            <h3 class="text-2xl font-bold">Unable to load announcements.</h3>

            <p class="mt-2 text-sm text-blue-50">
                Please try again later.
            </p>
        `;

        console.error(error);
    }
}


// ========================================
// FETCH WEATHER FROM OPEN-METEO
// ========================================


async function loadCampusWeather() {
    const container = document.getElementById('campusWeather');

    try {
        const response = await fetch('/api/weather', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${sessionStorage.getItem('campus_token')}`
            }
        });

        if (!response.ok) {
            throw new Error('Weather request failed.');
        }

        const result = await response.json();

        if (!result.success || !result.data?.current) {
            throw new Error('Weather data is unavailable.');
        }

        const weather = result.data.current;

        const weatherDescriptions = {
            0: 'Clear sky',
            1: 'Mainly clear',
            2: 'Partly cloudy',
            3: 'Overcast',
            45: 'Foggy',
            48: 'Foggy',
            51: 'Light drizzle',
            53: 'Drizzle',
            55: 'Heavy drizzle',
            61: 'Light rain',
            63: 'Moderate rain',
            65: 'Heavy rain',
            80: 'Rain showers',
            81: 'Rain showers',
            82: 'Heavy rain showers',
            95: 'Thunderstorm'
        };

        const description =
            weatherDescriptions[weather.weather_code] ||
            'Weather conditions updated';

        container.innerHTML = `
            <h3 class="text-4xl font-bold">
                ${weather.temperature_2m}°C
            </h3>

            <p class="mt-1 text-lg text-blue-50">
                ${description}
            </p>

            <div class="mt-3 flex flex-wrap gap-4 text-sm text-blue-50">
                <span>Feels like: ${weather.apparent_temperature}°C</span>
                <span>Humidity: ${weather.relative_humidity_2m}%</span>
                <span>Wind: ${weather.wind_speed_10m} km/h</span>
            </div>

            <p class="mt-3 text-xs text-blue-100">
                Updated: ${new Date(weather.time).toLocaleTimeString()}
            </p>
        `;

    } catch (error) {
        container.innerHTML = `
            <h3 class="text-xl font-bold">Weather unavailable</h3>

            <p class="mt-2 text-sm text-blue-50">
                Please try again later.
            </p>
        `;

        console.error('Weather error:', error);
    }
}

// Load campus weather
loadCampusWeather();

loadCampusAnnouncements();
loadCampusEvents();



// school calendar

let calendarDate = new Date();

let selectedCalendarDate = new Date(
    calendarDate.getFullYear(),
    calendarDate.getMonth(),
    calendarDate.getDate()
);

// Stores campus events and holidays
const calendarEvents = {};

// Tracks years whose holidays have loaded
const loadedHolidayYears = new Set();

// format date

function formatDateKey(year, month, day) {
    return year + "-" +
        String(month + 1).padStart(2, "0") + "-" +
        String(day).padStart(2, "0");
}


// render calendar

function renderCalendar() {
    const year = calendarDate.getFullYear();
    const month = calendarDate.getMonth();

    document.getElementById("calendarMonth").textContent =
        calendarDate.toLocaleDateString("en-US", {
            month: "long",
            year: "numeric"
        });

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const calendarDays = document.getElementById("calendarDays");
    calendarDays.innerHTML = "";

    // Empty spaces before first day
    for (let i = 0; i < firstDay; i++) {
        calendarDays.innerHTML +=
            '<div class="min-h-12"></div>';
    }

    // Calendar dates
    for (let day = 1; day <= daysInMonth; day++) {

        const dateKey = formatDateKey(year, month, day);
        const events = calendarEvents[dateKey] || [];

        const today = new Date();

        const isToday =
            year === today.getFullYear() &&
            month === today.getMonth() &&
            day === today.getDate();

        const isSelected =
            selectedCalendarDate.getFullYear() === year &&
            selectedCalendarDate.getMonth() === month &&
            selectedCalendarDate.getDate() === day;

        let marker = "";

        if (events.some(e => e.type === "holiday")) {
            marker =
                '<span class="mx-auto mt-1 block h-2 w-2 rounded-full bg-rose-500"></span>';
        } else if (events.some(e => e.type === "event")) {
            marker =
                '<span class="mx-auto mt-1 block h-2 w-2 rounded-full bg-[#173F91]"></span>';
        }

        let button = document.createElement("button");

        button.className =
            "min-h-12 rounded-lg p-1 text-sm transition hover:bg-[#E8EEF8] " +
            (isToday
                ? "bg-emerald-100 font-bold text-emerald-800 "
                : "bg-slate-50 ") +
            (isSelected ? "ring-2 ring-[#173F91] " : "");

        button.innerHTML = '<span>' + day + '</span>' + marker;

        button.addEventListener("click", function () {
            selectedCalendarDate = new Date(year, month, day);
            renderCalendar();
            showDateEvents(dateKey);
        });

        calendarDays.appendChild(button);
    }

    showDateEvents(formatDateKey(
        selectedCalendarDate.getFullYear(),
        selectedCalendarDate.getMonth(),
        selectedCalendarDate.getDate()
    ));
}


// show selected dates

function showDateEvents(dateKey) {
    const date = new Date(dateKey + "T00:00:00");

    document.getElementById("selectedDate").textContent =
        date.toLocaleDateString("en-US", {
            month: "long",
            day: "numeric",
            year: "numeric"
        });

    const events = calendarEvents[dateKey] || [];
    const details = document.getElementById("dateEvents");

    details.innerHTML = "";

    if (events.length === 0) {
        details.textContent =
            "No scheduled events or holidays for this date.";
        return;
    }

    events.forEach(event => {
        const card = document.createElement("div");

        card.className =
            "mb-3 rounded-lg border border-gray-200 bg-white p-3";

        const title = document.createElement("h4");

        title.className = "font-bold text-blue-950";

        title.textContent =
            (event.type === "holiday" ? "🎉 " : "📅 ") + event.name;

        card.appendChild(title);

        if (event.description) {
            const description = document.createElement("p");

            description.className = "mt-2 text-sm text-gray-600";
            description.textContent = event.description;

            card.appendChild(description);
        }

        if (event.venue) {
            const venue = document.createElement("p");

            venue.className = "mt-1 text-sm text-gray-500";
            venue.textContent = "📍 Venue: " + event.venue;

            card.appendChild(venue);
        }

        if (event.starts_at) {
            const time = document.createElement("p");

            time.className = "mt-1 text-sm text-gray-500";

            time.textContent =
                "🕒 Starts: " +
                new Date(event.starts_at).toLocaleTimeString([], {
                    hour: "2-digit",
                    minute: "2-digit"
                });

            card.appendChild(time);
        }

        if (event.ends_at) {
            const endTime = document.createElement("p");

            endTime.className = "mt-1 text-sm text-gray-500";

            endTime.textContent =
                "🕒 Ends: " +
                new Date(event.ends_at).toLocaleTimeString([], {
                    hour: "2-digit",
                    minute: "2-digit"
                });

            card.appendChild(endTime);
        }

        details.appendChild(card);
    });
}


// change calendar month

function changeMonth(direction) {
    const previousYear = calendarDate.getFullYear();

    calendarDate.setMonth(calendarDate.getMonth() + direction);

    renderCalendar();

    if (calendarDate.getFullYear() !== previousYear) {
        loadPhilippineHolidays();
    }
}


// load campus events

async function loadCalendarEvents() {
    const token = sessionStorage.getItem("campus_token");

    if (!token) {
        console.error("Login token not found.");
        renderCalendar();
        return;
    }

    try {
        const response = await fetch("/api/events", {
            headers: {
                "Accept": "application/json",
                "Authorization": `Bearer ${token}`
            }
        });

        if (!response.ok) {
            throw new Error("Unable to load calendar events.");
        }

        const events = await response.json();

        events.forEach(event => {
            if (!event.starts_at) return;

            const dateKey = event.starts_at.substring(0, 10);

            if (!calendarEvents[dateKey]) {
                calendarEvents[dateKey] = [];
            }

            calendarEvents[dateKey].push({
                name: event.title,
                type: "event",
                venue: event.venue,
                description: event.description,
                starts_at: event.starts_at,
                ends_at: event.ends_at
            });
        });

    } catch (error) {
        console.error("Calendar error:", error);
    }

    renderCalendar();
}


// load PH holidays

async function loadPhilippineHolidays() {
    const token = sessionStorage.getItem("campus_token");

    if (!token) return;

    const year = calendarDate.getFullYear();

    if (loadedHolidayYears.has(year)) return;

    try {
        const response = await fetch(
            "/api/holidays?year=" + year,
            {
                headers: {
                    "Accept": "application/json",
                    "Authorization": `Bearer ${token}`
                }
            }
        );

        if (!response.ok) {
            throw new Error("Unable to load Philippine holidays.");
        }

        const result = await response.json();

        if (!result.success || !Array.isArray(result.data)) {
            throw new Error("Invalid holiday data.");
        }

        result.data.forEach(holiday => {
            const dateKey = holiday.date;

            if (!calendarEvents[dateKey]) {
                calendarEvents[dateKey] = [];
            }

            calendarEvents[dateKey].push({
                name: holiday.localName || holiday.name,
                type: "holiday"
            });
        });

        loadedHolidayYears.add(year);

        renderCalendar();

    } catch (error) {
        console.error("Holiday API error:", error);
    }
}

// initial load

loadCalendarEvents();
loadPhilippineHolidays();



async function loadDailyQuote() {
    const token = sessionStorage.getItem('campus_token');

    const loading = document.getElementById('quoteLoading');
    const content = document.getElementById('quoteContent');

    loading.classList.remove('hidden');
    content.classList.add('hidden');

    try {
        const response = await fetch('/api/daily-quote', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (!response.ok) {
            throw new Error('Unable to load daily quote.');
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error('Invalid quote response.');
        }

        document.getElementById('dailyQuote').textContent =
            '"' + result.data.quote + '"';

        document.getElementById('quoteAuthor').textContent =
            '— ' + result.data.author;

        content.classList.remove('hidden');

    } catch (error) {
        console.error('Quote API error:', error);

        document.getElementById('dailyQuote').textContent =
            'Keep learning, keep growing, and keep believing in yourself.';

        document.getElementById('quoteAuthor').textContent =
            '— ACES Campus Portal';

        content.classList.remove('hidden');

    } finally {
        loading.classList.add('hidden');
    }
}

loadDailyQuote();


</script>
@endsection
