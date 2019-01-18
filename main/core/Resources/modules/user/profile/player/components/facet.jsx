import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'

import {selectors as detailsSelect} from '#/main/app/content/details/store'
import {DetailsData} from '#/main/app/content/details/containers/data'

import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'
import {select} from '#/main/core/user/profile/selectors'
import {getDetailsDefaultSection, formatDetailsSections} from '#/main/core/user/profile/utils'

const ProfileFacetComponent = props => {
  // todo : create a selector instead
  let sections = []
  if (props.facet) {
    if (props.facet.sections) {
      sections = formatDetailsSections(cloneDeep(props.facet.sections), props.user, props.parameters)
    }

    if (get(props.facet, 'meta.main')) {
      sections.unshift(getDetailsDefaultSection(props.user))
    }
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
