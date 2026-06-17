const header = document.querySelector("[data-header]");
const menuButton = document.querySelector(".menu-toggle");
const navPanel = document.querySelector(".nav-panel");
const backTop = document.querySelector(".back-top");

function updateChrome() {
  header?.classList.toggle("scrolled", window.scrollY > 10);
  backTop?.classList.toggle("visible", window.scrollY > 600);
}

window.addEventListener("scroll", updateChrome, { passive: true });
updateChrome();

menuButton?.addEventListener("click", () => {
  const isOpen = document.body.classList.toggle("menu-open");
  menuButton.setAttribute("aria-expanded", String(isOpen));
  menuButton.setAttribute("aria-label", isOpen ? "Close menu" : "Open menu");
});

navPanel?.addEventListener("click", (event) => {
  if (event.target.matches("a")) {
    document.body.classList.remove("menu-open");
    menuButton?.setAttribute("aria-expanded", "false");
    menuButton?.setAttribute("aria-label", "Open menu");
  }
});

backTop?.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));

document.querySelectorAll("[data-accordion]").forEach((accordion) => {
  accordion.querySelectorAll("button").forEach((button) => {
    button.addEventListener("click", () => {
      const panel = button.nextElementSibling;
      const isOpen = button.getAttribute("aria-expanded") === "true";
      button.setAttribute("aria-expanded", String(!isOpen));
      panel?.classList.toggle("open", !isOpen);
    });
  });
});

document.querySelectorAll("[data-slider]").forEach((slider) => {
  const slides = Array.from(slider.querySelectorAll(".testimonial-slide"));
  const previous = slider.querySelector("[data-prev]");
  const next = slider.querySelector("[data-next]");
  let active = 0;

  function showSlide(index) {
    if (!slides.length) return;
    active = (index + slides.length) % slides.length;
    slides.forEach((slide, slideIndex) => slide.classList.toggle("active", slideIndex === active));
  }

  previous?.addEventListener("click", () => showSlide(active - 1));
  next?.addEventListener("click", () => showSlide(active + 1));
  setInterval(() => showSlide(active + 1), 7000);
});

const revealObserver = "IntersectionObserver" in window
  ? new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.14 })
  : null;

document.querySelectorAll(".reveal").forEach((element) => {
  if (revealObserver) revealObserver.observe(element);
  else element.classList.add("visible");
});

const counterObserver = "IntersectionObserver" in window
  ? new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        animateCounter(entry.target);
        counterObserver.unobserve(entry.target);
      });
    }, { threshold: 0.4 })
  : null;

document.querySelectorAll("[data-counter]").forEach((counter) => {
  if (counterObserver) counterObserver.observe(counter);
  else animateCounter(counter);
});

function animateCounter(counter) {
  const target = Number(counter.dataset.counter || 0);
  const suffix = counter.dataset.suffix || "";
  const duration = 1200;
  const start = performance.now();

  function tick(now) {
    const progress = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const value = Math.round(target * eased);
    counter.textContent = `${target >= 1000 ? value.toLocaleString() : value}${suffix}`;
    if (progress < 1) requestAnimationFrame(tick);
  }

  requestAnimationFrame(tick);
}

async function hydrateCsrfTokens() {
  const forms = document.querySelectorAll("[data-form]");
  if (!forms.length || location.protocol === "file:") return;

  try {
    const response = await fetch("backend/config.php?csrf=1", { credentials: "same-origin" });
    if (!response.ok) return;
    const data = await response.json();
    forms.forEach((form) => {
      const token = form.querySelector('input[name="csrf_token"]');
      if (token && data.csrf_token) token.value = data.csrf_token;
    });
  } catch {
    // PHP may be unavailable during static preview; forms still validate client-side.
  }
}

hydrateCsrfTokens();

document.querySelectorAll("[data-form]").forEach((form) => {
  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const status = form.querySelector(".form-status");
    let isValid = true;

    form.querySelectorAll("[required]").forEach((field) => {
      const valid = field.checkValidity();
      field.classList.toggle("error", !valid);
      if (!valid) isValid = false;
    });

    if (!isValid) {
      if (status) status.textContent = "Please complete the highlighted fields.";
      return;
    }

    if (status) status.textContent = "Sending...";

    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        credentials: "same-origin",
        headers: { "Accept": "application/json" }
      });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message || "Unable to send your message.");
      form.reset();
      await hydrateCsrfTokens();
      if (status) status.textContent = data.message || "Thank you. Our team will contact you soon.";
    } catch (error) {
      if (status) status.textContent = error.message || "Please call the clinic to complete your message.";
    }
  });
});
