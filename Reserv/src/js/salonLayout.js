// salonLayout.js

// تابع اصلی که بر اساس salonId دیتا را از لاراول گرفته و رندر می‌کند
export async function loadAndRenderSalonLayout(salonId, containerSelector) {
  const container = document.querySelector(containerSelector);
  if (!container) return;

  // خواندن آدرس پویا
  const baseUrl = import.meta.env.VITE_API_BASE_URL || window.location.origin;

  try {
    container.innerHTML = '<div class="loading">در حال دریافت چیدمان...</div>';

    // ۱. درخواست به متد show لاراول
    const response = await fetch(`${baseUrl}/api/salons/${salonId}`, {
      method: "GET",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      credentials: "include",
    });

    if (!response.ok) throw new Error("خطا در دریافت اطلاعات سالن");

    const result = await response.json();
    const salon = result.data;

    // ۲. رندر کردن چیدمان
    renderLayout(salon, container);
  } catch (error) {
    console.error("Layout Render Error:", error);
    container.innerHTML =
      '<div class="error">خطا در بارگذاری چیدمان سالن</div>';
  }
}

// تابع کمکی برای رندر المان‌های HTML
function renderLayout(salon, container) {
  container.innerHTML = `<h2>چیدمان سالن: ${salon.name}</h2>`;

  if (!salon.floors || salon.floors.length === 0) {
    container.innerHTML += "<p>هیچ چیدمانی ثبت نشده است.</p>";
    return;
  }

  // ساخت ساختار طبقات، بخش‌ها و صندلی‌ها
  salon.floors.forEach((floor) => {
    const floorEl = document.createElement("div");
    floorEl.className = "floor-box";
    floorEl.innerHTML = `<h3>طبقه: ${floor.name || floor.id}</h3>`;

    floor.sections?.forEach((section) => {
      const sectionEl = document.createElement("div");
      sectionEl.className = "section-box";
      sectionEl.innerHTML = `<h4>بخش: ${section.name || section.id}</h4>`;

      const seatsGrid = document.createElement("div");
      seatsGrid.className = "seats-grid";

      section.seats?.forEach((seat) => {
        const seatEl = document.createElement("div");
        seatEl.className = `seat-item ${seat.is_booked ? "booked" : "free"}`;
        seatEl.textContent = seat.number || seat.id;
        seatsGrid.appendChild(seatEl);
      });

      sectionEl.appendChild(seatsGrid);
      floorEl.appendChild(sectionEl);
    });

    container.appendChild(floorEl);
  });
}
