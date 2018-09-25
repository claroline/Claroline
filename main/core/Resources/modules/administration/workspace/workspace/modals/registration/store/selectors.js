import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

export const STORE_NAME = 'workspaceRolesForm'

const roles = (state) => state.workspaces.registerableRoles

const selectedRole = (state) => formSelectors.data(formSelectors.form(state, STORE_NAME)).role

export const selectors = {
  STORE_NAME,
  roles,
  selectedRole
}
