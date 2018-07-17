import {makeActionCreator} from '#/main/app/store/actions'

export const ITEM_UPDATE_TAGS = 'ITEM_UPDATE_TAGS'
export const actions = {}

actions.updateItemTags = makeActionCreator(ITEM_UPDATE_TAGS, 'id', 'tags')
