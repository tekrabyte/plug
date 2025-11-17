import React from "react";
import { useCart } from "../store/CartStore";

export default function CartItem({ item }) {
  const { inc, dec, remove } = useCart();

  return (
    <li style={{ border: "1px solid #ddd", padding: 10, marginBottom: 5 }}>
      <div>{item.variant_sku} — {item.price}</div>
      <div style={{ marginTop: 5 }}>
        <button onClick={() => dec(item.id)}>-</button>
        <span style={{ margin: "0 10px" }}>{item.qty}</span>
        <button onClick={() => inc(item.id)}>+</button>
        <button style={{ marginLeft: 20, color: "red" }} onClick={() => remove(item.id)}>Remove</button>
      </div>
    </li>
  );
}
