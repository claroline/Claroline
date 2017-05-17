import {makeActionCreator} from '#/main/core/utilities/redux'

export const LIST_FILTER_ADD = 'LIST_FILTER_ADD'
export const LIST_FILTER_REMOVE = 'LIST_FILTER_REMOVE'
export const LIST_SORT_UPDATE = 'LIST_SORT_UPDATE'

export const LIST_RESET_SELECT = 'LIST_RESET_SELECT'
export const LIST_TOGGLE_SELECT = 'LIST_TOGGLE_SELECT'
export const LIST_TOGGLE_SELECT_ALL = 'LIST_TOGGLE_SELECT_ALL'

export const actions = {}

actions.addFilter = makeActionCreator(LIST_FILTER_ADD, 'property', 'value')
actions.removeFilter = makeActionCreator(LIST_FILTER_REMOVE, 'filter')

actions.updateSort = makeActionCreator(LIST_SORT_UPDATE, 'property')
actions.resetSelect = makeActionCreator(LIST_RESET_SELECT)
actions.toggleSelect = makeActionCreator(LIST_TOGGLE_SELECT, 'id')
actions.toggleSelectAll = makeActionCreator(LIST_TOGGLE_SELECT_ALL, 'items')
