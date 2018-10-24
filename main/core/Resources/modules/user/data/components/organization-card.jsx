import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/content/card/components/data'

import {Organization as OrganizationTypes} from '#/main/core/user/prop-types'

const OrganizationCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-building"
    title={props.data.name}
    subtitle={props.data.code}
    flags={[
      props.data.meta.default && ['fa fa-check', trans('default')]
    ].filter(flag => !!flag)}
  />

OrganizationCard.propTypes = {
  data: T.shape(
    OrganizationTypes.propTypes
  ).isRequired
}

export {
  OrganizationCard
}
