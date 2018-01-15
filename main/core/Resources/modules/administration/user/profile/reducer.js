import cloneDeep from 'lodash/cloneDeep'

import {makeId} from '#/main/core/scaffolding/id'
import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'

import {
  PROFILE_FACET_OPEN,
  PROFILE_FACET_ADD,
  PROFILE_FACET_REMOVE,
  PROFILE_ADD_SECTION,
  PROFILE_REMOVE_SECTION
} from './actions'

const defaultState = {
  currentFacet: null,
  data: []
}

const reducer = makeFormReducer('profile', defaultState, {
  pendingChanges: makeReducer(false, {
    [PROFILE_FACET_ADD]: () => true,
    [PROFILE_FACET_REMOVE]: () => true,
    [PROFILE_ADD_SECTION]: () => true,
    [PROFILE_REMOVE_SECTION]: () => true
  }),
  data: makeReducer(defaultState.data, {
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
    },

    [PROFILE_ADD_SECTION]: (state, action) => {
      const newState = cloneDeep(state)

      const currentFacet = newState.find(facet => facet.id === action.facetId)
      if (currentFacet) {
        currentFacet.sections.push({
          id: makeId(),
          title: '',
          position: newState.length,
          fields: []
        })
      }

      return newState
    },

    [PROFILE_REMOVE_SECTION]: (state, action) => {
      const newState = cloneDeep(state)

      const currentFacet = newState.find(facet => facet.id === action.facetId)
      if (currentFacet) {
        const pos = currentFacet.sections.findIndex(section => section.id === action.sectionId)
        if (-1 !== pos) {
          currentFacet.sections.splice(pos, 1)
        }

        // reorder sections
        currentFacet.sections.map((section, sectionIndex) => {
          section.position = sectionIndex

          return section
        })
      }

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
