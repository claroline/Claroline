export const locale = () => document.getElementById('homeLocale') ? document.getElementById('homeLocale').innerHTML: null
export const web = (path = '') => document.getElementById('homeAsset') ? document.getElementById('homeAsset').innerHTML + path: null
