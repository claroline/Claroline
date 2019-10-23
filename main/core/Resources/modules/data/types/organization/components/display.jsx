import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {Organization as OrganizationType} from '#/main/core/user/prop-types'
import {OrganizationCard} from '#/main/core/user/data/components/organization-card'

const OrganizationDisplay = (props) => props.data ?
  <OrganizationCard
    data={props.data}
    size="xs"
  /> :
  <EmptyPlaceholder
    icon="fa fa-building"
    title={trans('no_organization')}
  />

OrganizationDisplay.propTypes = {
  data: T.shape(OrganizationType.propTypes)
}

export {
  OrganizationDisplay
}
