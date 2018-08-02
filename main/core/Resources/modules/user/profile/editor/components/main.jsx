import React from 'react'
import {PropTypes as T} from 'prop-types'

import {UserDetails} from '#/main/core/user/components/details.jsx'
import {connectProfile} from '#/main/core/user/profile/connect'
import {ProfileNav} from '#/main/core/user/profile/components/nav.jsx'
import {ProfileFacets} from '#/main/core/user/profile/components/facets.jsx'

import {selectors as select} from '#/main/app/content/form/store/selectors'
import {ProfileFacet} from '#/main/core/user/profile/editor/components/facet.jsx'

const ProfileEditComponent = props =>
  <div className="row user-profile user-profile-edit">
    <div className="user-profile-aside col-md-3">
      <UserDetails
        user={props.user}
      />

      {1 < props.facets.length &&
        <ProfileNav
          prefix="/edit"
          facets={props.facets}
        />
      }
    </div>

    <div className="user-profile-content col-md-9">
      <ProfileFacets
        prefix="/edit"
        facets={props.facets}
        facetComponent={ProfileFacet}
        openFacet={props.openFacet}
      />
    </div>
  </div>

ProfileEditComponent.propTypes = {
  user: T.object.isRequired,
  facets: T.array.isRequired,
  openFacet: T.func.isRequired
}

const ProfileEdit = connectProfile(
  state => ({
    user: select.data(select.form(state, 'user'))
  })
)(ProfileEditComponent)

export {
  ProfileEdit
}
