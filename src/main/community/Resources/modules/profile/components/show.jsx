import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {DetailsData} from '#/main/app/content/details/containers/data'

import {Profile} from '#/main/community/profile/containers/main'
import {getDefaultFacet, formatDetailsSections, getDetailsDefaultSection} from '#/main/community/profile/utils'

const ProfileShow = (props) => {
  let facet = props.facet || getDefaultFacet()
  let sections = []

  if (facet.sections) {
    sections = formatDetailsSections(facet.sections, props.allFields, props.user, props.parameters, props.currentUser)
  }

  if (get(props.facet, 'meta.main')) {
    sections.unshift(getDetailsDefaultSection(props.parameters, props.user))
  }

  return (
    <Profile
      path={props.path}
      name={props.name}
      user={props.user}
    >
      <DetailsData
        name={props.name}
        title={facet.title}
        definition={sections}
        affix={get(props.facet, 'meta.main') && props.children}
      />
    </Profile>
  )
}

ProfileShow.propTypes = {
  name: T.string.isRequired,
  path: T.string.isRequired,
  user: T.object,
  facet: T.object,
  allFields: T.array,
  parameters: T.object,
  currentUser: T.object,
  children: T.node
}

export {
  ProfileShow
}
