import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'
import {selectors as formSelectors} from '#/main/app/content/form/store'

const STORE_NAME = baseSelectors.STORE_NAME + '.teams'

const LIST_NAME = STORE_NAME+ '.list'
const FORM_NAME = STORE_NAME + '.current'

const store = (state) => get(state, STORE_NAME)

const currentId = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME)).id || null

const userTeams = createSelector(
  [store],
  (store) => store.userTeams
)

const hasTeam = (state, team) => {
  const teams = userTeams(state)

  return teams.find(t => t.id === team.id)
}

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  FORM_NAME,

  hasTeam,
  currentId
}
