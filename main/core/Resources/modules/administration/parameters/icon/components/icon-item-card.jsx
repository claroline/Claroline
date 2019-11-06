import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {DataCard} from '#/main/app/content/card/components/data'

import {IconItem as IconItemType} from '#/main/core/administration/parameters/prop-types'

const IconItemCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon={<img src={asset(props.data.relativeUrl)} />}
    title={props.data.mimeType}
  />

IconItemCard.propTypes = {
  data: T.shape(IconItemType.propTypes).isRequired
}

export {
  IconItemCard
}
