import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as detailsSelectors} from '#/main/app/content/form/store/selectors'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {route} from '#/main/core/user/routing'
import {UserDetails} from '#/main/core/user/components/details'
import {ProfileFacets} from '#/main/core/user/profile/components/facets'
import {ProfileFacet} from '#/main/core/user/profile/editor/components/facet'
import {actions, selectors} from '#/main/core/user/profile/store'

const ProfileEditComponent = props =>
  <div className="row user-profile user-profile-edit">
    <div className="user-profile-aside col-md-3">
      <UserDetails
        user={props.user}
      />

      {props.facets && 1 < props.facets.length &&
        <Vertical
          basePath={route(props.user, props.path) + '/edit'}
          tabs={props.facets.map(facet => ({
            icon: facet.icon,
            title: facet.title,
            path: `/${facet.id}`
          }))}
        />
      }
    </div>

    <div className="user-profile-content col-md-9">
      <ProfileFacets
        prefix={route(props.user, props.path) + '/edit'}
        facets={props.facets}
        facetComponent={ProfileFacet}
        openFacet={props.openFacet}
      />
    </div>
  </div>

ProfileEditComponent.propTypes = {
  path: T.string,
  user: T.object.isRequired,
  facets: T.array.isRequired,
  openFacet: T.func.isRequired
}

ProfileEditComponent.defaultProps = {
  facets: []
}

const ProfileEdit = connect(
  (state) => ({
    path: toolSelectors.path(state),
    user: detailsSelectors.data(detailsSelectors.form(state, selectors.FORM_NAME)),
    facets: selectors.facets(state)
  }),
  (dispatch) => ({
    openFacet(id) {
      dispatch(actions.openFacet(id))
    }
  })
)(ProfileEditComponent)

export {
  ProfileEdit
}
