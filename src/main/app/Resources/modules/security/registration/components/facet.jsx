import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormData} from '#/main/app/content/form/containers/data'
import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'

import {selectors} from '#/main/app/security/registration/store/selectors'

/**
 * Registration Form : Facet section.
 * Contains all fields of a facet displayed in registration form.
 *
 * @todo maybe merge with #/main/core/user/profile/editor/components/facet.jsx
 */
const Facet = props =>
  <FormData
    className="profile-facet"
    name={selectors.FORM_NAME}
    sections={props.facet.sections.map(section => (Object.assign({}, section, {
      fields: section.fields.map(field => Object.assign({}, field, {
        name: 'profile.' + field.id
      }))
    })))}
  />

Facet.propTypes = {
  facet: T.shape(
    ProfileFacetTypes.propTypes
  ).isRequired
}

export {
  Facet
}
