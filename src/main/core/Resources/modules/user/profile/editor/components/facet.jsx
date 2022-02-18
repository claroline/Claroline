import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'
import {selectors} from '#/main/core/user/profile/store/selectors'
import {getFormDefaultSections, formatFormSections} from '#/main/core/user/profile/utils'

const ProfileFacetComponent = props => {
  // todo : create selector
  let sections = []
  if (props.facet) {
    if (props.facet.sections) {
      sections = formatFormSections(cloneDeep(props.facet.sections), props.allFields, props.originalUser, props.parameters, props.currentUser)
    }

    if (get(props.facet, 'meta.main')) {
      sections = [].concat(getFormDefaultSections(props.user), sections)
    }
  }

  return (
    <FormData
      name={selectors.FORM_NAME}
      title={props.facet.title}
      target={['apiv2_profile_update', {username: props.user.username}]}
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: props.path.replace('/edit', '/show'), // ugly
        exact: true
      }}
      sections={sections}
    />
  )
}

ProfileFacetComponent.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  user: T.object.isRequired,
  originalUser: T.object.isRequired,
  facet: T.shape(
    ProfileFacetTypes.propTypes
  ).isRequired,
  allFields: T.array,
  parameters: T.object.isRequired
}

const ProfileFacet = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    user: formSelect.data(formSelect.form(state, selectors.FORM_NAME)),
    originalUser: formSelect.originalData(formSelect.form(state, selectors.FORM_NAME)),
    facet: selectors.currentFacet(state),
    parameters: selectors.parameters(state),
    allFields: selectors.allFields(state)
  })
)(ProfileFacetComponent)

export {
  ProfileFacet
}
