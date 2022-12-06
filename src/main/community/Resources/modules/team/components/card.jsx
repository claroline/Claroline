import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {asset} from '#/main/app/config'
import {DataCard} from '#/main/app/data/components/card'

import {Team as TeamTypes} from '#/main/community/team/prop-types'

const TeamCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-fw fa-user-group"
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    title={props.data.name}
    contentText={get(props.data, 'meta.description')}
  />

TeamCard.propTypes = {
  data: T.shape(
    TeamTypes.propTypes
  ).isRequired
}

export {
  TeamCard
}
