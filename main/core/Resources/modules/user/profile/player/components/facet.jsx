import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import {select as detailsSelect} from '#/main/core/data/details/selectors'
import {DataDetailsContainer} from '#/main/core/data/details/containers/details.jsx'

import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'
import {select} from '#/main/core/user/profile/selectors'
import {getDetailsDefaultSection, formatDetailsSections} from '#/main/core/user/profile/utils'

const ProfileFacetComponent = props => {
  const sections = formatDetailsSections(cloneDeep(props.facet.sections), props.user, props.parameters)

  if (props.facet.meta.main) {
    sections.unshift(getDetailsDefaultSection(props.user))
  }

  return (
    <div className="profile-facet">
      <h2>{props.facet.title}</h2>

      <DataDetailsContainer
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
  ).isRequired,
  parameters: T.object.isRequired
}

const ProfileFacet = connect(
  state => ({
    user: detailsSelect.data(detailsSelect.details(state, 'user')),
    facet: select.currentFacet(state),
    parameters: select.parameters(state)
  })
)(ProfileFacetComponent)

export {
  ProfileFacet
}
