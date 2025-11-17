import React from "react";
import ProductGrid from "./components/ProductGrid";
import CartPanel from "./components/CartPanel";
import CheckoutButton from "./components/CheckoutButton";

import { ProductProvider } from "./store/ProductStore";
import { CartProvider } from "./store/CartStore";
import { TenantProvider } from "./store/TenantStore";

export default function App() {
  return (
    <TenantProvider>
      <ProductProvider>
        <CartProvider>
          <div style={{ padding: 20, fontFamily: "Arial" }}>
            <h2>ERP POS</h2>
            <ProductGrid />
            <CartPanel />
            <CheckoutButton />
          </div>
        </CartProvider>
      </ProductProvider>
    </TenantProvider>
  );
}
