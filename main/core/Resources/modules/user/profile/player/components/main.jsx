import React from 'react'
import {PropTypes as T} from 'prop-types'

import {UserDetails} from '#/main/core/user/components/details.jsx'

import {connectProfile} from '#/main/core/user/profile/connect'
import {ProfileNav} from '#/main/core/user/profile/components/nav.jsx'
import {ProfileFacets} from '#/main/core/user/profile/components/facets.jsx'

import {select} from '#/main/core/data/details/selectors'
import {ProfileFacet} from '#/main/core/user/profile/player/components/facet.jsx'

const ProfileShowComponent = props =>
  <div className="user-profile row">
    <div className="user-profile-aside col-md-3">
      <UserDetails
        user={props.user}
      />

      {1 < props.facets.length &&
        <ProfileNav
          prefix="/show"
          facets={props.facets}
        />
      }
    </div>

    <div className="user-profile-content col-md-9">
      <ProfileFacets
        prefix="/show"
        facets={props.facets}
        facetComponent={ProfileFacet}
        openFacet={props.openFacet}
      />
    </div>
  </div>

ProfileShowComponent.propTypes = {
  user: T.object.isRequired,
  facets: T.array.isRequired,
  openFacet: T.func.isRequired
}

const ProfileShow = connectProfile(
  state => ({
    user: select.data(select.details(state, 'user'))
  })
)(ProfileShowComponent)

export {
  ProfileShow
}
