import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormData} from '#/main/app/content/form/containers/data'
import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'
import cloneDeep from 'lodash/cloneDeep'

// todo maybe merge with #/main/core/user/profile/editor/components/facet.jsx

/**
 * Registration Form : Facet section.
 * Contains all fields of a facet displayed in registration form.
 *
 */
const Facet = props => {
  const sections = cloneDeep(props.facet.sections)
  sections.forEach(section => {
    section.fields.forEach(field => {
      field.name = 'profile.' + field.id
    })
  })
  
  return (<div className="profile-facet">
    <FormData
      name="user"
      sections={sections}
    />
  </div>)
}

Facet.propTypes = {
  facet: T.shape(
    ProfileFacetTypes.propTypes
  ).isRequired
}

export {
  Facet
}
