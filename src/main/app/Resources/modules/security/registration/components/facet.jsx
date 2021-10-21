import React from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'

import {FormData} from '#/main/app/content/form/containers/data'
import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'

import {selectors} from '#/main/app/security/registration/store/selectors'
import {formatFormSections} from '#/main/core/user/profile/utils'

/**
 * Registration Form : Facet section.
 * Contains all fields of a facet displayed in registration form.
 *
 * @todo maybe merge with #/main/core/user/profile/editor/components/facet.jsx
 */
const Facet = props => {
  // todo : create selector
  let sections = []
  if (props.facet) {
    if (props.facet.sections) {
      sections = formatFormSections(cloneDeep(props.facet.sections), props.allFields, props.user)
    }
  }

  return (
    <FormData
      name={selectors.FORM_NAME}
      sections={sections}
    />
  )
}

Facet.propTypes = {
  facet: T.shape(
    ProfileFacetTypes.propTypes
  ).isRequired,
  allFields: T.array,
  user: T.object
}

export {
  Facet
}
