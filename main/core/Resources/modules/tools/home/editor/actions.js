
import {actions as formActions} from '#/main/core/data/form/actions'

// action creators
export const actions = {}

actions.deleteTab = (currentTabIndex, editorTabs, push) => (dispatch) => {
  const newTabs = editorTabs.slice(0)
  newTabs.splice(currentTabIndex, 1)
  dispatch(formActions.updateProp('editor', 'tabs', newTabs))
  push('/edit')
}

actions.changePosition = (editorTabs, currentTab, newPosition) => {
  const newEditorTabs = editorTabs.slice(0)
  const oldPosition = currentTab.position

  if (newPosition > oldPosition) {
    newEditorTabs.forEach(tab => {
      if(tab.position >= newPosition) {
        tab.position++
      }
    })
  } else {
    newEditorTabs.forEach(tab => {
      if(newPosition >= tab.position) {
        tab.position--
      }
    })
  }
  return newEditorTabs
}
