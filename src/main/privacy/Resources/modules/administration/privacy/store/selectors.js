const STORE_NAME = 'privacy'
const store = (state) => state[STORE_NAME]

const getAdminRole = (state) => {
  const currentUser = state.security.currentUser
  let adminRole = false
  let roles = currentUser.roles
  for (let i = 0; i < roles.length; i++) {
    if (roles[i].name === 'ROLE_ADMIN') {
      adminRole = true
    }
  }
  return adminRole
};


export const selectors = {
  STORE_NAME,
  store,
  getAdminRole
}