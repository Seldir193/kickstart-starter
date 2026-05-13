// assets/js/offers-directory.js
(function () {
  "use strict";

  const C = window.KSOffersDirectoryCore;
  if (!C) return;
  const $ = C.$;
  const {
    normalizeCity,
    cityMatches,
    cityFromOffer,
    buildUrl,
    fillLocations,
    groupByLocation,
    renderList,
    setCounters,
    setAgeHeadline,
    matchesType,
    getProgramGroupFromKey,
    filterByProgram,
    getHolidayLabel,
    getHolidaySeasonKey,
    getHolidayWeekKey,
    fillHolidayWeeksSelect,
    normDay,
    offerHasDay,
  } = C;

  /* ===== main ===== */
  document.addEventListener("DOMContentLoaded", async () => {
    const root = $("#ksDir");
    if (!root) return;

    window.KSOffersDirectoryUI?.enhanceFilterSelects?.(root);

    const daySel = $("#ksFilterDay", root);
    const ageSel = $("#ksFilterAge", root);
    const locSel = $("#ksFilterLoc", root);
    const listEl = $("#ksDirList", root);
    const ageTitle = $("[data-age-title]", root);

    const resetBtn = $("[data-reset-filters]", root);
    const secondaryOfferCount = $("[data-count-offers-secondary]", root);

    function resetSelect(sel) {
      if (!sel) return;
      sel.value = "";
      sel.dispatchEvent(new Event("change", { bubbles: true }));
    }

    const holidaySeasonSel = $("#ksFilterHolidaySeason", root);
    const holidayWeekSel = $("#ksFilterHolidayWeek", root);

    const TYPE = root.dataset.type || "";
    const API = root.dataset.api || "http://localhost:5000";
    const CITY = root.dataset.city || "";

    const CATEGORY = root.dataset.category || "";
    const SUBTYPE = root.dataset.subtype || "";

    const catLower = (CATEGORY || "").toLowerCase();
    const isHolidayPage =
      catLower === "holiday" ||
      catLower === "holidayprograms" ||
      getProgramGroupFromKey(SUBTYPE || TYPE) === "camp";

    const normProgKey = (SUBTYPE || TYPE || "").toLowerCase();
    const isPowertrainingPage =
      normProgKey.includes("powertraining") ||
      normProgKey.includes("athletictraining") ||
      normProgKey.includes("athletiktraining");

    const mapManager = window.KSOffersDirectoryMap?.create(root) || null;

    let items = [];
    let filtered = [];

    const url = buildUrl(`${API}/api/offers`, {
      type: TYPE || undefined,
      category: CATEGORY || undefined,
      sub_type: SUBTYPE || undefined,
      limit: 500,
    });

    try {
      const data = await fetch(url).then((r) => r.json());
      items = Array.isArray(data?.items)
        ? data.items
        : Array.isArray(data)
          ? data
          : [];

      const currentProgramKey = (SUBTYPE || TYPE || "").trim();
      items = filterByProgram(items, currentProgramKey);

      fillLocations(locSel, items);

      if (locSel) {
        locSel.dispatchEvent(new Event("change", { bubbles: true }));
      }

      if (CITY && locSel) {
        const opt = Array.from(locSel.options).find(
          (o) => normalizeCity(o.value) === normalizeCity(CITY),
        );
        if (opt) locSel.value = opt.value;
      }

      mapManager?.set_all_points?.(items);

      if (isHolidayPage) {
        fillHolidayWeeksSelect(
          holidayWeekSel,
          items,
          holidaySeasonSel ? holidaySeasonSel.value || "" : "",
        );
        const weekSel = $("#ksFilterHolidayWeek", root);
        if (weekSel)
          weekSel.dispatchEvent(new Event("change", { bubbles: true }));
      }
    } catch {
      if (listEl)
        listEl.innerHTML =
          '<li><div class="card">Keine Angebote gefunden.</div></li>';
    }

    async function apply() {
      const dayRaw = String(daySel?.value || "").trim();
      const day =
        isHolidayPage ||
        !dayRaw ||
        normalizeCity(dayRaw) === normalizeCity("Alle Tage")
          ? ""
          : normDay(dayRaw);

      const age =
        isHolidayPage || !ageSel || ageSel.value === ""
          ? NaN
          : parseInt(ageSel.value, 10);
      const loc = (locSel?.value || "").trim();

      const seasonVal =
        isHolidayPage && holidaySeasonSel ? holidaySeasonSel.value : "";
      const weekVal =
        isHolidayPage && holidayWeekSel ? holidayWeekSel.value : "";

      filtered = items.filter((o) => {
        if (TYPE && !matchesType(o, TYPE)) return false;
        if (CATEGORY && o.category !== CATEGORY) return false;
        if (SUBTYPE && o.sub_type !== SUBTYPE) return false;

        if (isHolidayPage) {
          const label = getHolidayLabel(o);
          const season = getHolidaySeasonKey(label);
          const wKey = getHolidayWeekKey(o);
          if (seasonVal && seasonVal !== season) return false;
          if (weekVal && weekVal !== wKey) return false;
        } else {
          if (day && !offerHasDay(o, day)) return false;
          if (!isNaN(age)) {
            const f = Number(o.ageFrom ?? 0);
            const t = Number(o.ageTo ?? 99);
            if (!(age >= f && age <= t)) return false;
          }
        }

        const offerCity = cityFromOffer(o);
        if (loc && !cityMatches(offerCity, loc)) return false;

        return true;
      });

      setCounters(root, filtered);
      setAgeHeadline(ageTitle, filtered, SUBTYPE || TYPE);

      if (secondaryOfferCount) {
        secondaryOfferCount.textContent = String(filtered.length);
      }

      let displayArr = filtered;
      let groups = null;

      if (isPowertrainingPage) {
        groups = groupByLocation(filtered);
        displayArr = groups.map((g) => g.rep);
      }

      renderList(
        listEl,
        displayArr,
        filtered,
        mapManager,
        isPowertrainingPage,
        root,
        groups,
      );

      mapManager?.render_markers?.(displayArr);

      if (!mapManager?.map) return;

      const noneSet = !day && isNaN(age) && !loc && !seasonVal && !weekVal;
      if (noneSet) {
        mapManager.reset_view();
        return;
      }

      await mapManager.focus_for_filters({ day, age, loc, items, filtered });
    }

    if (daySel) {
      daySel.addEventListener("change", () => {
        resetSelect(ageSel);
        resetSelect(locSel);
        apply();
      });
    }

    if (ageSel) ageSel.addEventListener("change", apply);
    if (locSel) locSel.addEventListener("change", apply);

    if (holidaySeasonSel) {
      holidaySeasonSel.addEventListener("change", () => {
        resetSelect(holidayWeekSel);
        resetSelect(locSel);

        fillHolidayWeeksSelect(
          holidayWeekSel,
          items,
          holidaySeasonSel.value || "",
        );
        apply();
      });
    }

    // if (holidayWeekSel) holidayWeekSel.addEventListener("change", apply);

    // apply();

    if (holidayWeekSel) holidayWeekSel.addEventListener("change", apply);

    if (resetBtn) {
      resetBtn.addEventListener("click", () => {
        resetSelect(daySel);
        resetSelect(ageSel);
        resetSelect(locSel);
        resetSelect(holidaySeasonSel);
        resetSelect(holidayWeekSel);

        if (isHolidayPage) {
          fillHolidayWeeksSelect(holidayWeekSel, items, "");
        }

        apply();
      });
    }

    apply();
  });
})();

// // assets/js/offers-directory.js
// (function () {
//   "use strict";

//   const C = window.KSOffersDirectoryCore;
//   if (!C) return;

//   const $ = C.$;
//   const {
//     normalizeCity,
//     cityMatches,
//     cityFromOffer,
//     buildUrl,
//     fillLocations,
//     groupByLocation,
//     renderList,
//     setCounters,
//     setAgeHeadline,
//     matchesType,
//     getProgramGroupFromKey,
//     filterByProgram,
//     getHolidayLabel,
//     getHolidaySeasonKey,
//     getHolidayWeekKey,
//     fillHolidayWeeksSelect,
//     normDay,
//     offerHasDay,
//   } = C;

//   document.addEventListener("DOMContentLoaded", async () => {
//     const root = $("#ksDir");
//     if (!root) return;

//     window.KSOffersDirectoryUI?.enhanceFilterSelects?.(root);

//     const daySel = $("#ksFilterDay", root);
//     const ageSel = $("#ksFilterAge", root);
//     const locSel = $("#ksFilterLoc", root);
//     const listEl = $("#ksDirList", root);
//     const ageTitle = $("[data-age-title]", root);
//     const holidaySeasonSel = $("#ksFilterHolidaySeason", root);
//     const holidayWeekSel = $("#ksFilterHolidayWeek", root);

//     const TYPE = root.dataset.type || "";
//     const API = root.dataset.api || "http://localhost:5000";
//     const CITY = root.dataset.city || "";
//     const CATEGORY = root.dataset.category || "";
//     const SUBTYPE = root.dataset.subtype || "";

//     const catLower = (CATEGORY || "").toLowerCase();
//     const isHolidayPage =
//       catLower === "holiday" ||
//       catLower === "holidayprograms" ||
//       getProgramGroupFromKey(SUBTYPE || TYPE) === "camp";

//     const normProgKey = (SUBTYPE || TYPE || "").toLowerCase();
//     const isPowertrainingPage =
//       normProgKey.includes("powertraining") ||
//       normProgKey.includes("athletictraining") ||
//       normProgKey.includes("athletiktraining");

//     const mapManager = window.KSOffersDirectoryMap?.create(root) || null;

//     let items = [];
//     let filtered = [];

//     function resetSelect(sel) {
//       if (!sel) return;
//       sel.value = "";
//       sel.dispatchEvent(new Event("change", { bubbles: true }));
//     }

//     function syncEnhancedDropdowns() {
//       window.KSOffersDirectoryUI?.enhanceFilterSelects?.(root);
//     }

//     function getSelectedDay() {
//       const dayRaw = String(daySel?.value || "").trim();

//       if (
//         isHolidayPage ||
//         !dayRaw ||
//         normalizeCity(dayRaw) === normalizeCity("Alle Tage")
//       ) {
//         return "";
//       }

//       return normDay(dayRaw);
//     }

//     function getSelectedAge() {
//       if (isHolidayPage || !ageSel || ageSel.value === "") return NaN;
//       return parseInt(ageSel.value, 10);
//     }

//     function getSelectedHolidaySeason() {
//       return isHolidayPage && holidaySeasonSel ? holidaySeasonSel.value : "";
//     }

//     function getSelectedHolidayWeek() {
//       return isHolidayPage && holidayWeekSel ? holidayWeekSel.value : "";
//     }

//     function matchesHolidayFilters(offer, seasonVal, weekVal) {
//       const label = getHolidayLabel(offer);
//       const season = getHolidaySeasonKey(label);
//       const weekKey = getHolidayWeekKey(offer);

//       if (seasonVal && seasonVal !== season) return false;
//       if (weekVal && weekVal !== weekKey) return false;

//       return true;
//     }

//     function matchesStandardFilters(offer, day, age) {
//       if (day && !offerHasDay(offer, day)) return false;

//       if (!Number.isNaN(age)) {
//         const from = Number(offer.ageFrom ?? 0);
//         const to = Number(offer.ageTo ?? 99);

//         if (!(age >= from && age <= to)) return false;
//       }

//       return true;
//     }

//     function offerMatchesBaseFilters(offer) {
//       if (TYPE && !matchesType(offer, TYPE)) return false;
//       if (CATEGORY && offer.category !== CATEGORY) return false;
//       if (SUBTYPE && offer.sub_type !== SUBTYPE) return false;

//       return true;
//     }

//     function offerMatchesLocation(offer, location) {
//       if (!location) return true;

//       const offerCity = cityFromOffer(offer);
//       return cityMatches(offerCity, location);
//     }

//     function getDisplayData() {
//       let displayArr = filtered;
//       let groups = null;

//       if (isPowertrainingPage) {
//         groups = groupByLocation(filtered);
//         displayArr = groups.map((group) => group.rep);
//       }

//       return { displayArr, groups };
//     }

//     async function focusMapForFilters(day, age, location, seasonVal, weekVal) {
//       if (!mapManager?.map) return;

//       const noFiltersSet =
//         !day && Number.isNaN(age) && !location && !seasonVal && !weekVal;

//       if (noFiltersSet) {
//         mapManager.reset_view();
//         return;
//       }

//       await mapManager.focus_for_filters({
//         day,
//         age,
//         loc: location,
//         items,
//         filtered,
//       });
//     }

//     async function apply() {
//       const day = getSelectedDay();
//       const age = getSelectedAge();
//       const location = (locSel?.value || "").trim();
//       const seasonVal = getSelectedHolidaySeason();
//       const weekVal = getSelectedHolidayWeek();

//       filtered = items.filter((offer) => {
//         if (!offerMatchesBaseFilters(offer)) return false;

//         if (isHolidayPage) {
//           if (!matchesHolidayFilters(offer, seasonVal, weekVal)) return false;
//         } else if (!matchesStandardFilters(offer, day, age)) {
//           return false;
//         }

//         return offerMatchesLocation(offer, location);
//       });

//       setCounters(root, filtered);
//       setAgeHeadline(ageTitle, filtered, SUBTYPE || TYPE);

//       const { displayArr, groups } = getDisplayData();

//       renderList(
//         listEl,
//         displayArr,
//         filtered,
//         mapManager,
//         isPowertrainingPage,
//         root,
//         groups,
//       );

//       mapManager?.render_markers?.(displayArr);

//       await focusMapForFilters(day, age, location, seasonVal, weekVal);
//     }

//     async function loadOffers() {
//       const url = buildUrl(`${API}/api/offers`, {
//         type: TYPE || undefined,
//         category: CATEGORY || undefined,
//         sub_type: SUBTYPE || undefined,
//         limit: 500,
//       });

//       try {
//         const data = await fetch(url).then((response) => response.json());

//         items = Array.isArray(data?.items)
//           ? data.items
//           : Array.isArray(data)
//             ? data
//             : [];

//         const currentProgramKey = (SUBTYPE || TYPE || "").trim();
//         items = filterByProgram(items, currentProgramKey);

//         fillLocations(locSel, items);
//         setInitialCityFilter();
//         mapManager?.set_all_points?.(items);

//         if (isHolidayPage) {
//           fillHolidayWeeksSelect(
//             holidayWeekSel,
//             items,
//             holidaySeasonSel ? holidaySeasonSel.value || "" : "",
//           );

//           const weekSel = $("#ksFilterHolidayWeek", root);
//           if (weekSel) {
//             weekSel.dispatchEvent(new Event("change", { bubbles: true }));
//           }
//         }
//       } catch {
//         if (listEl) {
//           listEl.innerHTML =
//             '<li class="ks-offer ks-offer--empty"><article class="card"><h3 class="card-title">Keine Angebote gefunden.</h3></article></li>';
//         }
//       }
//     }

//     function setInitialCityFilter() {
//       if (!CITY || !locSel) return;

//       const option = Array.from(locSel.options).find(
//         (item) => normalizeCity(item.value) === normalizeCity(CITY),
//       );

//       if (option) locSel.value = option.value;
//     }

//     function bindStandardFilterEvents() {
//       if (daySel) {
//         daySel.addEventListener("change", () => {
//           resetSelect(ageSel);
//           resetSelect(locSel);
//           apply();
//         });
//       }

//       if (ageSel) ageSel.addEventListener("change", apply);
//       if (locSel) locSel.addEventListener("change", apply);
//     }

//     function bindHolidayFilterEvents() {
//       if (holidaySeasonSel) {
//         holidaySeasonSel.addEventListener("change", () => {
//           resetSelect(holidayWeekSel);
//           resetSelect(locSel);

//           fillHolidayWeeksSelect(
//             holidayWeekSel,
//             items,
//             holidaySeasonSel.value || "",
//           );

//           syncEnhancedDropdowns();
//           apply();
//         });
//       }

//       if (holidayWeekSel) holidayWeekSel.addEventListener("change", apply);
//     }

//     function bindLanguageRerender() {
//       window.addEventListener("ks:i18n-ready", () => {
//         fillLocations(locSel, items);

//         if (isHolidayPage) {
//           fillHolidayWeeksSelect(
//             holidayWeekSel,
//             items,
//             holidaySeasonSel ? holidaySeasonSel.value || "" : "",
//           );
//         }

//         syncEnhancedDropdowns();
//         apply();
//       });
//     }

//     await loadOffers();

//     bindStandardFilterEvents();
//     bindHolidayFilterEvents();
//     bindLanguageRerender();

//     apply();
//   });
// })();
