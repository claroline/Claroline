import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import {actions as formActions} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as baseSelectors} from '#/main/community/tools/community/store'
import {EditorProfile as EditorProfileComponent} from '#/main/community/tools/community/editor/components/profile'

import {selectors} from '#/main/community/tools/community/editor/store'
import {makeId} from '#/main/core/scaffolding/id'

const EditorProfile = connect(
  (state) => ({
    path: toolSelectors.path(state),
    loaded: toolSelectors.loaded(state),
    contextType: toolSelectors.contextType(state),
    contextId: toolSelectors.contextId(state),
    facets: selectors.facets(state),
    profile: baseSelectors.profile(state)
  }),
  (dispatch) => ({
    load(profile) {
      dispatch(formActions.load(selectors.FORM_NAME, {profile: profile}))
    },
    addFacet(facets = []) {
      const newFacets = []
        .concat(facets)
        .concat([
          {
            id: makeId(),
            title: '',
            position: facets.length,
            meta: {
              main: false
            },
            sections: []
          }
        ])

      dispatch(formActions.updateProp(selectors.FORM_NAME, 'profile', newFacets))
    },
    removeFacet(facets, deletedFacet) {
      const newState = cloneDeep(facets)

      const pos = newState.findIndex(facet => facet.id === deletedFacet.id)
      if (-1 !== pos) {
        newState.splice(pos, 1)
      }

      // reorder facets
      newState.map((facet, facetIndex) => {
        facet.position = facetIndex

        return facet
      })

      dispatch(formActions.updateProp(selectors.FORM_NAME, 'profile', newState))
    }
  })
)(EditorProfileComponent)

export {
  EditorProfile
}
