export const money = (n) => Number(n).toLocaleString("id-ID");

export const calcSubtotal = (cart) => cart.reduce((s,i)=>s + i.price*i.qty, 0);
