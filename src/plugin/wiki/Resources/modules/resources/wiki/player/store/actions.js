import {makeActionCreator} from '#/main/app/store/actions'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {API_REQUEST} from '#/main/app/api'
import {selectors} from '#/plugin/wiki/resources/wiki/store/selectors'

export const UPDATE_CURRENT_EDIT_SECTION = 'UPDATE_CURRENT_EDIT_SECTION'
export const UPDATE_CURRENT_PARENT_SECTION = 'UPDATE_CURRENT_PARENT_SECTION'
export const UPDATE_SECTION_VISIBILITY = 'UPDATE_SECTION_VISIBILITY'
export const LOADED_SECTION_TREE = 'LOADED_SECTION_TREE'
export const SECTION_DELETED = 'SECTION_DELETED'

export const actions = {}

actions.updateCurrentEditSection = makeActionCreator(UPDATE_CURRENT_EDIT_SECTION, 'sectionId')
actions.updateCurrentParentSection = makeActionCreator(UPDATE_CURRENT_PARENT_SECTION, 'sectionId')
actions.updateSectionVisibility = makeActionCreator(UPDATE_SECTION_VISIBILITY, 'sectionId', 'section')
actions.loadedSectionTree = makeActionCreator(LOADED_SECTION_TREE, 'sectionTree')
actions.sectionDeleted = makeActionCreator(SECTION_DELETED, 'sectionId', 'children')

actions.setCurrentParentSection = (parentId = null) => (dispatch) => {
  if (parentId) {
    dispatch(actions.updateCurrentParentSection(parentId))
  } else {
    dispatch(actions.updateCurrentParentSection(null))
  }
  dispatch(formActions.resetForm(selectors.STORE_NAME + '.sections.currentSection', {}, true))
}

actions.setCurrentEditSection = (section = null) => (dispatch) => {
  if (section) {
    dispatch(actions.updateCurrentEditSection(section.id))
    dispatch(formActions.resetForm(selectors.STORE_NAME + '.sections.currentSection', section, false))
  } else {
    dispatch(actions.updateCurrentEditSection(null))
    dispatch(formActions.resetForm(selectors.STORE_NAME + '.sections.currentSection', {}, true))
  }
}

actions.setSectionVisibility = (id = null, visible = true) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_wiki_section_set_visibility', {id}],
        request: {
          method: 'PUT',
          body: JSON.stringify({
            visible: visible
          })
        },
        success: (data) => dispatch(actions.updateSectionVisibility(id, data))
      }
    })
  }
}

actions.deleteSection = (wikiId = null, id = null, children = false) => dispatch => {
  if (wikiId && id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_wiki_section_delete', {wikiId}],
        request: {
          method: 'DELETE',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            ids: new Array(id),
            children: children,
            permanently: false
          })
        },
        success: (data, dispatch) => {
          dispatch(actions.sectionDeleted(id, children))
        }
      }
    })
  }
}

actions.fetchSectionTree = (wikiId = null) => dispatch => {
  if (wikiId) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_wiki_section_tree', {wikiId}],
        request: {
          method: 'GET'
        },
        success: (data, dispatch) => {
          dispatch(actions.loadedSectionTree(data))
        }
      }
    })
  }
}
