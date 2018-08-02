import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormData} from '#/main/app/content/form/containers/data'

import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'

// todo maybe merge with #/main/core/user/profile/editor/components/facet.jsx

/**
 * Registration Form : Facet section.
 * Contains all fields of a facet displayed in registration form.
 *
 */
const Facet = props =>
  <div className="profile-facet">
    <FormData
      name="user"
      sections={props.facet.sections}
    />
  </div>

Facet.propTypes = {
  facet: T.shape(
    ProfileFacetTypes.propTypes
  ).isRequired
}

export {
  Facet
}
