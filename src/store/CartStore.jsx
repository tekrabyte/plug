import React, { createContext, useContext, useState } from "react";

const CartCtx = createContext();

export function CartProvider({ children }) {
  const [cart, setCart] = useState([]);

  const add = (p) => {
    const exist = cart.find((i) => i.id === p.id);
    if (exist) setCart(cart.map((i) => i.id === p.id ? { ...i, qty: i.qty + 1 } : i));
    else setCart([...cart, { ...p, qty: 1 }]);
  };

  const inc = (id) => setCart(cart.map((i) => (i.id === id ? { ...i, qty: i.qty + 1 } : i)));
  const dec = (id) => setCart(cart.map((i) => (i.id === id ? { ...i, qty: i.qty - 1 } : i)).filter(i=>i.qty>0));
  const remove = (id) => setCart(cart.filter((i)=> i.id!==id));
  const clear = () => setCart([]);

  return (
    <CartCtx.Provider value={{ cart, add, inc, dec, remove, clear }}>
      {children}
    </CartCtx.Provider>
  );
}

export const useCart = () => useContext(CartCtx);
