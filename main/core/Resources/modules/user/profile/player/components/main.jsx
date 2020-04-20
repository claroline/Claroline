import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as detailsSelectors} from '#/main/app/content/details/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {route} from '#/main/core/user/routing'
import {UserDetails} from '#/main/core/user/components/details'
import {ProfileLayout} from '#/main/core/user/profile/components/layout'
import {ProfileFacets} from '#/main/core/user/profile/components/facets'
import {ProfileFacet} from '#/main/core/user/profile/player/components/facet'
import {actions, selectors} from '#/main/core/user/profile/store'

const ProfileShowComponent = props =>
  <ProfileLayout
    affix={
      <Fragment>
        <UserDetails
          user={props.user}
        />

        {props.facets && 1 < props.facets.length &&
        <Vertical
          basePath={route(props.user, props.path) + '/show'}
          tabs={props.facets.map(facet => ({
            icon: facet.icon,
            title: facet.title,
            path: `/${facet.id}`
          }))}
        />
        }
      </Fragment>
    }
    content={
      <ProfileFacets
        prefix={route(props.user, props.path) + '/show'}
        facets={props.facets}
        facetComponent={ProfileFacet}
        openFacet={props.openFacet}
      />
    }
  />

ProfileShowComponent.propTypes = {
  path: T.string,
  user: T.object.isRequired,
  facets: T.array.isRequired,
  openFacet: T.func.isRequired
}

ProfileShowComponent.defaultProps = {
  facets: []
}

const ProfileShow = connect(
  (state) => ({
    path: toolSelectors.path(state),
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
