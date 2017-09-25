import {makeActionCreator} from '#/main/core/utilities/redux'

export const ITEM_UPDATE_TAGS = 'ITEM_UPDATE_TAGS'
export const actions = {}

actions.updateItemTags = makeActionCreator(ITEM_UPDATE_TAGS, 'id', 'tags')
