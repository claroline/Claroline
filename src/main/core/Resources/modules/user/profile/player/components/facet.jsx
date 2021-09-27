import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as detailsSelect} from '#/main/app/content/details/store'
import {DetailsData} from '#/main/app/content/details/containers/data'

import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'
import {selectors} from '#/main/core/user/profile/store/selectors'
import {getDetailsDefaultSection, formatDetailsSections} from '#/main/core/user/profile/utils'

const ProfileFacetComponent = props => {
  // todo : create a selector instead
  let sections = []
  if (props.facet) {
    if (props.facet.sections) {
      sections = formatDetailsSections(cloneDeep(props.facet.sections), props.allFields, props.user, props.parameters, props.currentUser)
    }

    if (get(props.facet, 'meta.main')) {
      sections.unshift(getDetailsDefaultSection(props.parameters, props.user))
    }
  }

  return (
    <DetailsData
      name={selectors.FORM_NAME}
      title={props.facet.title}
      sections={sections}
    />
  )
}

ProfileFacetComponent.propTypes = {
  currentUser: T.object,
  user: T.object.isRequired,
  facet: T.shape(
    ProfileFacetTypes.propTypes
  ).isRequired,
  parameters: T.object.isRequired,
  allFields: T.array
}

const ProfileFacet = connect(
  state => ({
    currentUser: securitySelectors.currentUser(state),
    user: detailsSelect.data(detailsSelect.details(state, selectors.FORM_NAME)),
    facet: selectors.currentFacet(state),
    parameters: selectors.parameters(state),
    allFields: selectors.allFields(state)
  })
)(ProfileFacetComponent)

export {
  ProfileFacet
}
