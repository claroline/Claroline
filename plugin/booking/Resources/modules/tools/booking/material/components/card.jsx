import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {asset} from '#/main/app/config/asset'

import {DataCard} from '#/main/app/data/components/card'
import {Material as MaterialTypes} from '#/plugin/booking/prop-types'

const MaterialCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    icon="fa fa-toolbox"
    title={props.data.name}
    subtitle={props.data.code}
    contentText={get(props.data, 'description')}
  />

MaterialCard.propTypes = {
  data: T.shape(
    MaterialTypes.propTypes
  ).isRequired
}

export {
  MaterialCard
}
