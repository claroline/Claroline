import {createSelector} from 'reselect'
import {selectors as cursusSelectors} from '#/plugin/cursus/tools/trainings/store/selectors'

const STORE_NAME = cursusSelectors.STORE_NAME + '.quota'
const LIST_NAME = STORE_NAME + '.quotas'
const FORM_NAME = STORE_NAME + '.quotaForm'

const store = (state) => state[cursusSelectors.STORE_NAME].quota

const quota = createSelector(
  store,
  (state) => state.quota
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  FORM_NAME,

  quota
}
