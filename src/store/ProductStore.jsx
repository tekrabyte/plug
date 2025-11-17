import React, { createContext, useContext, useEffect, useState } from "react";
import toast from "react-hot-toast";
import { fetchProducts, fetchCategories } from "../api";

const ProductCtx = createContext();

export function ProductProvider({ children }) {
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const loadData = async () => {
      try {
        const [productsData, categoriesData] = await Promise.all([
          fetchProducts(),
          fetchCategories()
        ]);
        setProducts(productsData);
        setCategories(categoriesData);
      } catch (err) {
        console.error('Failed to load products:', err);
        setError(err.message || 'Failed to load products');
        toast.error('Failed to load products');
      } finally {
        setLoading(false);
      }
    };
    
    loadData();
  }, []);

  const refresh = async () => {
    setLoading(true);
    try {
      const [productsData, categoriesData] = await Promise.all([
        fetchProducts(),
        fetchCategories()
      ]);
      setProducts(productsData);
      setCategories(categoriesData);
      toast.success('Products refreshed');
    } catch (err) {
      toast.error('Failed to refresh products');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ProductCtx.Provider value={{ products, categories, loading, error, refresh }}>
      {children}
    </ProductCtx.Provider>
  );
}

export const useProducts = () => useContext(ProductCtx);
