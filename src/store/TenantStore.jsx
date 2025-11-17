import React, { createContext, useContext } from "react";

const TenantCtx = createContext();

export function TenantProvider({ children }) {
  const tenantId = window.ERP_TENANT_ID || null;
  return (
    <TenantCtx.Provider value={{ tenantId }}>
      {children}
    </TenantCtx.Provider>
  );
}

export const useTenant = () => useContext(TenantCtx);
