import axios from 'axios';
export const api = axios.create({baseURL:'/',withCredentials:true,withXSRFToken:true,headers:{Accept:'application/json'}});
export const errorMessage = (error) => {
  const data=error?.response?.data;
  return Object.values(data?.errors||{})?.flat()?.[0] || data?.message || 'Une erreur est survenue. Réessayez.';
};

let workspaceId = null;
export const setWorkspaceId = (id) => { workspaceId=id || null; };
api.interceptors.request.use(config => {
  if (config.url?.startsWith('/api/') && !config.url.startsWith('/api/workspaces') && !config.url.startsWith('/api/ai/')) {
    config.params={...config.params,...(workspaceId?{workspace_id:workspaceId}:{})};
  }
  return config;
});
