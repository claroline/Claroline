import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as detailsSelectors} from '#/main/app/content/details/store'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {ProfileLayout} from '#/main/community/profile/components/layout'
import {ProfileFacets} from '#/main/community/profile/components/facets'
import {ProfileFacet} from '#/main/community/profile/player/components/facet'
import {actions, selectors} from '#/main/community/profile/store'

const ProfileShowComponent = props =>
  <ProfileLayout
    user={props.user}
    affix={props.facets && 1 < props.facets.length &&
      <Vertical
        basePath={props.path + '/show'}
        tabs={props.facets.map(facet => ({
          icon: facet.icon,
          title: facet.title,
          path: `/${facet.id}`
        }))}
      />
    }
    content={
      <ProfileFacets
        prefix={props.path + '/show'}
        facets={props.facets}
        facetComponent={ProfileFacet}
        openFacet={props.openFacet}
      />
    }
  />

ProfileShowComponent.propTypes = {
  path: T.string.isRequired,
  user: T.object.isRequired,
  facets: T.array.isRequired,
  openFacet: T.func.isRequired
}

ProfileShowComponent.defaultProps = {
  facets: []
}

const ProfileShow = connect(
  (state) => ({
    user: detailsSelectors.data(detailsSelectors.details(state, selectors.FORM_NAME)),
    facets: selectors.facets(state)
  }),
  (dispatch) => ({
    openFacet(id) {
      dispatch(actions.openFacet(id))
    }
  })
)(ProfileShowComponent)

export {
  ProfileShow
}
