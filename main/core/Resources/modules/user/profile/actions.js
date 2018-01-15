
import {makeActionCreator} from '#/main/core/scaffolding/actions'

export const PROFILE_FACET_OPEN = 'PROFILE_FACET_OPEN'

export const actions = {}

actions.openFacet = makeActionCreator(PROFILE_FACET_OPEN, 'id')
