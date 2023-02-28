import {makeActionCreator} from '#/main/app/store/actions'

export const PROFILE_FACET_OPEN   = 'PROFILE_FACET_OPEN'
export const PROFILE_FACET_ADD    = 'PROFILE_FACET_ADD'
export const PROFILE_FACET_UPDATE = 'PROFILE_FACET_UPDATE'
export const PROFILE_FACET_REMOVE = 'PROFILE_FACET_REMOVE'

export const actions = {}

actions.openFacet = makeActionCreator(PROFILE_FACET_OPEN, 'id')
actions.addFacet = makeActionCreator(PROFILE_FACET_ADD)
actions.updateFacet = makeActionCreator(PROFILE_FACET_UPDATE, 'id', 'prop', 'value')
actions.removeFacet = makeActionCreator(PROFILE_FACET_REMOVE, 'id')
