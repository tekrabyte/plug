import React, { createContext, useContext, useEffect, useState } from "react";
import { fetchProducts } from "../api";

const ProductCtx = createContext();

export function ProductProvider({ children }) {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchProducts()
      .then(setProducts)
      .catch(()=>{})
      .finally(() => setLoading(false));
  }, []);

  return (
    <ProductCtx.Provider value={{ products, loading }}>
      {children}
    </ProductCtx.Provider>
  );
}

export const useProducts = () => useContext(ProductCtx);
