/**
 * The school's dates, for every script on a signed-in page (layouts/page_bottom.php loads this file first).
 * PHP decides them (layouts/page_top.php prints them from config/white_label/country.php), so a date written by a
 * script looks exactly like one written by PHP, and "today" is the school's today, never the computer's clock.
 *
 *   schoolToday                    → 'YYYY-MM-DD'
 *   formatSchoolDate(iso, style)   → the date in one of the school's styles: 'date' (Nov 18, 2023), 'day' (October 19),
 *                                    'full' (October 19, 2026), 'long' (Monday, October 19, 2026)
 */
(function ()
{
  'use strict';

  const school = JSON.parse(document.getElementById('j-school-dates').textContent);

  // Written like PHP's date(): d 05 · j 5 · l Monday · D Mon · m 10 · n 10 · F October · M Oct · Y 2026 · y 26
  function formatSchoolDate(iso, style)
  {
    const [year, month, day] = iso.split('-').map(Number);
    const weekday = school.weekdays[(new Date(year, month - 1, day).getDay() + 6) % 7];   // the list starts on Monday
    const monthName = school.months[month - 1];
    const parts = {
      d: String(day).padStart(2, '0'), j: day, l: weekday, D: weekday.slice(0, 3),
      m: String(month).padStart(2, '0'), n: month, F: monthName, M: monthName.slice(0, 3),
      Y: year, y: String(year).slice(2)
    };

    return school.formats[style].replace(/[djlDmnFMYy]/g, (letter) => parts[letter]);
  }

  window.schoolToday = school.today;
  window.formatSchoolDate = formatSchoolDate;
})();
