import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormContainer} from '#/main/core/data/form/containers/form.jsx'

import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'

// todo maybe merge with #/main/core/user/profile/editor/components/facet.jsx

/**
 * Registration Form : Facet section.
 * Contains all fields of a facet displayed in registration form.
 *
 */
const Facet = props =>
  <div className="profile-facet">
    <FormContainer
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
