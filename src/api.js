// api.js
async function apiRequest(url, options = {}) {
  const res = await fetch(url, {
    credentials: "include", // WP cookie auth
    headers: { "Content-Type": "application/json" },
    ...options,
  });

  if (!res.ok) {
    let errTxt = `HTTP ${res.status}`;
    try {
      const t = await res.text();
      errTxt += " — " + t;
    } catch {}
    throw new Error(errTxt);
  }

  return res.json();
}

export function fetchProducts() {
  return apiRequest("/wp-json/erp/v1/products");
}
export function fetchCategories() {
  return apiRequest("/wp-json/erp/v1/categories");
}

export function createOrder(payload) {
  return apiRequest("/wp-json/erp/v1/order/create", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}
