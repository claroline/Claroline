import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import {selectors as detailsSelect} from '#/main/app/content/details/store'
import {DetailsData} from '#/main/app/content/details/containers/data'

import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'
import {select} from '#/main/core/user/profile/selectors'
import {getDetailsDefaultSection, formatDetailsSections} from '#/main/core/user/profile/utils'

const ProfileFacetComponent = props => {
  const sections = formatDetailsSections(cloneDeep(props.facet.sections), props.user, props.parameters)

  if (props.facet.meta.main) {
    sections.unshift(getDetailsDefaultSection(props.user))
  }

  return (
    <DetailsData
      name="user"
      title={props.facet.title}
      sections={sections}
    />
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
