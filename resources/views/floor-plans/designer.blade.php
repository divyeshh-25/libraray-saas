@extends('layouts.app')
@section('title', 'Floor plan designer')
@section('content')
<x-page-header :title="$hall->name.' · '.$floor->name" subtitle="Floor plan &amp; seat designer" :back="['Halls', route('halls.show', $hall->id)]" />

{{--
    Embedded, unmodified Library Floor Planner (library_floor_planner.html).
    Only two integration seams were added to the original script: loading the
    initial layout from the server instead of localStorage, and posting Save
    to the floor-plan endpoint instead of localStorage. All markup, styles,
    tools, drag/resize/undo/zoom behavior are otherwise untouched.
--}}
<div id="fp-root">
<style>
#fp-root{
 --bg:#f4f6fa;--surface:#fff;--border:#e3e7ee;--border-strong:#cfd5e0;--text:#172033;--muted:#667085;
 --primary:#2457c5;--primary-h:#1947a9;--primary-soft:#eaf0fd;--danger:#b42318;--danger-soft:#fef3f2;
 --shadow:0 1px 2px rgba(16,24,40,.06),0 1px 3px rgba(16,24,40,.08);--shadow-lg:0 8px 24px rgba(16,24,40,.14);
 --header-h:56px;--left-w:224px;--right-w:288px;
 font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:var(--text);background:var(--bg);
 height:calc(100vh - 7.5rem);border-radius:12px;overflow:hidden;box-shadow:var(--shadow-lg);
}
#fp-root *{box-sizing:border-box}
#fp-root button,#fp-root input,#fp-root select{font:inherit;color:inherit}
#fp-root svg.i{width:16px;height:16px;flex:none;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}

/* ---------- App shell ---------- */
#fp-root .app{display:grid;width:100%;height:100%;
 grid-template-columns:var(--left-w) minmax(0,1fr) var(--right-w);
 grid-template-rows:var(--header-h) minmax(0,1fr);
 grid-template-areas:"head head head" "left main right"}
#fp-root .header{grid-area:head;display:flex;align-items:center;gap:16px;padding:0 16px;background:var(--surface);border-bottom:1px solid var(--border);z-index:5}
#fp-root .brand{display:flex;align-items:center;gap:10px;min-width:0}
#fp-root .logo{width:32px;height:32px;border-radius:8px;background:var(--primary);color:#fff;display:grid;place-items:center}
#fp-root .brand h1{font-size:15px;margin:0;line-height:1.1;white-space:nowrap}
#fp-root .brand .sub{font-size:11px;color:var(--muted);white-space:nowrap}
#fp-root .divider{width:1px;height:26px;background:var(--border)}
#fp-root .floor{display:flex;align-items:center;gap:8px;min-width:0}
#fp-root .floor label{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;font-weight:600;margin:0;white-space:nowrap}
#fp-root .floor input{width:180px;max-width:100%;font-weight:600}
#fp-root .spacer{flex:1}
#fp-root .actions{display:flex;gap:8px;align-items:center}

/* ---------- Controls ---------- */
#fp-root .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:32px;padding:0 12px;border:1px solid var(--border-strong);border-radius:8px;background:var(--surface);cursor:pointer;font-weight:500;white-space:nowrap;transition:background .12s,border-color .12s}
#fp-root .btn:hover{background:var(--primary-soft);border-color:#9db4e6}
#fp-root .btn:focus-visible,#fp-root .tool:focus-visible,#fp-root input:focus-visible{outline:2px solid var(--primary);outline-offset:1px}
#fp-root .btn.primary{background:var(--primary);border-color:var(--primary);color:#fff}
#fp-root .btn.primary:hover{background:var(--primary-h)}
#fp-root .btn.danger{color:var(--danger)}#fp-root .btn.danger:hover{background:var(--danger-soft);border-color:#f0a8a2}
#fp-root .btn.icon{width:32px;padding:0}
#fp-root .btn.active{background:var(--primary-soft);border-color:#9db4e6;color:var(--primary)}
#fp-root .btn:disabled{opacity:.45;cursor:default}
#fp-root input[type=text],#fp-root input[type=number]{height:32px;width:100%;padding:0 10px;border:1px solid var(--border-strong);border-radius:8px;background:var(--surface)}
#fp-root input:disabled{background:#f2f4f7;color:var(--muted)}

/* ---------- Sidebars ---------- */
#fp-root .sidebar{background:var(--surface);overflow-y:auto;min-height:0}
#fp-root .left{grid-area:left;border-right:1px solid var(--border)}
#fp-root .right{grid-area:right;border-left:1px solid var(--border)}
#fp-root .sec{padding:14px 14px 12px;border-bottom:1px solid var(--border)}
#fp-root .sec:last-child{border-bottom:0}
#fp-root .sec h3{margin:0 0 10px;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);font-weight:700;display:flex;justify-content:space-between;align-items:center}
#fp-root .tools{display:grid;grid-template-columns:1fr 1fr;gap:8px}
#fp-root .tool{display:flex;flex-direction:column;align-items:center;gap:6px;padding:12px 6px 9px;border:1px solid var(--border);border-radius:10px;background:var(--surface);cursor:grab;font-size:12px;font-weight:500;user-select:none;transition:border-color .12s,box-shadow .12s,transform .12s}
#fp-root .tool:hover{border-color:#9db4e6;box-shadow:var(--shadow);transform:translateY(-1px)}
#fp-root .tool:active{cursor:grabbing}
#fp-root .tool .sw{width:34px;height:34px;border-radius:8px;display:grid;place-items:center;border:1px solid rgba(0,0,0,.08);color:#334155}
#fp-root .tool .sw svg{width:18px;height:18px}
#fp-root .hint{color:var(--muted);font-size:12px;line-height:1.55}
#fp-root .hint ul{margin:0;padding-left:16px}#fp-root .hint li{margin-bottom:4px}
#fp-root kbd{font:11px ui-monospace,monospace;background:#f2f4f7;border:1px solid var(--border-strong);border-radius:4px;padding:0 4px}

#fp-root .props{display:grid;grid-template-columns:1fr 1fr;gap:10px 10px}
#fp-root .props .full{grid-column:1/-1}
#fp-root .props label,#fp-root .dims label{display:block;font-size:11px;color:var(--muted);font-weight:600;margin:0}
#fp-root .props label input,#fp-root .dims label input{margin-top:4px}
#fp-root .empty{display:grid;place-items:center;text-align:center;gap:8px;padding:28px 12px;color:var(--muted)}
#fp-root .empty .ico{width:44px;height:44px;border-radius:12px;background:#f2f4f7;display:grid;place-items:center}
#fp-root .empty .ico svg{width:22px;height:22px}
#fp-root .rotrow{display:flex;gap:6px;margin-top:4px}#fp-root .rotrow input{margin:0!important}
#fp-root .hidden{display:none!important}
#fp-root .dims{display:grid;grid-template-columns:1fr 1fr;gap:10px}
#fp-root .dims .btn{grid-column:1/-1}

/* ---------- Workspace ---------- */
#fp-root .main{grid-area:main;display:flex;flex-direction:column;min-width:0;min-height:0;background:var(--bg)}
#fp-root .toolbar{display:flex;align-items:center;gap:8px;padding:8px 12px;background:var(--surface);border-bottom:1px solid var(--border);flex-wrap:wrap}
#fp-root .group{display:flex;align-items:center;gap:4px}
#fp-root .zoomlabel{min-width:56px;font-variant-numeric:tabular-nums}
#fp-root .count{font-size:12px;color:var(--muted);padding:0 4px}
#fp-root .canvas-wrap{flex:1;min-height:0;overflow:auto;position:relative;background:
 radial-gradient(circle,#cdd4e0 1px,transparent 1px) 0 0/20px 20px,#e8ecf3}
#fp-root .canvas-pad{display:block;width:max-content;padding:32px;min-width:100%;min-height:100%}
#fp-root svg#canvas{display:block;background:#fff;touch-action:none;max-width:none;box-shadow:var(--shadow-lg);border:1px solid var(--border-strong)}
#fp-root .statusbar{display:flex;justify-content:space-between;gap:12px;padding:6px 14px;background:var(--surface);border-top:1px solid var(--border);font-size:12px;color:var(--muted)}
#fp-root .obj{cursor:move}#fp-root .obj.selected .shape{stroke:#2457c5;stroke-width:2.5;stroke-dasharray:5 3}
#fp-root .canvas-wrap.readonly .obj{cursor:pointer}
#fp-root #lockToggleBtn.active{background:var(--primary-soft);border-color:#9db4e6;color:var(--primary)}
#fp-root:fullscreen{height:100vh;width:100vw;border-radius:0;box-shadow:none}
#fp-root .handle{fill:#2457c5;stroke:#fff;stroke-width:1.5;cursor:nwse-resize}
#fp-root .canvas-wrap.dropping{outline:2px dashed var(--primary);outline-offset:-6px}
#fp-root .toast{position:fixed;left:50%;bottom:48px;transform:translate(-50%,20px);background:#172033;color:#fff;padding:8px 14px;border-radius:8px;box-shadow:var(--shadow-lg);opacity:0;pointer-events:none;transition:.2s;z-index:50}
#fp-root .toast.show{opacity:1;transform:translate(-50%,0)}

/* ---------- Responsive ---------- */
@media(max-width:1100px){#fp-root{--left-w:190px;--right-w:250px}#fp-root .brand .sub{display:none}#fp-root .floor input{width:130px}}
@media(max-width:860px){
 #fp-root{height:auto;min-height:70vh}
 #fp-root .app{grid-template-columns:minmax(0,1fr);grid-template-rows:auto auto minmax(60vh,1fr) auto;
  grid-template-areas:"head" "left" "main" "right";height:auto}
 #fp-root .header{flex-wrap:wrap;padding:8px 12px;height:auto;gap:8px}
 #fp-root .header .divider{display:none}#fp-root .floor{order:3;width:100%}#fp-root .floor input{flex:1;width:auto}
 #fp-root .left{border-right:0;border-bottom:1px solid var(--border);overflow-x:auto;overflow-y:hidden}
 #fp-root .left .sec.how{display:none}
 #fp-root .left .sec{padding:8px 12px}#fp-root .left .sec h3{display:none}
 #fp-root .tools{display:flex;gap:8px}#fp-root .tool{flex-direction:row;padding:8px 12px;min-width:max-content}#fp-root .tool .sw{width:24px;height:24px}#fp-root .tool .sw svg{width:14px;height:14px}
 #fp-root .right{border-left:0;border-top:1px solid var(--border);max-height:none}
 #fp-root .btn .lbl{display:none}#fp-root .btn.keep .lbl{display:inline}
 #fp-root .canvas-wrap{min-height:55vh}
}
</style>

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
 <defs>
  <symbol id="i-seat" viewBox="0 0 24 24"><path d="M7 11V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v6"/><rect x="5" y="11" width="14" height="5" rx="2"/><path d="M7 16v5M17 16v5"/></symbol>
  <symbol id="i-wall" viewBox="0 0 24 24"><rect x="2" y="9" width="20" height="6" rx="1"/><path d="M8 9v6M14 9v6"/></symbol>
  <symbol id="i-block" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/></symbol>
  <symbol id="i-wc" viewBox="0 0 24 24"><circle cx="7" cy="4.5" r="1.7"/><path d="M7 8v7M4.5 10h5M5.5 15v6M8.5 15v6"/><circle cx="17" cy="4.5" r="1.7"/><path d="M14.5 21l2.5-9 2.5 9M14 10h6"/></symbol>
  <symbol id="i-door" viewBox="0 0 24 24"><path d="M5 21V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v17M3 21h18"/><circle cx="15" cy="12.5" r=".8"/></symbol>
  <symbol id="i-bell" viewBox="0 0 24 24"><path d="M3 18h18M5 18a7 7 0 0 1 14 0M12 8V5M10 5h4"/></symbol>
  <symbol id="i-office" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="1"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M10 21v-4h4v4"/></symbol>
  <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
  <symbol id="i-minus" viewBox="0 0 24 24"><path d="M5 12h14"/></symbol>
  <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="1"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/></symbol>
  <symbol id="i-undo" viewBox="0 0 24 24"><path d="M9 14L4 9l5-5"/><path d="M4 9h10a6 6 0 0 1 0 12h-3"/></symbol>
  <symbol id="i-fit" viewBox="0 0 24 24"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></symbol>
  <symbol id="i-up" viewBox="0 0 24 24"><path d="M12 16V4M7 9l5-5 5 5M4 20h16"/></symbol>
  <symbol id="i-down" viewBox="0 0 24 24"><path d="M12 4v12M7 11l5 5 5-5M4 20h16"/></symbol>
  <symbol id="i-save" viewBox="0 0 24 24"><path d="M5 3h11l4 4v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M8 3v5h7V3M8 21v-7h8v7"/></symbol>
  <symbol id="i-new" viewBox="0 0 24 24"><path d="M14 3H6a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8z"/><path d="M14 3v5h5M12 12v6M9 15h6"/></symbol>
  <symbol id="i-trash" viewBox="0 0 24 24"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></symbol>
  <symbol id="i-rot" viewBox="0 0 24 24"><path d="M20 12a8 8 0 1 1-2.5-5.8M20 4v5h-5"/></symbol>
  <symbol id="i-pointer" viewBox="0 0 24 24"><path d="M5 3l14 7-6 2-2 6z"/></symbol>
  <symbol id="i-book" viewBox="0 0 24 24"><path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2zM4 19V5M9 7h6"/></symbol>
  <symbol id="i-expand" viewBox="0 0 24 24"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></symbol>
  <symbol id="i-collapse" viewBox="0 0 24 24"><path d="M9 4v5H4M15 4v5h5M9 20v-5H4M15 20v-5h5"/></symbol>
  <symbol id="i-edit" viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4Z"/></symbol>
  <symbol id="i-back" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></symbol>
 </defs>
</svg>

<div class="app">
 <header class="header">
  <button class="btn icon" id="backBtn" title="Back to hall"><svg class="i"><use href="#i-back"/></svg></button>
  <div class="brand">
   <div class="logo"><svg class="i"><use href="#i-book"/></svg></div>
   <div><h1>Library Floor Planner</h1><div class="sub">{{ $hall->name }} · {{ $hall->branch?->name }}</div></div>
  </div>
  <div class="divider"></div>
  <div class="floor"><label for="floorName">Floor</label><input id="floorName" type="text" value="Ground Floor"></div>
  <div class="spacer"></div>
  <div class="actions">
   <button class="btn icon" id="fullscreenBtn" title="Fullscreen"><svg class="i"><use href="#i-expand"/></svg></button>
   <button class="btn" id="lockToggleBtn" title="Toggle edit mode"><svg class="i"><use href="#i-edit"/></svg><span class="lbl" id="lockToggleLbl">Edit</span></button>
   <button class="btn" id="newBtn" title="New layout"><svg class="i"><use href="#i-new"/></svg><span class="lbl">New</span></button>
   <button class="btn" id="importBtn" title="Import JSON"><svg class="i"><use href="#i-up"/></svg><span class="lbl">Import JSON</span></button>
   <input id="fileInput" type="file" accept=".json,application/json" hidden>
   <button class="btn" id="exportBtn" title="Export JSON"><svg class="i"><use href="#i-down"/></svg><span class="lbl">Export JSON</span></button>
   <button class="btn primary keep" id="saveBtn" title="Save this floor plan (Ctrl+S)"><svg class="i"><use href="#i-save"/></svg><span class="lbl">Save</span></button>
  </div>
 </header>

 <aside class="sidebar left">
  <div class="sec">
   <h3>Objects</h3>
   <div class="tools" id="tools">
    <button class="tool" draggable="true" data-type="SEAT"><span class="sw" style="background:#dbeafe"><svg class="i"><use href="#i-seat"/></svg></span>Seat</button>
    <button class="tool" draggable="true" data-type="WALL"><span class="sw" style="background:#cbd5e1"><svg class="i"><use href="#i-wall"/></svg></span>Wall</button>
    <button class="tool" draggable="true" data-type="BLOCKER"><span class="sw" style="background:#fecaca"><svg class="i"><use href="#i-block"/></svg></span>Blocker</button>
    <button class="tool" draggable="true" data-type="WASHROOM"><span class="sw" style="background:#bfdbfe"><svg class="i"><use href="#i-wc"/></svg></span>Washroom</button>
    <button class="tool" draggable="true" data-type="ENTRY_GATE"><span class="sw" style="background:#bbf7d0"><svg class="i"><use href="#i-door"/></svg></span>Entry Gate</button>
    <button class="tool" draggable="true" data-type="RECEPTION"><span class="sw" style="background:#fef0c7"><svg class="i"><use href="#i-bell"/></svg></span>Reception</button>
    <button class="tool" draggable="true" data-type="OFFICE"><span class="sw" style="background:#e9d5ff"><svg class="i"><use href="#i-office"/></svg></span>Office</button>
    <button class="tool" draggable="true" data-type="OTHER"><span class="sw" style="background:#e5e7eb"><svg class="i"><use href="#i-plus"/></svg></span>Other</button>
   </div>
  </div>
  <div class="sec how">
   <h3>Tips</h3>
   <div class="hint"><ul>
    <li><b>Drag</b> an object onto the canvas, or click to add it.</li>
    <li>Drag on canvas to move; drag the blue corner to resize.</li>
    <li><kbd>Del</kbd> deletes, <kbd>Ctrl</kbd>+<kbd>Z</kbd> undoes.</li>
    <li>Export JSON to keep a file copy; import to continue later.</li>
   </ul></div>
  </div>
 </aside>

 <main class="main">
  <div class="toolbar">
   <div class="group">
    <button class="btn icon" id="undoBtn" title="Undo (Ctrl+Z)"><svg class="i"><use href="#i-undo"/></svg></button>
    <button class="btn" id="gridBtn" title="Toggle grid snapping"><svg class="i"><use href="#i-grid"/></svg><span id="gridLabel">Grid: ON</span></button>
   </div>
   <div class="divider"></div>
   <div class="group">
    <button class="btn icon" id="zoomOut" title="Zoom out"><svg class="i"><use href="#i-minus"/></svg></button>
    <button class="btn zoomlabel" id="zoomLabel" title="Reset to 100%">100%</button>
    <button class="btn icon" id="zoomIn" title="Zoom in"><svg class="i"><use href="#i-plus"/></svg></button>
    <button class="btn icon" id="fitBtn" title="Fit to screen"><svg class="i"><use href="#i-fit"/></svg></button>
   </div>
   <div class="divider"></div>
   <div class="group dims" style="display:flex;gap:6px;align-items:center">
    <label style="display:flex;align-items:center;gap:6px">W <input id="floorWidth" type="number" min="300" max="3000" value="1000" style="width:72px;margin:0"></label>
    <label style="display:flex;align-items:center;gap:6px">H <input id="floorHeight" type="number" min="250" max="2000" value="650" style="width:72px;margin:0"></label>
    <button class="btn" id="applySize">Apply</button>
    <button class="btn icon" id="orientBtn" title="Rotate orientation (landscape ⇄ portrait)"><svg class="i"><use href="#i-rot"/></svg></button>
   </div>
   <div class="spacer"></div>
   <span class="count" id="count">0 objects</span>
  </div>
  <div class="canvas-wrap" id="wrap">
   <div class="canvas-pad">
    <svg id="canvas" width="1000" height="650" viewBox="0 0 1000 650" xmlns="http://www.w3.org/2000/svg">
     <defs><pattern id="gridPattern" width="25" height="25" patternUnits="userSpaceOnUse"><path d="M25 0H0V25" fill="none" stroke="#dfe4ec" stroke-width="1"/></pattern></defs>
     <rect id="bg" width="1000" height="650" fill="url(#gridPattern)"/><g id="objects"></g>
    </svg>
   </div>
  </div>
  <div class="statusbar"><span id="status">Ready. Add an object to start designing.</span><span id="coords">Top view · canvas units</span></div>
 </main>

 <aside class="sidebar right">
  <div class="sec">
   <h3>Properties</h3>
   <div id="empty" class="empty"><div class="ico"><svg class="i"><use href="#i-pointer"/></svg></div><div class="hint">Select an object on the canvas to edit its properties.</div></div>
   <div id="properties" class="props hidden">
    <label class="full">Type<input id="pType" disabled></label>
    <label class="full">Name / label<input id="pName" type="text"></label>
    <label>X<input id="pX" type="number"></label>
    <label>Y<input id="pY" type="number"></label>
    <label>Width<input id="pW" type="number" min="10"></label>
    <label>Height<input id="pH" type="number" min="10"></label>
    <label class="full">Rotation (°)
     <div class="rotrow"><input id="pR" type="number"><button class="btn icon" id="rotBtn" title="Rotate 90°" type="button"><svg class="i"><use href="#i-rot"/></svg></button></div>
    </label>
    <label class="full">Details<input id="pDetail" type="text" placeholder="e.g. Male washroom"></label>
    <button class="btn danger full" id="deleteBtn"><svg class="i"><use href="#i-trash"/></svg>Delete object</button>
   </div>
  </div>
  @if ($canAllocate)
  <div class="sec hidden" id="seatAllocSec">
   <h3>Seat allocation</h3>
   <div id="seatAllocUnsaved" class="hint hidden">Save the floor plan first to allocate this seat.</div>
   <div id="seatAllocFree" class="hidden">
    <p class="hint" style="margin:0 0 10px">This seat is available.</p>
    <button type="button" class="btn primary full" id="seatAllocBtn" style="width:100%">Allocate to student</button>
   </div>
   <div id="seatAllocOccupied" class="hidden">
    <p class="hint" style="margin:0 0 10px">Allocated to <b id="seatAllocStudent"></b> · <span id="seatAllocShift"></span></p>
    <button type="button" class="btn danger full" id="seatReleaseBtn" style="width:100%">Release seat</button>
   </div>
  </div>
  @endif
 </aside>
</div>
<div class="toast" id="toast"></div>

@if ($canAllocate)
<div id="seatAllocModal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-slate-900/40" style="position:fixed">
    <div class="card w-full max-w-sm p-5 shadow-xl" style="background:#fff;border-radius:10px">
        <h3 class="font-semibold text-[15px]" style="margin:0">Allocate seat <span id="seatAllocModalNumber"></span></h3>
        <div class="mt-4 space-y-3" style="margin-top:16px">
            <label class="block" style="display:block;margin-bottom:10px">
                <span class="text-[12.5px] text-muted">Student</span>
                <select id="seatAllocStudentSelect" class="input w-full mt-1" style="width:100%;margin-top:4px" data-placeholder="Select student">
                    <option></option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->user->name }} ({{ $student->student_code }})</option>
                    @endforeach
                </select>
            </label>
            <label class="block" style="display:block;margin-bottom:10px">
                <span class="text-[12.5px] text-muted">Shift</span>
                <select id="seatAllocShiftSelect" class="input w-full mt-1" style="width:100%;margin-top:4px" data-placeholder="Select shift">
                    <option></option>
                    @foreach ($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time }}–{{ $shift->end_time }})</option>
                    @endforeach
                </select>
            </label>
            <label class="block" style="display:block">
                <span class="text-[12.5px] text-muted">Start date</span>
                <input type="date" id="seatAllocStartDate" class="input w-full mt-1" style="width:100%;margin-top:4px" value="{{ now()->toDateString() }}">
            </label>
        </div>
        <div class="flex justify-end gap-2 mt-5" style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px">
            <button type="button" class="btn" id="seatAllocModalCancel">Cancel</button>
            <button type="button" class="btn primary" id="seatAllocModalConfirm">Allocate</button>
        </div>
    </div>
</div>
@endif
</div>

<script>
(()=>{
const $=id=>document.getElementById(id),svg=$('canvas'),layer=$('objects'),wrap=$('wrap'),NS='http://www.w3.org/2000/svg';
let width=1000,height=650,grid=true,zoom=1,objects=[],selected=null,nextId=1,history=[],drag=null,toastT;
const sizes={SEAT:[46,46],WALL:[150,16],BLOCKER:[90,28],WASHROOM:[100,70],ENTRY_GATE:[100,28],RECEPTION:[125,60],OFFICE:[130,85],OTHER:[100,60]};
const fills={SEAT:'#dbeafe',WALL:'#64748b',BLOCKER:'#fecaca',WASHROOM:'#bfdbfe',ENTRY_GATE:'#bbf7d0',RECEPTION:'#fef0c7',OFFICE:'#e9d5ff',OTHER:'#e5e7eb'};
const CAN_ALLOCATE=@json($canAllocate);
const HALL_ID={{ $hall->id }};
const SEAT_META=@json($seatMeta);
const HALL_SHOW_URL=@json(route('halls.show', $hall->id));
let locked=@json($hasExistingPlan);
function saveHistory(){history.push(JSON.stringify(objects));if(history.length>40)history.shift();$('undoBtn').disabled=false}
function snap(n){return grid?Math.round(n/25)*25:Math.round(n)}
function say(s){$('status').textContent=s}
function toast(s){const t=$('toast');t.textContent=s;t.classList.add('show');clearTimeout(toastT);toastT=setTimeout(()=>t.classList.remove('show'),1800);say(s)}
function node(tag,attrs,parent=layer){const n=document.createElementNS(NS,tag);for(const [k,v] of Object.entries(attrs))n.setAttribute(k,v);parent.appendChild(n);return n}
function add(type,px,py){saveHistory();const [w,h]=sizes[type];const count=objects.filter(o=>o.type===type).length+1;
 const x=px===undefined?snap(80+(objects.length%7)*30):Math.max(0,Math.min(width-w,snap(px-w/2)));
 const y=py===undefined?snap(70+(objects.length%8)*30):Math.max(0,Math.min(height-h,snap(py-h/2)));
 const o={id:nextId++,type,name:type==='SEAT'?'S'+count:type.replaceAll('_',' '),x,y,w,h,rotation:0,detail:''};objects.push(o);selected=o.id;draw();say(type+' added')}
function draw(){
 layer.innerHTML='';
 for(const o of objects){
  const g=node('g',{class:'obj'+(selected===o.id?' selected':''),transform:`translate(${o.x} ${o.y}) rotate(${o.rotation} ${o.w/2} ${o.h/2})`,'data-id':o.id});
  // Seats mirror the Hall seat map's palette: white = available, slate = occupied, brand blue = selected.
  let seatFill=null,seatStroke=null,seatTextFill=null;
  if(o.type==='SEAT'){
   const occupied=!!SEAT_META[o.id]?.allocationId;
   if(selected===o.id){seatFill='#3453c4';seatStroke='#3453c4';seatTextFill='#fff'}
   else if(occupied){seatFill='#e2e8f0';seatStroke='#cbd5e1';seatTextFill='#263548'}
   else{seatFill='#fff';seatStroke='#cbd5e1';seatTextFill='#263548'}
  }
  const rect=node('rect',{class:'shape',x:0,y:0,width:o.w,height:o.h,rx:o.type==='SEAT'?8:3,fill:seatFill??fills[o.type],stroke:seatStroke??(o.type==='WALL'?'#475467':'#667085'),'stroke-width':o.type==='WALL'?1:1.4},g);
  if(o.type==='WALL')rect.setAttribute('fill','#64748b');
  if(o.type==='WALL'||o.type==='BLOCKER'){
   // Walls and blockers (incl. structural columns) are usually too small for
   // text to fit; show no label and use a native tooltip on hover instead.
   node('title',{},g).textContent=o.name+(o.detail?' · '+o.detail:'');
  }else{
   const text=node('text',{x:o.w/2,y:o.h/2+4,'text-anchor':'middle','font-size':Math.min(14,Math.max(9,o.w/7)),fill:seatTextFill??'#263548','pointer-events':'none'},g);
   text.textContent=o.type==='SEAT'?'♙ '+o.name:o.name;
  }
  if(selected===o.id)node('rect',{class:'handle',x:o.w-6,y:o.h-6,width:12,height:12,rx:2},g);
  g.addEventListener('pointerdown',e=>beginDrag(e,o));
 }
 $('count').textContent=objects.length+(objects.length===1?' object':' objects');
 $('undoBtn').disabled=locked||!history.length;
 const o=objects.find(x=>x.id===selected);$('empty').classList.toggle('hidden',!!o);$('properties').classList.toggle('hidden',!o);
 if(o){$('pType').value=o.type;$('pName').value=o.name;$('pX').value=o.x;$('pY').value=o.y;$('pW').value=o.w;$('pH').value=o.h;$('pR').value=o.rotation;$('pDetail').value=o.detail||''}
 for(const id of ['pName','pX','pY','pW','pH','pR','pDetail'])$(id).disabled=locked;
 $('rotBtn').disabled=locked;$('deleteBtn').disabled=locked;
 renderSeatPanel(o);
}
function renderSeatPanel(o){
 if(!CAN_ALLOCATE)return;
 const sec=$('seatAllocSec');
 if(!o||o.type!=='SEAT'){sec.classList.add('hidden');return}
 sec.classList.remove('hidden');
 const meta=SEAT_META[o.id];
 $('seatAllocUnsaved').classList.toggle('hidden',!!meta);
 $('seatAllocFree').classList.add('hidden');
 $('seatAllocOccupied').classList.add('hidden');
 if(!meta)return;
 if(meta.allocationId){
  $('seatAllocOccupied').classList.remove('hidden');
  $('seatAllocStudent').textContent=meta.studentName;
  $('seatAllocShift').textContent=meta.shiftName;
 }else{
  $('seatAllocFree').classList.remove('hidden');
 }
}
function point(e){const p=svg.createSVGPoint();p.x=e.clientX;p.y=e.clientY;return p.matrixTransform(svg.getScreenCTM().inverse())}
function beginDrag(e,o){e.preventDefault();e.stopPropagation();if(locked){selected=o.id;draw();return}const resize=e.target.classList.contains('handle');saveHistory();selected=o.id;draw();const p=point(e);drag={id:o.id,x:p.x,y:p.y,ox:o.x,oy:o.y,ow:o.w,oh:o.h,resize,moved:false};svg.setPointerCapture(e.pointerId)}
svg.addEventListener('pointermove',e=>{const p=point(e);$('coords').textContent='X '+Math.round(p.x)+' · Y '+Math.round(p.y);if(!drag)return;const o=objects.find(x=>x.id===drag.id);if(!o)return;const dx=p.x-drag.x,dy=p.y-drag.y;drag.moved=true;if(drag.resize){o.w=Math.max(15,Math.round(drag.ow+dx));o.h=Math.max(15,Math.round(drag.oh+dy))}else{o.x=Math.max(0,Math.min(width-o.w,snap(drag.ox+dx)));o.y=Math.max(0,Math.min(height-o.h,snap(drag.oy+dy)))}draw()});
function endDrag(){if(drag){if(!drag.moved){history.pop();draw()}drag=null}}
svg.addEventListener('pointerup',endDrag);svg.addEventListener('pointercancel',endDrag);
svg.addEventListener('click',e=>{if(e.target===$('bg')){selected=null;draw()}});
$('tools').addEventListener('click',e=>{if(locked)return;const b=e.target.closest('[data-type]');if(b)add(b.dataset.type)});
$('tools').addEventListener('dragstart',e=>{if(locked){e.preventDefault();return}const b=e.target.closest('[data-type]');if(!b)return;e.dataTransfer.setData('text/plain',b.dataset.type);e.dataTransfer.effectAllowed='copy'});
wrap.addEventListener('dragover',e=>{if(locked)return;e.preventDefault();e.dataTransfer.dropEffect='copy';wrap.classList.add('dropping')});
wrap.addEventListener('dragleave',e=>{if(e.target===wrap)wrap.classList.remove('dropping')});
wrap.addEventListener('drop',e=>{if(locked)return;e.preventDefault();wrap.classList.remove('dropping');const t=e.dataTransfer.getData('text/plain');if(!sizes[t])return;const p=point(e);add(t,p.x,p.y)});
const mapping={pName:'name',pX:'x',pY:'y',pW:'w',pH:'h',pR:'rotation',pDetail:'detail'};
for(const [id,key] of Object.entries(mapping))$(id).addEventListener('change',()=>{if(locked)return;const o=objects.find(x=>x.id===selected);if(!o)return;saveHistory();o[key]=['x','y','w','h','rotation'].includes(key)?Number($(id).value):$(id).value;if(['x','y'].includes(key))o[key]=Math.max(0,o[key]);if(['w','h'].includes(key))o[key]=Math.max(10,o[key]);draw()});
$('rotBtn').onclick=()=>{if(locked)return;const o=objects.find(x=>x.id===selected);if(!o)return;saveHistory();o.rotation=((o.rotation||0)+90)%360;draw()};
function del(){if(locked||selected===null)return;saveHistory();objects=objects.filter(o=>o.id!==selected);selected=null;draw();say('Object deleted')}
function undo(){if(locked)return;const prev=history.pop();if(prev!==undefined){objects=JSON.parse(prev);selected=null;draw();say('Undo complete')}else say('Nothing to undo')}
$('deleteBtn').onclick=del;$('undoBtn').onclick=undo;
document.addEventListener('keydown',e=>{
 const typing=/INPUT|SELECT|TEXTAREA/.test(document.activeElement.tagName);
 if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='s'){e.preventDefault();if(!locked)$('saveBtn').click();return}
 if(typing)return;
 if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='z'){e.preventDefault();undo()}
 else if(e.key==='Delete'||e.key==='Backspace'){e.preventDefault();del()}
});
function setGrid(){$('gridLabel').textContent='Grid: '+(grid?'ON':'OFF');$('gridBtn').classList.toggle('active',grid);$('bg').setAttribute('fill',grid?'url(#gridPattern)':'#fff')}
$('gridBtn').onclick=()=>{grid=!grid;setGrid()};
function applyDims(){svg.setAttribute('viewBox',`0 0 ${width} ${height}`);svg.setAttribute('width',width);svg.setAttribute('height',height);$('bg').setAttribute('width',width);$('bg').setAttribute('height',height);setZoom(zoom)}
$('applySize').onclick=()=>{if(locked)return;width=Math.max(300,Math.min(3000,Number($('floorWidth').value)||1000));height=Math.max(250,Math.min(2000,Number($('floorHeight').value)||650));$('floorWidth').value=width;$('floorHeight').value=height;applyDims();draw();say('Canvas dimensions updated')};
$('orientBtn').onclick=()=>{
 if(locked)return;
 saveHistory();
 const oldW=width,oldH=height;
 // Rotate every object's center 90° clockwise around the canvas, and turn the
 // object itself by the same 90° (via its own rotation field, w/h untouched),
 // so the whole layout turns together instead of just resizing the boundary.
 objects=objects.map(o=>{
  const cx=o.x+o.w/2,cy=o.y+o.h/2;
  const ncx=oldH-cy,ncy=cx;
  return {...o,x:ncx-o.w/2,y:ncy-o.h/2,rotation:((o.rotation||0)+90)%360};
 });
 width=Math.max(300,Math.min(3000,oldH));
 height=Math.max(250,Math.min(2000,oldW));
 $('floorWidth').value=width;$('floorHeight').value=height;
 selected=null;
 applyDims();draw();
 say('Layout rotated 90° — landscape ⇄ portrait');
};
function setZoom(z){zoom=Math.max(.25,Math.min(2,Math.round(z*100)/100));svg.style.width=(width*zoom)+'px';svg.style.height=(height*zoom)+'px';$('zoomLabel').textContent=Math.round(zoom*100)+'%'}
$('zoomIn').onclick=()=>setZoom(zoom+.1);$('zoomOut').onclick=()=>setZoom(zoom-.1);$('zoomLabel').onclick=()=>setZoom(1);
$('fitBtn').onclick=()=>setZoom(Math.min((wrap.clientWidth-64)/width,(wrap.clientHeight-64)/height));
wrap.addEventListener('wheel',e=>{if(!e.ctrlKey)return;e.preventDefault();setZoom(zoom+(e.deltaY<0?.1:-.1))},{passive:false});
function exportObject(){return {format:'library-floor-plan',version:1,floor:{name:$('floorName').value,width,height},objects}}
$('exportBtn').onclick=()=>{const blob=new Blob([JSON.stringify(exportObject(),null,2)],{type:'application/json'});const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='library-floor-plan.json';a.click();URL.revokeObjectURL(a.href);toast('JSON exported successfully')};
function load(data){width=Number(data.floor.width)||1000;height=Number(data.floor.height)||650;$('floorName').value=data.floor.name||'Imported Floor';$('floorWidth').value=width;$('floorHeight').value=height;objects=data.objects.map((o,i)=>({id:Number(o.id)||i+1,type:o.type||'OTHER',name:o.name||o.type||'Object',x:Number(o.x)||0,y:Number(o.y)||0,w:Number(o.w)||100,h:Number(o.h)||60,rotation:Number(o.rotation??o.r)||0,detail:o.detail||''}));nextId=Math.max(0,...objects.map(o=>o.id))+1;selected=null;applyDims();draw()}

/* --- Server integration (Laravel): save/load replace localStorage draft --- */
const SAVE_URL=@json(route('halls.floor-plan.save', $hall->id));
const CSRF=document.querySelector('meta[name="csrf-token"]').content;
$('saveBtn').onclick=async()=>{
 try{
  const res=await fetch(SAVE_URL,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},body:JSON.stringify(exportObject())});
  if(!res.ok){const err=await res.json().catch(()=>({message:'Save failed'}));throw new Error(err.message||'Save failed')}
  toast('Floor plan saved')
 }catch(err){toast('Could not save: '+err.message)}
};
if(CAN_ALLOCATE){
 const allocModal=$('seatAllocModal');
 $('seatAllocBtn')?.addEventListener('click',()=>{
  const o=objects.find(x=>x.id===selected);if(!o)return;
  $('seatAllocModalNumber').textContent=o.name;
  allocModal.classList.remove('hidden');allocModal.classList.add('flex');
 });
 $('seatAllocModalCancel')?.addEventListener('click',()=>{allocModal.classList.add('hidden');allocModal.classList.remove('flex')});
 $('seatAllocModalConfirm')?.addEventListener('click',async()=>{
  const o=objects.find(x=>x.id===selected);if(!o)return;
  const meta=SEAT_META[o.id];if(!meta)return;
  const studentId=$('seatAllocStudentSelect').value,shiftId=$('seatAllocShiftSelect').value,startDate=$('seatAllocStartDate').value;
  if(!studentId||!shiftId||!startDate){toast('Please fill in all fields.');return}
  try{
   const res=await fetch(`/halls/${HALL_ID}/seats/${meta.seatId}/allocate`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,Accept:'application/json'},body:JSON.stringify({student_id:studentId,shift_id:shiftId,start_date:startDate})});
   const body=await res.json().catch(()=>({}));
   if(!res.ok){toast(body.message||'Could not allocate seat.');return}
   meta.allocationId=body.allocation_id;meta.studentName=body.student_name;meta.shiftName=body.shift_name;
   allocModal.classList.add('hidden');allocModal.classList.remove('flex');
   toast('Seat allocated.');
   draw();
  }catch(err){toast('Could not allocate seat.')}
 });
 $('seatReleaseBtn')?.addEventListener('click',async()=>{
  const o=objects.find(x=>x.id===selected);if(!o)return;
  const meta=SEAT_META[o.id];if(!meta||!meta.allocationId)return;
  const result=await Swal.fire({title:'Release this seat?',text:`Seat ${o.name} will be released from ${meta.studentName}.`,icon:'warning',showCancelButton:true,confirmButtonText:'Release',confirmButtonColor:'#dc2626'});
  if(!result.isConfirmed)return;
  try{
   const res=await fetch(`/seat-allocations/${meta.allocationId}/release`,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,Accept:'application/json'}});
   const body=await res.json().catch(()=>({}));
   if(!res.ok){toast(body.message||'Could not release seat.');return}
   meta.allocationId=null;meta.studentName=null;meta.shiftName=null;
   toast('Seat released.');
   draw();
  }catch(err){toast('Could not release seat.')}
 });
}
$('importBtn').onclick=()=>{if(!locked)$('fileInput').click()};
$('fileInput').onchange=async e=>{
 const file=e.target.files[0];if(!file)return;
 try{
  const data=JSON.parse(await file.text());
  if(!data.floor||!Array.isArray(data.objects))throw Error('Invalid floor-plan JSON structure');
  const result=await Swal.fire({title:'Import this layout?',text:'It will replace the current canvas.',icon:'warning',showCancelButton:true,confirmButtonText:'Import'});
  if(!result.isConfirmed)return;
  saveHistory();load(data);toast('Layout imported successfully');
 }catch(err){Swal.fire({icon:'error',title:'Could not import JSON',text:err.message})}
 finally{$('fileInput').value=''}
};
$('newBtn').onclick=async()=>{
 if(locked)return;
 const result=await Swal.fire({title:'Start a new empty layout?',text:'Unsaved changes on this floor will be lost.',icon:'warning',showCancelButton:true,confirmButtonText:'Start new'});
 if(!result.isConfirmed)return;
 saveHistory();objects=[];selected=null;$('floorName').value='Ground Floor';draw();say('New layout created');
};

/* --- Edit lock: existing plans open read-only until "Edit" is clicked --- */
function applyLockState(){
 for(const id of ['newBtn','importBtn','saveBtn','applySize','orientBtn','floorWidth','floorHeight','floorName','undoBtn'])$(id).disabled=locked;
 document.querySelectorAll('#tools .tool').forEach(t=>t.disabled=locked);
 $('lockToggleLbl').textContent=locked?'Edit':'Lock';
 $('lockToggleBtn').classList.toggle('active',!locked);
 wrap.classList.toggle('readonly',locked);
 draw();
}
$('lockToggleBtn').onclick=()=>{locked=!locked;applyLockState();say(locked?'View only — click Edit to make changes':'Editing enabled')};

/* --- Fullscreen --- */
const fsBtn=$('fullscreenBtn'),fsIcon=fsBtn.querySelector('use');
function updateFsBtn(){const isFs=!!document.fullscreenElement;fsIcon.setAttribute('href',isFs?'#i-collapse':'#i-expand');fsBtn.title=isFs?'Exit fullscreen':'Fullscreen'}
fsBtn.onclick=()=>{if(!document.fullscreenElement){$('fp-root').requestFullscreen?.()}else{document.exitFullscreen?.()}};
document.addEventListener('fullscreenchange',updateFsBtn);
updateFsBtn();

/* --- Back to hall --- */
$('backBtn').onclick=()=>{location.href=HALL_SHOW_URL};

setGrid();
load(@json($plan));
applyLockState();
say(locked?'Loaded from database (view only — click Edit to make changes)':'Loaded from database');
draw();setZoom(1);
})();
</script>
@endsection
