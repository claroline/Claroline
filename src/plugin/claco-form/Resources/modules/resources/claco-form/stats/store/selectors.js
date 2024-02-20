import get from 'lodash/get'

import {selectors as baseSelectors} from '#/plugin/claco-form/resources/claco-form/store/selectors'

const stats = (state) => get(state, baseSelectors.STORE_NAME+'.stats')

export const selectors = {
  stats
}
