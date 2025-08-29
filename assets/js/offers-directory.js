(function () {
  "use strict";

  /* ================= DEBUG ================= */
  const DEBUG = true;
  const log  = (...a) => { if (DEBUG) console.log("[KS-DIR]", ...a); };
  const warn = (...a) => { if (DEBUG) console.warn("[KS-DIR]", ...a); };
  const err  = (...a) => { if (DEBUG) console.error("[KS-DIR]", ...a); };

  /* ================= Helpers (DOM / Text) ================= */
  function $(sel, ctx=document){ return ctx.querySelector(sel); }
  function $all(sel, ctx=document){ return Array.from(ctx.querySelectorAll(sel)); }
  const esc = s => String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

  const DAY_ALIASES = {
    m:'Mo', mo:'Mo', montag:'Mo', monday:'Mo', mon:'Mo',
    di:'Di', dienstag:'Di', tuesday:'Di', tue:'Di',
    mi:'Mi', mittwoch:'Mi', wednesday:'Mi', wed:'Mi',
    do:'Do', donnerstag:'Do', thursday:'Do', thu:'Do',
    fr:'Fr', freitag:'Fr', friday:'Fr', fri:'Fr',
    sa:'Sa', samstag:'Sa', saturday:'Sa', sat:'Sa',
    so:'So', sonntag:'So', sunday:'So', sun:'So'
  };
  function normDay(v){ if(!v) return ""; const k=String(v).trim().toLowerCase(); return DAY_ALIASES[k]||v; }
  function offerHasDay(o, d){ if(!d) return true; const a=Array.isArray(o.days)?o.days:[]; return a.some(x=>normDay(x)===d); }

  function normalizeCity(s){
    if(!s) return "";
    let out=String(s).normalize("NFD").replace(/\p{Diacritic}/gu,"");
    return out.replace(/[^a-z0-9]+/gi," ").trim().toLowerCase();
  }
  function cityMatches(itemLoc, selectedLoc){
    const a=normalizeCity(itemLoc), b=normalizeCity(selectedLoc);
    if(!a||!b) return false; return a===b||a.includes(b)||b.includes(a);
  }
  function cityFromLocationString(s){
    const raw=String(s||"").trim(); if(!raw) return "";
    const split=raw.split(/\s*[-–—,•|]\s*/); return split[0]||raw;
  }

  /* ================= Koordinaten-Parser ================= */
  const isLat=n=>Number.isFinite(n)&&n>=-90&&n<=90;
  const isLng=n=>Number.isFinite(n)&&n>=-180&&n<=180;
  function parseCoord(v){ if(v==null) return NaN; const n=Number(String(v).trim().replace(",",".")); return Number.isFinite(n)?n:NaN; }
  function normalizePair(a,b){ if(isLat(a)&&isLng(b))return[a,b]; if(isLng(a)&&isLat(b))return[b,a]; return[a,b]; }
  function parseLatLngString(str){
    const m=String(str||"").trim().match(/(-?\d+(?:[.,]\d+)?)[\s,;]+(-?\d+(?:[.,]\d+)?)/);
    if(!m) return null; const a=parseCoord(m[1]), b=parseCoord(m[2]);
    if(!Number.isFinite(a)||!Number.isFinite(b)) return null; return normalizePair(a,b);
  }
  function coordsFromArray(arr){
    if(!Array.isArray(arr)||arr.length<2) return null;
    const a=parseCoord(arr[0]), b=parseCoord(arr[1]);
    if(!Number.isFinite(a)||!Number.isFinite(b)) return null; return normalizePair(a,b);
  }
  function firstFinite(cands){ for(const c of cands){ const v=parseCoord(c); if(Number.isFinite(v)) return v; } return NaN; }
  function latLngOf(o){
    { const lat=firstFinite([o.lat,o.latitude,o.latDeg]); const lng=firstFinite([o.lng,o.lon,o.long,o.longitude]);
      if(isLat(lat)&&isLng(lng)) return [lat,lng]; }
    const C=[o.coords,o.coord,o.position,o.geo,o.gps,o.map,o.center,o.centerPoint,o.point,o.location].filter(Boolean);
    for(const c of C){
      const lat=firstFinite([c?.lat,c?.latitude]); const lng=firstFinite([c?.lng,c?.lon,c?.long,c?.longitude]);
      if(isLat(lat)&&isLng(lng)) return [lat,lng];
      if(typeof c==="string"){ const p=parseLatLngString(c); if(p) return p; }
      for(const k of Object.keys(c||{})){ if(typeof c[k]==="string"){ const p=parseLatLngString(c[k]); if(p) return p; } }
      if(c?.coordinates){ const p=coordsFromArray(c.coordinates); if(p) return p; }
      if(c?.coords){ const p=coordsFromArray(c.coords); if(p) return p; }
      if(c?.latlng){ const p=coordsFromArray(c.latlng); if(p) return p; }
    }
    for(const key of ["latlng","lat_lon","lon_lat"]){ const p=parseLatLngString(o[key]); if(p) return p; }
    for(const [k,v] of Object.entries(o)){ if(typeof v==="string"){ const p=parseLatLngString(v); if(p) return p; } }
    return null;
  }
  function pointsFrom(items){ const pts=[]; (items||[]).forEach(o=>{ const ll=latLngOf(o); if(ll) pts.push(ll); }); return pts; }

  /* ================= Geocoding (Nominatim) ================= */
  const GEOCODE_ENDPOINT="https://nominatim.openstreetmap.org/search";
  const geoCache=new Map(), inflight=new Map();
  async function geocodeCity(name){
    const key=normalizeCity(name); if(!key) return null;
    if(geoCache.has(key)) return geoCache.get(key);
    if(inflight.has(key)) return inflight.get(key);
    const url=`${GEOCODE_ENDPOINT}?format=jsonv2&q=${encodeURIComponent(name)}&limit=1&addressdetails=0`;
    const p=fetch(url,{headers:{Accept:"application/json"}}).then(r=>r.json()).then(j=>{
      let res=null; if(Array.isArray(j)&&j.length){ const lat=parseCoord(j[0].lat), lon=parseCoord(j[0].lon);
        if(Number.isFinite(lat)&&Number.isFinite(lon)) res=[lat,lon]; }
      geoCache.set(key,res); inflight.delete(key); return res;
    }).catch(e=>{ inflight.delete(key); geoCache.set(key,null); warn("Geocode fail:",name,e); return null; });
    inflight.set(key,p); return p;
  }
  async function geocodeCities(names,limit=8){
    const pts=[], seen=new Set();
    for(const n of names){ const k=normalizeCity(n); if(!k||seen.has(k)) continue; seen.add(k);
      const p=await geocodeCity(n); if(p) pts.push(p); if(pts.length>=limit) break; }
    return pts;
  }

  /* ================= Map ================= */
  const DEFAULT_CENTER=[51.1657,10.4515], DEFAULT_Z=6;
  function ensureMapHeight(el){ if(el&&el.getBoundingClientRect().height<50){ el.style.height="360px"; log("Map: Fallback-Höhe 360px"); } }
  function initMap(el){
    if(!el||!window.L) return null;
    const m=L.map(el,{scrollWheelZoom:true,zoomControl:true});
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{maxZoom:19,attribution:"© OpenStreetMap"}).addTo(m);
    m.setView(DEFAULT_CENTER,DEFAULT_Z);
    m.whenReady(()=>m.invalidateSize()); requestAnimationFrame(()=>m.invalidateSize()); setTimeout(()=>m.invalidateSize(),150);
    if(window.ResizeObserver){ new ResizeObserver(()=>m.invalidateSize()).observe(el); }
    return m;
  }
  function clearMarkers(ms){ ms.forEach(x=>x.remove()); return []; }
  function setMarkers(map,arr,listEl){
    let ms=[]; arr.forEach((o,i)=>{ const ll=latLngOf(o); if(!ll) return;
      const mk=L.marker(ll).addTo(map); mk.bindPopup(`<strong>${esc(o.title||o.type||"Standort")}</strong><br>${esc(o.location||"")}`);
      mk.on("click",()=>{ const li=listEl?.querySelector(`.ks-offer[data-offer-index="${i}"]`); li&&li.scrollIntoView({behavior:"smooth",block:"start"}); });
      ms.push(mk);
    }); return ms;
  }
  function flyTo(map,pts){
    if(!pts.length) return;
    if(pts.length===1) map.flyTo(pts[0],12,{duration:.5});
    else map.flyToBounds(L.latLngBounds(pts),{padding:[24,24]});
  }
  function resetView(map,allPts){
    if(!map) return;
    if(allPts.length>1) flyTo(map,allPts);
    else if(allPts.length===1) map.flyTo(allPts[0],12,{duration:.5});
    else map.setView(DEFAULT_CENTER,DEFAULT_Z);
  }

  /* ================= UI Helpers ================= */
  function buildUrl(base,q){ const u=new URL(base,window.location.origin); Object.entries(q||{}).forEach(([k,v])=>{ if(v!=null&&v!=="") u.searchParams.set(k,v); }); return u.toString(); }
  function fillLocations(sel,arr){
    if(!sel) return;
    const cities=Array.from(new Set(arr.map(o=>cityFromLocationString(o.location)).filter(Boolean))).sort((a,b)=>a.localeCompare(b));
    sel.innerHTML=`<option value="">Alle Standorte</option>`+cities.map(c=>`<option>${esc(c)}</option>`).join("");
    log("Standorte:",cities.length,cities.slice(0,10));
  }
  function nameAddr(o){
    const name=o.clubName||o.club||o.provider||o.title||o.type||"Standort";
    const l1=o.address||o.street||"", l2=[o.zip||o.postalCode||"",o.city||""].filter(Boolean).join(", ");
    const addr=(l1&&l2)?`${l1} - ${l2}`:l1||l2||(o.location||"");
    return {name,addr};
  }
  function renderList(listEl,arr,ref,map){
    if(!listEl) return;
    if(!arr.length){ listEl.innerHTML='<li><div class="card">Keine Angebote gefunden.</div></li>'; return; }
    listEl.innerHTML=arr.map((o,i)=>{ const {name,addr}=nameAddr(o);
      return `<li class="ks-offer" data-offer-index="${i}"><article class="card"><h3 class="card-title">${esc(name)}</h3>${addr?`<div class="offer-meta">${esc(addr)}</div>`:""}</article></li>`;
    }).join("");
    $all(".ks-offer",listEl).forEach(li=>{
      li.addEventListener("click",()=>{ const i=parseInt(li.dataset.offerIndex||"-1",10), ll=latLngOf(ref[i]); if(ll&&map) map.setView(ll,14,{animate:true}); });
    });
  }
  function counters(root,arr){
    const o=$("[data-count-offers]",root), l=$("[data-count-locations]",root);
    if(o) o.textContent=String(arr.length);
    if(l){ const s=new Set(arr.map(x=>(x.location||"").trim()).filter(Boolean)); l.textContent=String(s.size); }
  }
  function ageHeadline(el,arr){
    if(!el) return; let min=null,max=null;
    arr.forEach(o=>{ if(o.ageFrom!=null) min=min==null?o.ageFrom:Math.min(min,o.ageFrom); if(o.ageTo!=null) max=max==null?o.ageTo:Math.max(max,o.ageTo); });
    el.textContent=(min!=null&&max!=null)?`${min}–${max} Jahre`:"alle Altersstufen";
  }

  /* ================= Movement (prio: loc → day → age) ================= */
  async function moveForLoc(map,loc,items,filtered){
    const pts=pointsFrom(filtered); if(pts.length){ flyTo(map,pts); return; }
    const locItems=items.filter(o=>cityMatches(o.location||"",loc)); const p2=pointsFrom(locItems);
    if(p2.length){ flyTo(map,p2); return; }
    const g=await geocodeCity(loc); if(g) flyTo(map,[g]);
  }
  async function moveForDay(map,day,items,filtered){
    const pts=pointsFrom(filtered); if(pts.length){ flyTo(map,pts); return; }
    const dItems=items.filter(o=>offerHasDay(o,day)); const p2=pointsFrom(dItems);
    if(p2.length){ flyTo(map,p2); return; }
    const names=Array.from(new Set(dItems.map(o=>cityFromLocationString(o.location)).filter(Boolean)));
    const gs=await geocodeCities(names,8); if(gs.length) flyTo(map,gs);
  }
  async function moveForAge(map,age,items,filtered){
    const pts=pointsFrom(filtered); if(pts.length){ flyTo(map,pts); return; }
    const aItems=items.filter(o=>{ const f=Number(o.ageFrom??0),t=Number(o.ageTo??99); return age>=f&&age<=t; });
    const p2=pointsFrom(aItems); if(p2.length){ flyTo(map,p2); return; }
    const names=Array.from(new Set(aItems.map(o=>cityFromLocationString(o.location)).filter(Boolean)));
    const gs=await geocodeCities(names,8); if(gs.length) flyTo(map,gs);
  }

  /* ================= Main ================= */
  document.addEventListener("DOMContentLoaded", async () => {
    const root=$("#ksDir"); if(!root){ warn("Kein #ksDir"); return; }
    const daySel=$("#ksFilterDay",root), ageSel=$("#ksFilterAge",root), locSel=$("#ksFilterLoc",root);
    const listEl=$("#ksDirList",root), ageTitle=$("[data-age-title]",root);
    const TYPE=root.dataset.type||"", API=root.dataset.api||"http://localhost:5000", CITY=root.dataset.city||"";
    const mapNode=$("#ksMap",root); ensureMapHeight(mapNode); const map=initMap(mapNode);

    let items=[], filtered=[], markers=[], allPts=[];
    const url=buildUrl(`${API}/api/offers`,{type:TYPE||undefined,limit:500});
    log("Fetch:",url);
    try{
      const data=await fetch(url).then(r=>r.json());
      items=Array.isArray(data?.items)?data.items:(Array.isArray(data)?data:[]);
      log("Offers:",items.length, items[0]?Object.keys(items[0]):[]);
      fillLocations(locSel,items);
      if(CITY&&locSel){ const opt=[...locSel.options].find(o=>normalizeCity(o.value)===normalizeCity(CITY)); if(opt) locSel.value=opt.value; }
      allPts=pointsFrom(items);
    }catch(e){ err("API-Fehler:",e); if(listEl) listEl.innerHTML='<li><div class="card">Keine Angebote gefunden.</div></li>'; }

    async function apply(){
      const day=normDay(daySel?.value||"");
      const age=ageSel&&ageSel.value!==""?parseInt(ageSel.value,10):NaN;
      const loc=(locSel?.value||"").trim();

      filtered=items.filter(o=>{
        if(TYPE&&o.type!==TYPE) return false;
        if(day&&!offerHasDay(o,day)) return false;
        if(!isNaN(age)){ const f=Number(o.ageFrom??0),t=Number(o.ageTo??99); if(!(age>=f&&age<=t)) return false; }
        if(loc&&!cityMatches(o.location||"",loc)) return false;
        return true;
      });

      renderList(listEl,filtered,filtered,map);
      if(map){ markers=clearMarkers(markers); markers=setMarkers(map,filtered,listEl); }
      counters(root,filtered); ageHeadline(ageTitle,filtered);

      if(!map) return;
      const allEmpty=(!day&&isNaN(age)&&!loc);
      if(allEmpty){ resetView(map,allPts); return; }
      if(loc){ await moveForLoc(map,loc,items,filtered); return; }
      if(day){ await moveForDay(map,day,items,filtered); return; }
      if(!isNaN(age)){ await moveForAge(map,age,items,filtered); return; }
    }

    daySel&&daySel.addEventListener("change",apply);
    ageSel&&ageSel.addEventListener("change",apply);
    locSel&&locSel.addEventListener("change",apply);

    apply(); // initial: rendert Liste/Marker; Map bleibt Startansicht bis Filter greifen

    if(DEBUG){
      window.KS_DIR_DEBUG={
        get items(){return items;}, get filtered(){return filtered;},
        latLngOf, pointsFrom, geocodeCity, geocodeCities,
        cityFromLocationString, normalizeCity, cityMatches,
        recalc:()=>apply()
      };
      log("KS_DIR_DEBUG bereit.");
    }
  });
})();
