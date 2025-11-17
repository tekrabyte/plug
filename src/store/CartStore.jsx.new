import React, { createContext, useContext, useState } from "react";
import toast from "react-hot-toast";

const CartCtx = createContext();

export function CartProvider({ children }) {
  const [cart, setCart] = useState([]);

  const add = (p) => {
    const exist = cart.find((i) => i.id === p.id);
    if (exist) {
      setCart(cart.map((i) => i.id === p.id ? { ...i, qty: i.qty + 1 } : i));
      toast.success(`Added ${p.name} to cart`);
    } else {
      setCart([...cart, { ...p, qty: 1 }]);
      toast.success(`${p.name} added to cart`);
    }
  };

  const inc = (id) => {
    setCart(cart.map((i) => (i.id === id ? { ...i, qty: i.qty + 1 } : i)));
  };
  
  const dec = (id) => {
    const item = cart.find(i => i.id === id);
    if (item && item.qty === 1) {
      toast.success('Item removed from cart');
    }
    setCart(cart.map((i) => (i.id === id ? { ...i, qty: i.qty - 1 } : i)).filter(i=>i.qty>0));
  };
  
  const remove = (id) => {
    const item = cart.find(i => i.id === id);
    if (item) {
      toast.success(`${item.name} removed from cart`);
    }
    setCart(cart.filter((i)=> i.id!==id));
  };
  
  const clear = () => {
    setCart([]);
  };

  return (
    <CartCtx.Provider value={{ cart, add, inc, dec, remove, clear }}>
      {children}
    </CartCtx.Provider>
  );
}

export const useCart = () => useContext(CartCtx);