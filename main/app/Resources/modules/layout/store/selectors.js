
const maintenance = state => state.maintenance.enabled
const maintenanceMessage = state => state.maintenance.message

const sidebar = state => state.sidebar.name

export const selectors = {
  maintenance,
  maintenanceMessage,
  sidebar
}
