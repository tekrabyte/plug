import React from "react";
import { useCart } from "../store/CartStore";
import CartItem from "./CartItem";
import { calcSubtotal, money } from "../utils";

export default function CartPanel() {
  const { cart } = useCart();
  const subtotal = calcSubtotal(cart);

  return (
    <div>
      <h3>Cart ({cart.length})</h3>
      {cart.length === 0 && <div>Cart empty</div>}
      <ul style={{ padding: 0, listStyle: "none" }}>
        {cart.map((i) => <CartItem key={i.id} item={i} />)}
      </ul>

      <div style={{ marginTop: 10 }}>
        <div>Subtotal: {money(subtotal)}</div>
        <div>Total: {money(subtotal)}</div>
      </div>
    </div>
  );
}
