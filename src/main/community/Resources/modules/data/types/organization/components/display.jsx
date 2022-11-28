import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Organization as OrganizationType} from '#/main/community/prop-types'
import {OrganizationCard} from '#/main/community/organization/components/card'

const OrganizationDisplay = (props) => props.data ?
  <OrganizationCard
    data={props.data}
    size="xs"
  /> :
  <ContentPlaceholder
    icon="fa fa-building"
    title={trans('no_organization')}
  />

OrganizationDisplay.propTypes = {
  data: T.shape(OrganizationType.propTypes)
}

export {
  OrganizationDisplay
}
