import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const WIDGET_PROGRESSION_LOAD_ITEMS = 'WIDGET_PROGRESSION_LOAD_ITEMS'

export const actions = {}

actions.loadWidgetProgressionItems = makeActionCreator(WIDGET_PROGRESSION_LOAD_ITEMS, 'items')

actions.loadProgressionItems = (workspaceId, levelMax = null) => ({
  [API_REQUEST]: {
    url: ['apiv2_progression_items_list', {workspace: workspaceId, levelMax: levelMax}],
    success: (response, dispatch) => dispatch(actions.loadWidgetProgressionItems(response))
  }
})
