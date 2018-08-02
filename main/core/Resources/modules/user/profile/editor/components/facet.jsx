import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'

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
    <FormData
      name="user"
      title={props.facet.title}
      target={['apiv2_user_update', {id: props.user.id}]}
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: '/show',
        exact: true
      }}
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
