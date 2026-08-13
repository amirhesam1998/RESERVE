import Konva from "konva";

// --- دیتا استراکچر داینامیک فضاها ---
let floors = [
  {
    id: "floor_1",
    name: "سالن اصلی",
    sections: [],
  },
];

let currentFloorId = "floor_1";
let currentTool = "move";

let selectedSeatIds = [];
let selectedSectionId = null;

let zoneHistory = {
  floor_1: { history: [], index: -1 },
};

let zoneIdToDelete = null;

// آیا این سالن از قبل چیدمانی روی بک‌اند ذخیره‌شده دارد؟ (برای انتخاب API ساخت/آپدیت)
let salonHasBackendLayout = false;

// --- خواندن دیتا از LocalStorage هنگام لود اولیه ---
function loadFromLocalStorage() {
  const savedData = localStorage.getItem("salon_layout_data");
  if (savedData) {
    try {
      const parsedFloors = JSON.parse(savedData);
      if (Array.isArray(parsedFloors) && parsedFloors.length > 0) {
        floors = parsedFloors;
        currentFloorId = floors[0].id;
      }
    } catch (e) {
      console.error("خطا در خواندن داده‌های LocalStorage:", e);
    }
  }
}

// --- دریافت چیدمان ذخیره‌شده‌ی سالن از بک‌اند و بارگذاری در state ---
// خروجی: true اگر سالن از قبل چیدمان ذخیره‌شده داشت و با موفقیت لود شد، در غیر این صورت false
async function loadSalonLayoutFromBackend(salonId) {
  try {
    const backendBaseUrl = import.meta.env.VITE_API_BASE_URL;
    const response = await fetch(`${backendBaseUrl}/api/salon/${salonId}`, {
      credentials: "include",
      headers: { Accept: "application/json" },
    });

    if (!response.ok) {
      throw new Error("Failed to load salon");
    }

    const result = await response.json();
    const salon = result.data;

    // اگر سالن هنوز هیچ فضایی ذخیره نکرده (اولین بار است)، به دیفالت برگردیم
    if (!salon.floors || salon.floors.length === 0) {
      salonHasBackendLayout = false;
      return false;
    }

    floors = salon.floors.map((floor) => ({
      id: `floor_${floor.id}`,
      name: floor.name,
      sections: (floor.sections || []).map((sec) => ({
        id: `sec_${sec.id}`,
        name: sec.name,
        x: sec.x,
        y: sec.y,
        seats: (sec.seats || []).map((seat) => ({
          id: `seat_${seat.id}`,
          row: seat.row,
          number: seat.number,
          x: seat.x,
          y: seat.y,
          price: seat.price || 0,
          type: seat.type || "regular",
          customText: seat.customText ?? null,
        })),
      })),
    }));

    currentFloorId = floors[0].id;
    salonHasBackendLayout = true;
    return true;
  } catch (error) {
    console.error("خطا در دریافت چیدمان سالن از سرور:", error);
    salonHasBackendLayout = false;
    return false;
  }
}

function getCurrentFloor() {
  return floors.find((f) => f.id === currentFloorId) || floors[0];
}

function getActiveSections() {
  return getCurrentFloor().sections;
}

function setActiveSections(newData) {
  getCurrentFloor().sections = newData;
}

// --- مدیریت تاریخچه Undo / Redo و ذخیره‌سازی خودکار در LocalStorage ---
function saveState() {
  // ۱. بروزرسانی سابقه داخلی
  if (!zoneHistory[currentFloorId]) {
    zoneHistory[currentFloorId] = { history: [], index: -1 };
  }
  let hData = zoneHistory[currentFloorId];
  if (hData.index < hData.history.length - 1) {
    hData.history = hData.history.slice(0, hData.index + 1);
  }
  const currentFloorObj = getCurrentFloor();
  hData.history.push(JSON.stringify(currentFloorObj.sections));
  hData.index++;

  // ۲. ذخیره خودکار وضعیت کامل کل فضاها در LocalStorage مرورگر
  try {
    localStorage.setItem("salon_layout_data", JSON.stringify(floors));
  } catch (e) {
    console.error("خطا در ذخیره‌سازی در LocalStorage:", e);
  }
}

function undo() {
  let hData = zoneHistory[currentFloorId];
  if (hData && hData.index > 0) {
    hData.index--;
    getCurrentFloor().sections = JSON.parse(hData.history[hData.index]);
    clearSelection();
    closeSidebar();
    renderSeats();
    localStorage.setItem("salon_layout_data", JSON.stringify(floors));
  }
}

function redo() {
  let hData = zoneHistory[currentFloorId];
  if (hData && hData.index < hData.history.length - 1) {
    hData.index++;
    getCurrentFloor().sections = JSON.parse(hData.history[hData.index]);
    clearSelection();
    closeSidebar();
    renderSeats();
    localStorage.setItem("salon_layout_data", JSON.stringify(floors));
  }
}

function clearSelection() {
  selectedSeatIds = [];
  selectedSectionId = null;
  updateToolbarContext();
}

// --- مدیریت سایدبار کشویی ---
function openSidebar() {
  const sidebar = document.getElementById("seat-sidebar");
  const infoBadge = document.getElementById("sidebar-seat-info");
  const labelInput = document.getElementById("seat-label-input");
  const priceInput = document.getElementById("seat-price-input");
  const typeSelect = document.getElementById("seat-type-select");

  if (!sidebar) return;

  const count = selectedSeatIds.length;
  if (count === 0) {
    closeSidebar();
    return;
  }

  let firstSeat = null;
  let sectionName = "";

  getActiveSections().forEach((sec) => {
    const found = sec.seats.find((s) => s.id === selectedSeatIds[0]);
    if (found) {
      firstSeat = found;
      sectionName = sec.name;
    }
  });

  if (infoBadge) {
    if (count === 1 && firstSeat) {
      infoBadge.textContent = `${sectionName} | ردیف ${firstSeat.row} - شماره ${firstSeat.number}`;
    } else {
      infoBadge.textContent = `${count} صندلی انتخاب شده است`;
    }
  }

  if (labelInput && firstSeat) {
    labelInput.value = count === 1 ? firstSeat.customText || "" : "";
  }

  if (priceInput && firstSeat) {
    priceInput.value = firstSeat.price || 0;
  }

  if (typeSelect && firstSeat) {
    typeSelect.value = firstSeat.type || "regular";
  }

  sidebar.classList.add("open");
}

function closeSidebar() {
  const sidebar = document.getElementById("seat-sidebar");
  if (sidebar) {
    sidebar.classList.remove("open");
  }
}

document
  .getElementById("close-sidebar-btn")
  ?.addEventListener("click", closeSidebar);

// همگام‌سازی متن/شماره سفارشی
document.getElementById("seat-label-input")?.addEventListener("input", (e) => {
  const newLabel = e.target.value;

  if (selectedSeatIds.length > 0) {
    getActiveSections().forEach((sec) => {
      sec.seats.forEach((seat) => {
        if (selectedSeatIds.includes(seat.id)) {
          seat.customText = newLabel;
        }
      });
    });
    saveState();
    renderSeats();
  }
});

// همگام‌سازی قیمت
document.getElementById("seat-price-input")?.addEventListener("input", (e) => {
  const newPrice = parseFloat(e.target.value) || 0;

  if (selectedSeatIds.length > 0) {
    getActiveSections().forEach((sec) => {
      sec.seats.forEach((seat) => {
        if (selectedSeatIds.includes(seat.id)) {
          seat.price = newPrice;
        }
      });
    });
    saveState();
  }
});

// همگام‌سازی نوع صندلی
document.getElementById("seat-type-select")?.addEventListener("change", (e) => {
  const newType = e.target.value;

  if (selectedSeatIds.length > 0) {
    getActiveSections().forEach((sec) => {
      sec.seats.forEach((seat) => {
        if (selectedSeatIds.includes(seat.id)) {
          seat.type = newType;
        }
      });
    });
    saveState();
    renderSeats();
  }
});

// --- راه‌اندازی بوم Konva ---
const stage = new Konva.Stage({
  container: "container",
  width: window.innerWidth,
  height: window.innerHeight,
  draggable: false,
});

const layer = new Konva.Layer();
stage.add(layer);

const GRID_SIZE = 50;
const SEAT_SIZE = 40;
const SECTION_PADDING = 60;

let lastValidPosition = { x: 0, y: 0 };
let lastValidSectionPosition = { x: 0, y: 0 };

function haveIntersection(r1, r2) {
  return !(
    r2.x >= r1.x + r1.width ||
    r2.x + r2.width <= r1.x ||
    r2.y >= r1.y + r1.height ||
    r2.y + r2.height <= r1.y
  );
}

// --- کادر انتخاب ویندوزی ---
let selectionRect = new Konva.Rect({
  fill: "rgba(52, 152, 219, 0.25)",
  stroke: "#3498db",
  strokeWidth: 1.5,
  dash: [4, 4],
  visible: false,
  listening: false,
});
layer.add(selectionRect);

// --- مدیریت راست‌کلیک برای Hand Tool (Pan Screen) ---
let isRightClickDragging = false;

// غیرفعال کردن منوی راست‌کلیک مرورگر روی بوم
stage.container().addEventListener("contextmenu", (e) => {
  e.preventDefault();
});

stage.on("mousedown", (e) => {
  if (e.evt.button === 2) {
    // دکمه راست موس
    e.evt.preventDefault();
    isRightClickDragging = true;
    stage.draggable(true);
    stage.startDrag();
    stage.container().style.cursor = "grabbing";
  }
});

window.addEventListener("mouseup", (e) => {
  if (e.button === 2 && isRightClickDragging) {
    isRightClickDragging = false;
    stage.stopDrag();
    stage.draggable(false);
    stage.container().style.cursor = "default";
  }
});

// --- مدیریت تولبارها ---
function updateToolbarContext() {
  const sectionTb = document.getElementById("section-toolbar");
  const seatTb = document.getElementById("seat-toolbar");

  if (selectedSectionId) {
    if (sectionTb) sectionTb.style.display = "flex";
    if (seatTb) seatTb.style.display = "none";

    const sec = getActiveSections().find((s) => s.id === selectedSectionId);
    if (sec) {
      const nameInput = document.getElementById("sec-name-input");
      if (nameInput) nameInput.value = sec.name;

      const regularSeats = sec.seats.filter((s) => s.row !== "آزاد");
      const rows = new Set(regularSeats.map((s) => s.row)).size;
      const maxCols =
        regularSeats.length > 0
          ? Math.max(...regularSeats.map((s) => s.number))
          : 0;

      const rowsInput = document.getElementById("sec-rows-input");
      const colsInput = document.getElementById("sec-cols-input");
      if (rowsInput) rowsInput.value = rows;
      if (colsInput) colsInput.value = maxCols;
    }
  } else if (selectedSeatIds.length > 0) {
    if (sectionTb) sectionTb.style.display = "none";
    if (seatTb) seatTb.style.display = "flex";
  } else {
    if (sectionTb) sectionTb.style.display = "none";
    if (seatTb) seatTb.style.display = "none";
  }
}

// --- شماره‌گذاری مجدد صندلی‌ها ---
function reorderSectionSeats(sec) {
  if (!sec || !sec.seats.length) return;

  const rowsMap = new Map();

  sec.seats.forEach((seat) => {
    seat.x = Math.round(seat.x / GRID_SIZE) * GRID_SIZE;
    seat.y = Math.round(seat.y / GRID_SIZE) * GRID_SIZE;

    if (!rowsMap.has(seat.y)) {
      rowsMap.set(seat.y, []);
    }
    rowsMap.get(seat.y).push(seat);
  });

  const sortedY = Array.from(rowsMap.keys()).sort((a, b) => a - b);

  sortedY.forEach((yVal, rowIndex) => {
    const rowSeats = rowsMap.get(yVal);
    rowSeats.sort((a, b) => a.x - b.x);

    rowSeats.forEach((seat, colIndex) => {
      seat.row = rowIndex + 1;
      seat.number = colIndex + 1;
    });
  });
}

// --- رندر صندلی‌ها ---
function renderSeats() {
  layer.destroyChildren();

  const currentSections = getActiveSections();

  currentSections.forEach((sec) => {
    let minX = 0,
      maxX = 300,
      minY = 0,
      maxY = 250;

    if (sec.seats.length > 0) {
      minX = Math.min(...sec.seats.map((s) => s.x));
      maxX = Math.max(...sec.seats.map((s) => s.x)) + SEAT_SIZE;
      minY = Math.min(...sec.seats.map((s) => s.y));
      maxY = Math.max(...sec.seats.map((s) => s.y)) + SEAT_SIZE;
    }

    const secWidth = maxX - minX + SECTION_PADDING * 2;
    const secHeight = maxY - minY + SECTION_PADDING * 2;
    const secStartX = minX - SECTION_PADDING;
    const secStartY = minY - SECTION_PADDING;

    const isSecSelected = selectedSectionId === sec.id;

    const sectionGroup = new Konva.Group({
      x: sec.x,
      y: sec.y,
      draggable: true,
      id: sec.id,
    });

    sectionGroup.setAttrs({
      secStartX: secStartX,
      secStartY: secStartY,
      secWidth: secWidth,
      secHeight: secHeight,
    });

    sectionGroup.on("click", (e) => {
      if (e.evt.button !== 0) return; // تنها با کلیک چپ
      if (e.target === sectionBox || e.target === sectionTitle) {
        e.cancelBubble = true;
        selectedSectionId = sec.id;
        selectedSeatIds = [];
        closeSidebar();
        updateToolbarContext();
        renderSeats();
      }
    });

    sectionGroup.on("dragstart", (e) => {
      if (e.target === sectionGroup) {
        selectedSectionId = sec.id;
        selectedSeatIds = [];
        closeSidebar();
        updateToolbarContext();
        lastValidSectionPosition = { x: e.target.x(), y: e.target.y() };
      }
    });

    sectionGroup.on("dragmove", (e) => {
      if (e.target !== sectionGroup) return;

      const movingX = e.target.x();
      const movingY = e.target.y();

      const movingBox = {
        x: movingX + secStartX,
        y: movingY + secStartY,
        width: secWidth,
        height: secHeight,
      };

      let isSectionCollision = false;

      layer.getChildren().forEach((otherGroup) => {
        if (
          otherGroup === selectionRect ||
          otherGroup === sectionGroup ||
          !otherGroup.id()?.startsWith("sec_")
        )
          return;

        const otherStartX = otherGroup.getAttr("secStartX") || 0;
        const otherStartY = otherGroup.getAttr("secStartY") || 0;
        const otherWidth = otherGroup.getAttr("secWidth") || 0;
        const otherHeight = otherGroup.getAttr("secHeight") || 0;

        const otherBox = {
          x: otherGroup.x() + otherStartX,
          y: otherGroup.y() + otherStartY,
          width: otherWidth,
          height: otherHeight,
        };

        if (haveIntersection(movingBox, otherBox)) {
          isSectionCollision = true;
        }
      });

      if (isSectionCollision) {
        e.target.x(lastValidSectionPosition.x);
        e.target.y(lastValidSectionPosition.y);
      } else {
        lastValidSectionPosition = { x: movingX, y: movingY };
      }
    });

    sectionGroup.on("dragend", (e) => {
      if (e.target === sectionGroup) {
        sec.x = e.target.x();
        sec.y = e.target.y();
        saveState();
      }
    });

    const sectionBox = new Konva.Rect({
      x: secStartX,
      y: secStartY,
      width: secWidth,
      height: secHeight,
      stroke: isSecSelected ? "#3498db" : "#FF6900",
      strokeWidth: isSecSelected ? 3 : 2,
      dash: isSecSelected ? [] : [6, 6],
      cornerRadius: 12,
      id: `__box_${sec.id}`,
    });

    sectionGroup.add(sectionBox);

    const sectionTitle = new Konva.Text({
      x: secStartX,
      y: secStartY - 35,
      text: sec.name,
      fontSize: 18,
      fontFamily: "Vazirmatn",
      fill: isSecSelected ? "#3498db" : "#FF6900",
      fontStyle: "bold",
      width: secWidth,
      align: "center",
      cursor: "pointer",
      id: `__title_${sec.id}`,
    });

    sectionGroup.add(sectionTitle);

    const rowsMap = {};
    sec.seats.forEach((seat) => {
      if (seat.row !== "آزاد") {
        if (!rowsMap[seat.row]) rowsMap[seat.row] = [];
        rowsMap[seat.row].push(seat);
      }
    });

    Object.keys(rowsMap).forEach((rowNum) => {
      const rowSeats = rowsMap[rowNum];
      if (rowSeats.length === 0) return;

      let minXSeat = rowSeats[0];
      let maxXSeat = rowSeats[0];

      rowSeats.forEach((s) => {
        if (s.x < minXSeat.x) minXSeat = s;
        if (s.x > maxXSeat.x) maxXSeat = s;
      });

      const labelY = minXSeat.y;

      sectionGroup.add(
        new Konva.Text({
          x: minXSeat.x - 40,
          y: labelY,
          text: `${rowNum}`,
          fontSize: 14,
          fontFamily: "Vazirmatn",
          fill: "#888888",
          width: 25,
          height: SEAT_SIZE,
          align: "right",
          verticalAlign: "middle",
        }),
      );

      sectionGroup.add(
        new Konva.Text({
          x: maxXSeat.x + SEAT_SIZE + 15,
          y: labelY,
          text: `${rowNum}`,
          fontSize: 14,
          fontFamily: "Vazirmatn",
          fill: "#888888",
          width: 25,
          height: SEAT_SIZE,
          align: "left",
          verticalAlign: "middle",
        }),
      );
    });

    sec.seats.forEach((seat) => {
      const isSelected = selectedSeatIds.includes(seat.id);

      const seatGroup = new Konva.Group({
        x: seat.x,
        y: seat.y,
        id: seat.id,
        draggable: true,
      });

      let strokeColor = "#BBBBBB";
      let fillColor = "#171717";

      if (seat.type === "vip") {
        strokeColor = "#F1C40F";
      } else if (seat.type === "wheelchair") {
        strokeColor = "#3498DB";
      }

      if (isSelected) {
        strokeColor = "#FF6900";
      }

      const rect = new Konva.Rect({
        width: SEAT_SIZE,
        height: SEAT_SIZE,
        fill: fillColor,
        cornerRadius: 6,
        stroke: strokeColor,
        strokeWidth: isSelected ? 3 : 2,
      });

      const text = new Konva.Text({
        text: seat.customText ? seat.customText : `${seat.number}`,
        fontSize: 14,
        fontFamily: "Vazirmatn",
        fill: seat.type === "vip" ? "#F1C40F" : "#BBBBBB",
        width: SEAT_SIZE,
        height: SEAT_SIZE,
        align: "center",
        verticalAlign: "middle",
      });

      seatGroup.add(rect);
      seatGroup.add(text);

      let startPositions = [];

      seatGroup.on("dragstart", (e) => {
        e.cancelBubble = true;
        selectedSectionId = null;
        if (!selectedSeatIds.includes(seat.id)) {
          selectedSeatIds = [seat.id];
        }
        updateToolbarContext();

        startPositions = selectedSeatIds.map((id) => {
          let sFound = null;
          getActiveSections().forEach((s) => {
            const found = s.seats.find((item) => item.id === id);
            if (found) sFound = found;
          });
          return { id: sFound.id, x: sFound.x, y: sFound.y };
        });

        lastValidPosition = { x: e.target.x(), y: e.target.y() };
      });

      seatGroup.on("dragmove", (e) => {
        e.cancelBubble = true;
        const originalSeat = startPositions.find((p) => p.id === seat.id);
        if (!originalSeat) return;

        const dx = e.target.x() - originalSeat.x;
        const dy = e.target.y() - originalSeat.y;

        let nextX_Raw = originalSeat.x + dx;
        let nextY_Raw = originalSeat.y + dy;

        let nextX_Snapped = !e.evt.altKey
          ? Math.round(nextX_Raw / GRID_SIZE) * GRID_SIZE
          : nextX_Raw;
        let nextY_Snapped = !e.evt.altKey
          ? Math.round(nextY_Raw / GRID_SIZE) * GRID_SIZE
          : nextY_Raw;

        let isCollisionDetected = false;

        sectionGroup.getChildren().forEach((otherGroup) => {
          if (
            otherGroup === selectionRect ||
            otherGroup.id()?.startsWith("__") ||
            otherGroup.id() === sec.id
          )
            return;

          if (!selectedSeatIds.includes(otherGroup.id())) {
            const otherRect = {
              x: otherGroup.x(),
              y: otherGroup.y(),
              width: SEAT_SIZE,
              height: SEAT_SIZE,
            };

            selectedSeatIds.forEach((movingId) => {
              const movingStartPos = startPositions.find(
                (p) => p.id === movingId,
              );
              if (movingStartPos) {
                let mX = movingStartPos.x + dx;
                let mY = movingStartPos.y + dy;
                if (!e.evt.altKey) {
                  mX = Math.round(mX / GRID_SIZE) * GRID_SIZE;
                  mY = Math.round(mY / GRID_SIZE) * GRID_SIZE;
                }

                if (
                  haveIntersection(
                    { x: mX, y: mY, width: SEAT_SIZE, height: SEAT_SIZE },
                    otherRect,
                  )
                ) {
                  isCollisionDetected = true;
                }
              }
            });
          }
        });

        if (isCollisionDetected) {
          e.target.x(lastValidPosition.x);
          e.target.y(lastValidPosition.y);
          return;
        }

        lastValidPosition = { x: nextX_Snapped, y: nextY_Snapped };

        sectionGroup.getChildren().forEach((group) => {
          if (selectedSeatIds.includes(group.id())) {
            const startPos = startPositions.find((p) => p.id === group.id());
            if (startPos) {
              if (group.id() === seat.id) {
                group.x(nextX_Snapped);
                group.y(nextY_Snapped);
              } else {
                let followerX = startPos.x + dx;
                let followerY = startPos.y + dy;
                if (!e.evt.altKey) {
                  followerX = Math.round(followerX / GRID_SIZE) * GRID_SIZE;
                  followerY = Math.round(followerY / GRID_SIZE) * GRID_SIZE;
                }
                group.x(followerX);
                group.y(followerY);
              }
            }
          }
        });
      });

      seatGroup.on("dragend", (e) => {
        e.cancelBubble = true;
        sectionGroup.getChildren().forEach((group) => {
          if (selectedSeatIds.includes(group.id())) {
            const targetSeat = sec.seats.find((s) => s.id === group.id());
            if (targetSeat) {
              targetSeat.x = group.x();
              targetSeat.y = group.y();
            }
          }
        });

        reorderSectionSeats(sec);
        saveState();
        renderSeats();
        openSidebar();
      });

      seatGroup.on("click", (e) => {
        if (e.evt.button !== 0) return; // تنها کلیک چپ
        e.cancelBubble = true;
        selectedSectionId = null;

        if (!e.evt.ctrlKey && !e.evt.metaKey) {
          selectedSeatIds = [seat.id];
        } else {
          if (selectedSeatIds.includes(seat.id)) {
            selectedSeatIds = selectedSeatIds.filter((id) => id !== seat.id);
          } else {
            selectedSeatIds.push(seat.id);
          }
        }

        updateToolbarContext();
        renderSeats();

        if (selectedSeatIds.length > 0) {
          openSidebar();
        } else {
          closeSidebar();
        }
      });

      sectionGroup.add(seatGroup);
    });

    layer.add(sectionGroup);
  });

  layer.add(selectionRect);
  selectionRect.moveToTop();

  layer.draw();
}

// --- کلیک روی بوم (Deselect) ---
stage.on("click", (e) => {
  if (e.evt.button === 0 && e.target === stage) {
    clearSelection();
    closeSidebar();
    renderSeats();
  }
});

// --- ساخت سکشن جدید ---
document.getElementById("generate-grid-btn")?.addEventListener("click", () => {
  const currentSections = getActiveSections();
  const newSecId = `sec_${currentFloorId}_${Date.now()};`;
  const defaultRows = 5;
  const defaultCols = 10;
  const newSeats = [];

  // ۱. محاسبه موقعیت Y جدید برای جلوگیری از همپوشانی با سکشن‌های قبلی
  let newX = 100;
  let newY = 100;

  if (currentSections.length > 0) {
    let maxY = 0;

    currentSections.forEach((sec) => {
      // محاسبه ارتفاع و محدوده پایین سکشن بر اساس صندلی‌ها
      let secSeatsMaxY = 0;
      if (sec.seats && sec.seats.length > 0) {
        secSeatsMaxY = Math.max(...sec.seats.map((s) => s.y)) + SEAT_SIZE;
      } else {
        secSeatsMaxY = 250; // ارتفاع پیش‌فرض برای سکشن‌های بدون صندلی
      }

      const absoluteBottomY = sec.y + secSeatsMaxY + SECTION_PADDING * 2;
      if (absoluteBottomY > maxY) {
        maxY = absoluteBottomY;
      }
    });

    // قرار دادن سکشن جدید در پایین‌ترین سکشن موجود با فاصله ۵۰ پیکسلی
    newY = maxY + 50;
  }

  // ۲. ساخت صندلی‌های شبکه جدید
  for (let r = 1; r <= defaultRows; r++) {
    for (let c = 1; c <= defaultCols; c++) {
      newSeats.push({
        id: `seat_${newSecId}_${r}_${c}_${Date.now()}`,
        row: r,
        number: c,
        x: c * GRID_SIZE,
        y: r * GRID_SIZE,
        price: 0,
        type: "regular",
      });
    }
  }

  // ۳. افزودن سکشن جدید با موقعیت X و Y محاسبه‌شده
  currentSections.push({
    id: newSecId,
    name: `سکشن ${currentSections.length + 1}`,
    x: newX,
    y: newY,
    seats: newSeats,
  });

  selectedSectionId = newSecId;
  closeSidebar();
  updateToolbarContext();
  saveState();
  renderSeats();
});

// --- افزودن تک صندلی ---
document.getElementById("add-seat-btn")?.addEventListener("click", () => {
  if (!selectedSectionId) return;

  const currentSections = getActiveSections();
  const sec = currentSections.find((s) => s.id === selectedSectionId);
  if (!sec) return;

  let maxX = 0;
  let maxY = GRID_SIZE;

  if (sec.seats.length > 0) {
    maxX = Math.max(...sec.seats.map((s) => s.x));
    const seatsInMaxX = sec.seats.filter((s) => s.x === maxX);
    maxY = Math.max(...seatsInMaxX.map((s) => s.y));
  }

  let newX = maxX + GRID_SIZE;
  let newY = maxY;

  if (newX > 10 * GRID_SIZE) {
    newX = GRID_SIZE;
    newY += GRID_SIZE;
  }

  const newSeatId = `seat_${sec.id}_single_${Date.now()}`;
  const newSeat = {
    id: newSeatId,
    row: 1,
    number: sec.seats.length + 1,
    x: newX,
    y: newY,
    price: 0,
    type: "regular",
  };

  sec.seats.push(newSeat);

  reorderSectionSeats(sec);

  selectedSectionId = null;
  selectedSeatIds = [newSeatId];

  saveState();
  updateToolbarContext();
  renderSeats();
  openSidebar();
});

// --- تغییر ابعاد سکشن ---
function updateSelectedSectionDimensions(newRows, newCols) {
  if (!selectedSectionId) return;

  const currentSections = getActiveSections();
  const sec = currentSections.find((s) => s.id === selectedSectionId);
  if (!sec) return;

  const existingSeatsMap = new Map();
  sec.seats.forEach((seat) => {
    existingSeatsMap.set(`${seat.row}_${seat.number}`, seat);
  });

  let newSeats = [];
  for (let r = 1; r <= newRows; r++) {
    for (let c = 1; c <= newCols; c++) {
      const key = `${r}_${c}`;
      const existingSeat = existingSeatsMap.get(key);

      if (existingSeat) {
        newSeats.push({
          ...existingSeat,
          x: c * GRID_SIZE,
          y: r * GRID_SIZE,
        });
      } else {
        newSeats.push({
          id: `seat_${sec.id}_${r}_${c}_${Date.now()}`,
          row: r,
          number: c,
          x: c * GRID_SIZE,
          y: r * GRID_SIZE,
          price: 0,
          type: "regular",
        });
      }
    }
  }

  sec.seats = newSeats;
  reorderSectionSeats(sec);
  saveState();
  renderSeats();
}

// --- مدیریت فضاها و درگ Drag & Drop منو ---
function renderZoneSelectOptions() {
  const optionsList = document.getElementById("options-list");
  const currentLabel = document.getElementById("current-zone-label");
  if (!optionsList) return;

  optionsList.innerHTML = "";

  floors.forEach((floor, index) => {
    const item = document.createElement("div");
    item.className = `dropdown-item ${floor.id === currentFloorId ? "active" : ""}`;
    item.setAttribute("draggable", "true");
    item.dataset.index = index;

    // ۱. ایجاد بخش عنوان فضا
    const titleSpan = document.createElement("span");
    titleSpan.className = "dropdown-item-title";
    titleSpan.textContent = floor.name;

    // ۲. ایجاد دکمه ضربدر حذف
    const deleteBtn = document.createElement("button");
    deleteBtn.className = "delete-zone-btn";
    deleteBtn.innerHTML = "&#10005;";
    deleteBtn.title = "حذف فضا";

    deleteBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      openDeleteZoneModal(floor.id, floor.name);
    });

    item.appendChild(titleSpan);
    item.appendChild(deleteBtn);

    item.addEventListener("click", () => {
      const currentFloor = getCurrentFloor();
      if (currentFloor) {
        currentFloor.sections = getActiveSections();
      }

      currentFloorId = floor.id;
      if (currentLabel) currentLabel.textContent = floor.name;
      closeZoneDropdown();
      clearSelection();
      closeSidebar();
      renderSeats();
      renderZoneSelectOptions();
    });

    // --- پیاده‌سازی Drag and Drop با انیمیشن ---
    item.addEventListener("dragstart", (e) => {
      e.dataTransfer.effectAllowed = "move";
      e.dataTransfer.setData("text/plain", index);
      item.classList.add("dragging");
    });

    item.addEventListener("dragend", () => {
      item.classList.remove("dragging");
    });

    item.addEventListener("dragover", (e) => {
      e.preventDefault();
      e.dataTransfer.dropEffect = "move";
      const draggingItem = optionsList.querySelector(".dragging");
      if (!draggingItem || draggingItem === item) return;

      const items = Array.from(optionsList.querySelectorAll(".dropdown-item"));
      const draggingIndex = items.indexOf(draggingItem);
      const targetIndex = items.indexOf(item);

      if (draggingIndex !== targetIndex) {
        const movedFloor = floors.splice(draggingIndex, 1)[0];
        floors.splice(targetIndex, 0, movedFloor);

        renderZoneSelectOptions();
        saveState();
      }
    });

    optionsList.appendChild(item);
  });

  const activeFloor = getCurrentFloor();
  if (currentLabel && activeFloor) {
    currentLabel.textContent = activeFloor.name;
  }
}

// --- مدیریت مودال حذف فضا ---
function openDeleteZoneModal(id, name) {
  zoneIdToDelete = id;
  const modal = document.getElementById("delete-zone-modal");
  const msg = document.getElementById("delete-modal-msg");
  if (msg) msg.textContent = `آیا از حذف فضای "${name}" اطمینان دارید؟`;
  if (modal) modal.style.display = "flex";
}

function closeDeleteZoneModal() {
  zoneIdToDelete = null;
  const modal = document.getElementById("delete-zone-modal");
  if (modal) modal.style.display = "none";
}

document
  .getElementById("cancel-delete-zone-btn")
  ?.addEventListener("click", closeDeleteZoneModal);

document
  .getElementById("confirm-delete-zone-btn")
  ?.addEventListener("click", () => {
    if (!zoneIdToDelete) return;

    if (floors.length <= 1) {
      alert("حداقل باید یک فضا در سالن وجود داشته باشد!");
      closeDeleteZoneModal();
      return;
    }

    floors = floors.filter((f) => f.id !== zoneIdToDelete);
    delete zoneHistory[zoneIdToDelete];

    if (currentFloorId === zoneIdToDelete) {
      currentFloorId = floors[0].id;
    }

    closeDeleteZoneModal();
    saveState();
    renderZoneSelectOptions();
    clearSelection();
    closeSidebar();
    renderSeats();
  });

function closeZoneDropdown() {
  const menu = document.getElementById("dropdown-menu");
  const inputWrapper = document.getElementById("add-zone-input-wrapper");
  const showBtn = document.getElementById("show-add-input-btn");
  const input = document.getElementById("new-zone-input");

  if (menu) menu.classList.remove("show");
  if (inputWrapper) inputWrapper.style.display = "none";
  if (showBtn) showBtn.style.display = "flex";
  if (input) input.value = "";
}

document.getElementById("dropdown-trigger")?.addEventListener("click", (e) => {
  e.stopPropagation();
  const menu = document.getElementById("dropdown-menu");
  menu?.classList.toggle("show");
});

window.addEventListener("click", (e) => {
  const dropdown = document.getElementById("zone-dropdown");
  if (dropdown && !dropdown.contains(e.target)) {
    closeZoneDropdown();
  }
});

document
  .getElementById("show-add-input-btn")
  ?.addEventListener("click", (e) => {
    e.stopPropagation();
    const showBtn = document.getElementById("show-add-input-btn");
    const inputWrapper = document.getElementById("add-zone-input-wrapper");
    const input = document.getElementById("new-zone-input");

    if (showBtn) showBtn.style.display = "none";
    if (inputWrapper) inputWrapper.style.display = "flex";
    if (input) input.focus();
  });

function handleCreateNewZone() {
  const input = document.getElementById("new-zone-input");
  const name = input?.value.trim();

  if (name) {
    const currentFloor = getCurrentFloor();
    if (currentFloor) {
      currentFloor.sections = getActiveSections();
    }

    const newFloorId = `floor_new_${Date.now()}`;
    floors.push({
      id: newFloorId,
      name: name,
      sections: [],
    });

    zoneHistory[newFloorId] = { history: [], index: -1 };
    currentFloorId = newFloorId;

    saveState();
    renderZoneSelectOptions();
    clearSelection();
    closeSidebar();
    renderSeats();
    closeZoneDropdown();
  }
}

document
  .getElementById("confirm-add-zone-btn")
  ?.addEventListener("click", (e) => {
    e.stopPropagation();
    handleCreateNewZone();
  });

document.getElementById("new-zone-input")?.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault();
    handleCreateNewZone();
  }
});

document
  .getElementById("cancel-add-zone-btn")
  ?.addEventListener("click", (e) => {
    e.stopPropagation();
    closeZoneDropdown();
  });

// --- ویرایش آنلاین نام فضای فعال ---
const editZoneBtn = document.getElementById("edit-zone-name-btn");
const editZoneInput = document.getElementById("edit-zone-input");
const triggerBtn = document.getElementById("dropdown-trigger");

editZoneBtn?.addEventListener("click", (e) => {
  e.stopPropagation();
  closeZoneDropdown();
  const currentFloor = getCurrentFloor();
  if (!currentFloor || !editZoneInput || !triggerBtn) return;

  triggerBtn.style.display = "none";
  editZoneBtn.style.display = "none";
  editZoneInput.style.display = "block";
  editZoneInput.value = currentFloor.name;
  editZoneInput.focus();
});

function saveZoneNameChange() {
  if (!editZoneInput || editZoneInput.style.display === "none") return;

  const newName = editZoneInput.value.trim();
  const currentFloor = getCurrentFloor();

  if (newName && currentFloor) {
    currentFloor.name = newName;
    renderZoneSelectOptions();
    saveState();
  }

  editZoneInput.style.display = "none";
  if (triggerBtn) triggerBtn.style.display = "flex";
  if (editZoneBtn) editZoneBtn.style.display = "flex";
}

editZoneInput?.addEventListener("blur", saveZoneNameChange);
editZoneInput?.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault();
    saveZoneNameChange();
  }
});

// --- لیسنرهای تولبار ---
document.getElementById("sec-name-input")?.addEventListener("input", (e) => {
  if (selectedSectionId) {
    const sec = getActiveSections().find((s) => s.id === selectedSectionId);
    if (sec) {
      sec.name = e.target.value;
      renderSeats();
    }
  }
});

document.getElementById("sec-rows-input")?.addEventListener("change", (e) => {
  const colsInput = document.getElementById("sec-cols-input");
  const newRows = parseInt(e.target.value) || 1;
  const newCols = parseInt(colsInput?.value) || 1;
  updateSelectedSectionDimensions(newRows, newCols);
});

document.getElementById("sec-cols-input")?.addEventListener("change", (e) => {
  const rowsInput = document.getElementById("sec-rows-input");
  const newRows = parseInt(rowsInput?.value) || 1;
  const newCols = parseInt(e.target.value) || 1;
  updateSelectedSectionDimensions(newRows, newCols);
});

document
  .getElementById("delete-section-toolbar-btn")
  ?.addEventListener("click", () => {
    if (selectedSectionId) {
      setActiveSections(
        getActiveSections().filter((s) => s.id !== selectedSectionId),
      );
      clearSelection();
      closeSidebar();
      saveState();
      renderSeats();
    }
  });

document
  .getElementById("delete-seat-toolbar-btn")
  ?.addEventListener("click", () => {
    if (selectedSeatIds.length > 0) {
      getActiveSections().forEach((sec) => {
        sec.seats = sec.seats.filter(
          (seat) => !selectedSeatIds.includes(seat.id),
        );
        reorderSectionSeats(sec);
      });
      clearSelection();
      closeSidebar();
      saveState();
      renderSeats();
    }
  });

// --- ابزار زوم ---
const scaleBy = 1.1;
stage.on("wheel", (e) => {
  e.evt.preventDefault();
  const oldScale = stage.scaleX();
  const pointer = stage.getPointerPosition();
  const mousePointTo = {
    x: (pointer.x - stage.x()) / oldScale,
    y: (pointer.y - stage.y()) / oldScale,
  };
  let newScale = e.evt.deltaY < 0 ? oldScale * scaleBy : oldScale / scaleBy;
  if (newScale < 0.2) newScale = 0.2;
  if (newScale > 5) newScale = 5;
  stage.scale({ x: newScale, y: newScale });
  stage.position({
    x: pointer.x - mousePointTo.x * newScale,
    y: pointer.y - mousePointTo.y * newScale,
  });
  stage.batchDraw();
});

// --- کادر انتخاب گروهی صندلی‌ها ---
let isSelecting = false;
let startPos = { x: 0, y: 0 };

stage.on("mousedown", (e) => {
  if (e.evt.button !== 0 || e.target !== stage) return;
  e.evt.preventDefault();

  const transform = stage.getAbsoluteTransform().copy().invert();
  startPos = transform.point(stage.getPointerPosition());

  selectionRect.setAttrs({
    x: startPos.x,
    y: startPos.y,
    width: 0,
    height: 0,
    visible: true,
  });

  selectionRect.moveToTop();
  isSelecting = true;
  layer.batchDraw();
});

stage.on("mousemove", (e) => {
  if (!isSelecting) return;
  e.evt.preventDefault();

  const transform = stage.getAbsoluteTransform().copy().invert();
  const currentPos = transform.point(stage.getPointerPosition());

  const x = Math.min(startPos.x, currentPos.x);
  const y = Math.min(startPos.y, currentPos.y);
  const width = Math.abs(currentPos.x - startPos.x);
  const height = Math.abs(currentPos.y - startPos.y);

  selectionRect.setAttrs({ x, y, width, height });
  layer.batchDraw();
});

stage.on("mouseup", (e) => {
  if (!isSelecting) return;
  e.evt.preventDefault();

  isSelecting = false;
  selectionRect.visible(false);

  const selBox = selectionRect.getClientRect();
  const selected = [];

  getActiveSections().forEach((sec) => {
    sec.seats.forEach((seat) => {
      const seatNode = stage.findOne(`#${seat.id}`);
      if (seatNode) {
        const seatBox = seatNode.getClientRect();
        if (Konva.Util.haveIntersection(selBox, seatBox)) {
          selected.push(seat.id);
        }
      }
    });
  });

  if (selected.length > 0) {
    selectedSectionId = null;
    selectedSeatIds = selected;
    updateToolbarContext();
    renderSeats();
    openSidebar();
  } else {
    layer.batchDraw();
  }
});

document.getElementById("undo-btn")?.addEventListener("click", undo);
document.getElementById("redo-btn")?.addEventListener("click", redo);

// --- استخراج آی‌دی سالن از URL سیستم لاراول ---
/* function getSalonIdFromURL() {
  const pathSegments = window.location.pathname.split("/");
  const salonIndex = pathSegments.indexOf("salon");
  if (salonIndex !== -1 && pathSegments[salonIndex + 1]) {
    return pathSegments[salonIndex + 1];
  }
  return null;
} */

/* function getSalonIdFromURL() {
  // 1. Check URL path (e.g., /salons/1/layout or /salon/1)
  const pathSegments = window.location.pathname.split("/").filter(Boolean);
  const salonIndex = pathSegments.findIndex(
    (seg) => seg === "salons" || seg === "salon",
  );
  if (salonIndex !== -1 && pathSegments[salonIndex + 1]) {
    return pathSegments[salonIndex + 1];
  }

  // 2. Check query parameters (e.g., http://localhost:5174/?salon=1 or ?salon_id=1)
  const urlParams = new URLSearchParams(window.location.search);
  return (
    urlParams.get("salon") || urlParams.get("salon_id") || urlParams.get("id")
  );
} */

/* // --- ارسال جیسون نهایی به ای‌پای‌آی لاراول با متد PUT و فیلد position ---
async function exportToBackendJSON() {
  const salonId = getSalonIdFromURL();

  if (!salonId) {
    alert("آی‌دی سالن از آدرس مرورگر (URL) دریافت نشد!");
    return;
  }

  const currentFloor = getCurrentFloor();
  if (currentFloor) {
    currentFloor.sections = getActiveSections();
  }

  const exportedFloors = floors.map((floor, index) => {
    return {
      id: floor.id,
      name: floor.name,
      position: index + 1,
      sections: (floor.sections || []).map((sec) => ({
        id: sec.id,
        name: sec.name,
        x: sec.x,
        y: sec.y,
        seats: (sec.seats || []).map((seat) => ({
          id: seat.id,
          row: seat.row,
          number: seat.number,
          x: seat.x,
          y: seat.y,
          price: seat.price || 0,
          type: seat.type || "regular",
          customText: seat.customText || null,
        })),
      })),
    };
  });

  const payload = {
    salon_id: salonId,
    floors: exportedFloors,
  };

  try {
    const response = await fetch(`/salon/${salonId}/layout`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
      },
      body: JSON.stringify(payload),
    });

    if (response.ok) {
      alert("چیدمان سالن با موفقیت ذخیره شد!");
    } else {
      alert("خطا در ذخیره چیدمان در سرور.");
    }
  } catch (error) {
    console.error("خطا در ارتباط با سرور:", error);
  }
} */

// --- ارسال جیسون نهایی به ای‌پای‌آی لاراول با متد PUT و فیلد position ---
async function exportToBackendJSON(salonId) {
  if (!salonId) {
    alert("آی‌دی سالن دریافت نشد!");
    return;
  }

  const currentFloor = getCurrentFloor();
  if (currentFloor) {
    currentFloor.sections = getActiveSections();
  }

  let hasEmptySection = false;
  let emptySectionName = "";

  for (const floor of floors) {
    if (floor.sections && floor.sections.length > 0) {
      for (const sec of floor.sections) {
        if (!sec.seats || sec.seats.length === 0) {
          hasEmptySection = true;
          emptySectionName = sec.name || "بدون نام";
          break;
        }
      }
    }
    if (hasEmptySection) break;
  }

  // اگر سکشن خالی پیدا شد، عملیات ذخیره متوقف می‌شود و پیام داده می‌شود
  if (hasEmptySection) {
    alert(
      `امکان ذخیره وجود ندارد! سکشن "${emptySectionName}" هیچ صندلی‌ای ندارد. لطفاً به آن صندلی اضافه کنید یا آن را حذف نمایید.`,
    );
    return;
  }

  // --- استخراج آی‌دی واقعیِ بک‌اند از روی شناسه‌ی محلی (مثلاً "sec_12" -> 12) ---
  // اگر شناسه با این الگو مطابقت نداشته باشد (یعنی رکورد فقط در همین جلسه‌ی ادیت
  // ساخته شده و هنوز روی سرور وجود ندارد)، null برمی‌گردد تا بک‌اند آن را جدید بسازد.
  function extractBackendId(prefixedId, prefix) {
    const pattern = new RegExp(`^${prefix}_(\\d+)$`);
    const match = String(prefixedId).match(pattern);
    return match ? parseInt(match[1], 10) : null;
  }

  // --- فیلتر کردن و آماده‌سازی داده‌ها برای ارسال به بک‌اند ---
  const exportedFloors = floors.map((floor, index) => {
    return {
      id: extractBackendId(floor.id, "floor"),
      name: floor.name,
      position: index + 1,
      sections: (floor.sections || []).map((sec) => ({
        id: extractBackendId(sec.id, "sec"),
        name: sec.name,
        x: sec.x,
        y: sec.y,
        seats: (sec.seats || []).map((seat) => ({
          id: extractBackendId(seat.id, "seat"),
          row: seat.row,
          number: seat.number,
          x: seat.x,
          y: seat.y,
          price: seat.price || 0,
          type: seat.type || "regular",
          customText: seat.customText || null,
        })),
      })),
    };
  });

  const payload = {
    salon_id: salonId,
    floors: exportedFloors,
  };

  // اگر این سالن از قبل روی بک‌اند چیدمان ذخیره‌شده داشت (یعنی هنگام لود صفحه
  // با loadSalonLayoutFromBackend گرفته شد)، باید به API آپدیت (sync) درخواست بزنیم،
  // نه API ساخت — وگرنه هر بار یک ست کامل تکراری از فضا/سکشن/صندلی ساخته می‌شود.
  const backendBaseUrl = import.meta.env.VITE_API_BASE_URL;
  const endpoint = salonHasBackendLayout
    ? `${backendBaseUrl}/api/salons/${salonId}/update/layout`
    : `${backendBaseUrl}/api/salons/${salonId}/layout`;

  try {
    console.log(
      "salonHasBackendLayout:",
      salonHasBackendLayout,
      "endpoint:",
      endpoint,
    );

    const response = await fetch(endpoint, {
      method: "PUT",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (response.ok) {
      localStorage.removeItem("salon_layout_data");
      alert("سالن و چیدمان آن با موفقیت ذخیره شد!");
      window.location.href = `${backendBaseUrl}/salons`;
    } else {
      alert("خطا در ذخیره چیدمان سالن در سرور.");
    }
  } catch (error) {
    console.error("خطا در ارتباط با سرور:", error);
  }
}

function getSalonIdFromURL() {
  const pathSegments = window.location.pathname.split("/").filter(Boolean);
  const salonIndex = pathSegments.findIndex(
    (seg) => seg === "salons" || seg === "salon",
  );
  if (salonIndex !== -1 && pathSegments[salonIndex + 1]) {
    return pathSegments[salonIndex + 1];
  }
  return null;
}

function checkSalonIdAndResetIfNeeded() {
  const currentSalonId = getSalonIdFromURL();
  const savedSalonId = localStorage.getItem("salon_layout_salon_id");

  if (currentSalonId && currentSalonId !== savedSalonId) {
    localStorage.removeItem("salon_layout_data");
    localStorage.setItem("salon_layout_salon_id", currentSalonId);
  }
}

// ۲. متصل کردن فراخوانی تابع به دکمه ذخیره در فرانت:
document.getElementById("save-btn")?.addEventListener("click", async () => {
  const salonId = getSalonIdFromURL();
  if (salonId) {
    await exportToBackendJSON(salonId);
  } else {
    alert("آی‌دی سالن از URL دریافت نشد!");
  }
});

// برای دسترسی داشتن فایل Blade به این تابع در حالت ESM/Vite
window.exportToBackendJSON = exportToBackendJSON;

// مقداردهی اولیه برنامه
async function initApp() {
  checkSalonIdAndResetIfNeeded();

  const salonId = getSalonIdFromURL();
  let loadedFromBackend = false;

  if (salonId) {
    loadedFromBackend = await loadSalonLayoutFromBackend(salonId);
  }

  // اگر سالن هنوز هیچ چیدمانی روی سرور ندارد (اولین بار ساخته شده)،
  // به‌عنوان fallback سراغ پیش‌نویس محلی (اگر وجود داشته باشد) می‌رویم.
  if (!loadedFromBackend) {
    loadFromLocalStorage();
  }

  renderZoneSelectOptions();
  saveState();
  renderSeats();
}

initApp();
