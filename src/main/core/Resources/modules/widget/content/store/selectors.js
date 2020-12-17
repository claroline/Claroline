import {createSelector} from 'reselect'

const context = (state) => state.currentContext

const instance = (state) => state.instance

const parameters = createSelector(
  [instance],
  (instance) => instance.parameters || {}
)

const source = createSelector(
  [instance],
  (instance) => instance.source
)

export const selectors = {
  context,
  instance,
  parameters,
  source
}
