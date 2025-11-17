import React from "react";
import { useProducts } from "../store/ProductStore";
import { useCart } from "../store/CartStore";

export default function ProductGrid() {
  const { products, loading } = useProducts();
  const { add } = useCart();

  if (loading) return <div>Loading products...</div>;

  return (
    <div>
      {products.map((p) => (
        <button
          key={p.id}
          onClick={() => add(p)}
          style={{
            display: "block",
            width: "100%",
            margin: "6px 0",
            padding: "10px",
            textAlign: "left",
          }}
        >
          {p.variant_sku} — {p.price}
        </button>
      ))}
    </div>
  );
}
