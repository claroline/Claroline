import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Organization as OrganizationTypes} from '#/main/core/user/prop-types'

const OrganizationCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-building"
    title={props.data.name}
    subtitle={props.data.code}
    flags={[
      get(props.data, 'meta.default', false) && ['fa fa-check', trans('default')],
      get(props.data, 'restrictions.public') && ['fa fa-globe', trans('public_organization', {}, 'user')]
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
