import {createSelector} from 'reselect'

const context = (state) => state.context

const instance = (state) => state.instance

const parameters = createSelector(
  [instance],
  (instance) => instance.parameters || {}
)

const showResourceHeader = createSelector(
  [instance],
  (instance) => instance.showResourceHeader
)

const source = createSelector(
  [instance],
  (instance) => instance.source
)

export const selectors = {
  context,
  instance,
  parameters,
  showResourceHeader,
  source
}
