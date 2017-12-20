import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import {select as formSelect} from '#/main/core/data/form/selectors'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'

import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'
import {select} from '#/main/core/user/profile/selectors'
import {getFormDefaultSection} from '#/main/core/user/profile/utils'

// todo manage differences between main / default / plugin facets

const ProfileFacetComponent = props => {
  const sections = cloneDeep(props.facet.sections)
  if (props.facet.meta.main) {
    sections.unshift(getFormDefaultSection(props.user))
  }

  return (
    <div className="profile-facet">
      <h2>{props.facet.title}</h2>

      <FormContainer
        name="user"
        sections={sections}
      />
    </div>
  )
}

ProfileFacetComponent.propTypes = {
  user: T.object.isRequired,
  facet: T.shape(
    ProfileFacetTypes.propTypes
  ).isRequired
}

const ProfileFacet = connect(
  state => ({
    user: formSelect.data(formSelect.form(state, 'user')),
    facet: select.currentFacet(state)
  })
)(ProfileFacetComponent)

export {
  ProfileFacet
}
