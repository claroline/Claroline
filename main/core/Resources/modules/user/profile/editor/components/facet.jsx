import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import {select as formSelect} from '#/main/core/data/form/selectors'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'

import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'
import {select} from '#/main/core/user/profile/selectors'
import {getFormDefaultSection, formatFormSections} from '#/main/core/user/profile/utils'

// todo manage differences between main / default / plugin facets

const ProfileFacetComponent = props => {
  const sections = formatFormSections(cloneDeep(props.facet.sections), props.originalUser, props.parameters)

  if (props.facet.meta.main) {
    sections.unshift(getFormDefaultSection(props.user))
  }

  return (
    <FormContainer
      name="user"
      title={props.facet.title}
      sections={sections}
    />
  )
}

ProfileFacetComponent.propTypes = {
  user: T.object.isRequired,
  originalUser: T.object.isRequired,
  facet: T.shape(
    ProfileFacetTypes.propTypes
  ).isRequired,
  parameters: T.object.isRequired
}

const ProfileFacet = connect(
  state => ({
    user: formSelect.data(formSelect.form(state, 'user')),
    originalUser: formSelect.originalData(formSelect.form(state, 'user')),
    facet: select.currentFacet(state),
    parameters: select.parameters(state)
  })
)(ProfileFacetComponent)

export {
  ProfileFacet
}
