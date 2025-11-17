import React, { useState } from "react";
import { useCart } from "../store/CartStore";
import { useTenant } from "../store/TenantStore";
import { createOrder } from "../api";
import { calcSubtotal } from "../utils";

export default function CheckoutButton() {
  const { cart, clear } = useCart();
  const { tenantId } = useTenant();
  const [loading, setLoading] = useState(false);

  async function checkout() {
    if (cart.length === 0) return alert("Cart empty");
    if (!tenantId) return alert("Tenant not assigned");

    setLoading(true);

    const subtotal = calcSubtotal(cart);

    try {
      const res = await createOrder({
        tenant_id: tenantId,
        type: "pos",
        subtotal,
        tax: 0,
        discount: 0,
        total: subtotal,
        items: cart.map((i) => ({
          variant_id: i.id,
          price: i.price,
          qty: i.qty,
        })),
      });

      alert("Order created: " + res.order_id);
      clear();
      // optionally open receipt in new window
      if (res.receipt_html) {
        const w = window.open('', '_blank', 'width=300,height=600');
        w.document.write(res.receipt_html);
        w.document.close();
      }
    } catch (e) {
      alert("Checkout failed: " + e.message);
    }

    setLoading(false);
  }

  return (
    <button
      onClick={checkout}
      disabled={loading}
      style={{ width: "100%", padding: 12, marginTop: 20 }}
    >
      {loading ? "Processing..." : "Checkout"}
    </button>
  );
}
