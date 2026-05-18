class Paginator {
  constructor(tableId, infoId, pagesId, perPage = 10) {
    this.tbody = document.querySelector(`#${tableId} tbody`);
    this.infoEl = document.getElementById(infoId);
    this.pagesEl = document.getElementById(pagesId);
    this.perPage = perPage;
    this.page = 1;
    this.render();
  }

  rows() {
    // Considera apenas linhas não escondidas por filtro externo
    return Array.from(this.tbody.querySelectorAll("tr"));
  }

  setPerPage(n) {
    this.perPage = n;
    this.page = 1;
    this.render();
  }

  goTo(p) {
    this.page = p;
    this.render();
  }

  render() {
    const all = this.rows();
    const total = all.length;

    if (total === 0) {
      this.infoEl.textContent = "0 registros";
      this.pagesEl.innerHTML = "";
      return;
    }

    const totalPages = Math.ceil(total / this.perPage);
    if (this.page > totalPages) this.page = totalPages;

    const start = (this.page - 1) * this.perPage;
    const end = Math.min(start + this.perPage, total);

    // Mostra/esconde linhas
    all.forEach((tr, i) => {
      tr.style.display = i >= start && i < end ? "" : "none";
    });

    // Info
    this.infoEl.textContent = `${start + 1}–${end} de ${total} registros`;

    // Botões de página
    this.pagesEl.innerHTML = "";
    const add = (
      label,
      page,
      active = false,
      disabled = false,
      ellipsis = false,
    ) => {
      const btn = document.createElement("button");
      btn.className =
        "pagination-btn" +
        (active ? " active" : "") +
        (ellipsis ? " ellipsis" : "");
      btn.textContent = label;
      btn.disabled = disabled;
      if (!disabled && !ellipsis && !active)
        btn.onclick = () => this.goTo(page);
      this.pagesEl.appendChild(btn);
    };

    add("‹", this.page - 1, false, this.page === 1);

    // Páginas com ellipsis
    const pages = this.pageRange(this.page, totalPages);
    let prev = null;
    for (const p of pages) {
      if (prev !== null && p - prev > 1) add("…", null, false, false, true);
      add(p, p, p === this.page);
      prev = p;
    }

    add("›", this.page + 1, false, this.page === totalPages);
  }

  pageRange(current, total) {
    // Sempre mostra: 1, last, current-1, current, current+1
    const set = new Set(
      [1, total, current - 1, current, current + 1].filter(
        (p) => p >= 1 && p <= total,
      ),
    );
    return [...set].sort((a, b) => a - b);
  }
}

const paginators = {
  produtos: new Paginator(
    "tabela-produtos", // id da <table>
    "info-produtos",
    "pages-produtos",
    10,
  ),
  culturas: new Paginator(
    "tabela-culturas", // id da <table>
    "info-culturas",
    "pages-culturas",
    10,
  ),
};
