document.addEventListener("DOMContentLoaded", () => {
  const container = document.querySelector(".testimonials-container");
  if (!container) return;

  const wrapper = container.querySelector(".testimonial-wrapper");
  const cards = Array.from(container.querySelectorAll(".testimonial-card"));
  const dots = Array.from(container.querySelectorAll(".testimonial-dots .dot"));

  if (cards.length === 0) return;

  let current = 0;
  let timer = null;
  const INTERVAL = 5000;

  function show(i) {
    current = (i + cards.length) % cards.length;
    cards.forEach((c) => c.classList.remove("active"));
    dots.forEach((d) => d.classList.remove("active"));
    cards[current].classList.add("active");
    if (dots[current]) dots[current].classList.add("active");
  }

  function startAuto() {
    if (cards.length < 2) return;
    stopAuto();
    timer = setInterval(() => show(current + 1), INTERVAL);
  }

  function stopAuto() {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }

  dots.forEach((dot, i) => {
    dot.addEventListener("click", () => {
      show(i);
      startAuto();
    });
  });

  if (wrapper) {
    wrapper.addEventListener("mouseenter", stopAuto);
    wrapper.addEventListener("mouseleave", startAuto);
  }

  container.addEventListener("keydown", (e) => {
    if (e.key === "ArrowRight") {
      show(current + 1);
      startAuto();
    } else if (e.key === "ArrowLeft") {
      show(current - 1);
      startAuto();
    }
  });

  show(0);
  startAuto();
});

/* ===== FAQ toggle ===== */
document.addEventListener("DOMContentLoaded", () => {
  const faqControls = document.querySelectorAll(".faq .faq-control");

  faqControls.forEach((btn) => {
    btn.addEventListener("click", () => {
      const expanded = btn.getAttribute("aria-expanded") === "true";
      const panel = btn.parentElement.querySelector(".faq-panel");

      // refermer tous les autres (comportement “accordion”)
      document
        .querySelectorAll('.faq .faq-control[aria-expanded="true"]')
        .forEach((openBtn) => {
          if (openBtn !== btn) {
            openBtn.setAttribute("aria-expanded", "false");
            const other = openBtn.parentElement.querySelector(".faq-panel");
            if (other) other.hidden = true;
          }
        });

      // basculer celui-ci
      btn.setAttribute("aria-expanded", String(!expanded));
      if (panel) panel.hidden = expanded; // si déjà ouvert => ferme, sinon ouvre
    });
  });
});
