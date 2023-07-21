import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {asset} from '#/main/app/config'
import {DataCard} from '#/main/app/data/components/card'

const CrudCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon={!props.data.thumbnail ? 'fa fa-fw fa-ghost' : null}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    title={props.data.name}
    contentText={get(props.data, 'meta.description')}
  />

CrudCard.propTypes = {
  data: T.shape({
    id: T.string,
    name: T.string,
    thumbnail: T.string
  }).isRequired
}

export {
  CrudCard
}
