import {createSelector} from 'reselect'

const STORE_NAME = 'shortcut'
const FORM_NAME = STORE_NAME+'.form'

const resource = (state) => state[STORE_NAME]

const shortcut = createSelector(
  [resource],
  (resource) => resource.resource
)

const embeddedResource = createSelector(
  [shortcut],
  (shortcut) => shortcut.target
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  embeddedResource,
  shortcut
}
