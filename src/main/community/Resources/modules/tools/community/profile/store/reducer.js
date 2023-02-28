import cloneDeep from 'lodash/cloneDeep'

import {makeId} from '#/main/core/scaffolding/id'
import {makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'
import {decorate} from '#/main/community/profile/decorator'
import {
  PROFILE_FACET_OPEN,
  PROFILE_FACET_ADD,
  PROFILE_FACET_REMOVE
} from '#/main/community/tools/community/profile/store/actions'

const defaultState = {
  currentFacet: null,
  data: []
}

const reducer = makeFormReducer(baseSelectors.STORE_NAME+'.profile', defaultState, {
  pendingChanges: makeReducer(false, {
    [PROFILE_FACET_ADD]: () => true,
    [PROFILE_FACET_REMOVE]: () => true
  }),
  originalData: makeReducer(defaultState.data, {
    [makeInstanceAction(TOOL_LOAD, 'community')]: (state, action) => decorate(action.toolData.profile)
  }),
  data: makeReducer(defaultState.data, {
    [makeInstanceAction(TOOL_LOAD, 'community')]: (state, action) => decorate(action.toolData.profile),
    [PROFILE_FACET_ADD]: (state) => {
      const newState = cloneDeep(state)

      newState.push({
        id: makeId(),
        title: '',
        position: newState.length,
        meta: {
          main: false
        },
        sections: []
      })

      return newState
    },

    [PROFILE_FACET_REMOVE]: (state, action) => {
      const newState = cloneDeep(state)

      const pos = newState.findIndex(facet => facet.id === action.id)
      if (-1 !== pos) {
        newState.splice(pos, 1)
      }

      // reorder facets
      newState.map((facet, facetIndex) => {
        facet.position = facetIndex

        return facet
      })

      return newState
    }
  }),
  currentFacet: makeReducer(defaultState.currentFacet, {
    [PROFILE_FACET_OPEN]: (state, action) => action.id
  })
})


export {
  reducer
}
