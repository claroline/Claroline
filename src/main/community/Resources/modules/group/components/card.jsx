import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {asset} from '#/main/app/config'
import {DataCard} from '#/main/app/data/components/card'

import {Group as GroupTypes} from '#/main/community/group/prop-types'

const GroupCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon={!props.data.thumbnail ? 'fa fa-fw fa-users' : null}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    title={props.data.name}
    contentText={get(props.data, 'meta.description')}
  />

GroupCard.propTypes = {
  data: T.shape(
    GroupTypes.propTypes
  ).isRequired
}

export {
  GroupCard
}
