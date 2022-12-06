import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {asset} from '#/main/app/config'
import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Organization as OrganizationTypes} from '#/main/community/organization/prop-types'

const OrganizationCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon={!props.data.thumbnail ? 'fa fa-fw fa-building' : null}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    title={props.data.name}
    subtitle={props.data.code}
    flags={[
      get(props.data, 'meta.default', false) && ['fa fa-check', trans('default')],
      get(props.data, 'restrictions.public') && ['fa fa-globe', trans('public_organization', {}, 'community')]
    ].filter(flag => !!flag)}
    contentText={get(props.data, 'meta.description')}
  />

OrganizationCard.propTypes = {
  data: T.shape(
    OrganizationTypes.propTypes
  ).isRequired
}

export {
  OrganizationCard
}
