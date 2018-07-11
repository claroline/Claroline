import {actions as formActions} from '#/main/core/data/form/actions'

// action creators
export const actions = {}

actions.deleteTab = (currentTabIndex, tabs, push) => (dispatch) => {

  const newTabs = tabs.slice(0)
  newTabs.splice(currentTabIndex, 1)
  dispatch(formActions.updateProp('editor', tabs, newTabs))
  push('/edit')
}
