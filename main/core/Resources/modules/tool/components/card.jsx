import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/content/card/components/data'

const ToolCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon={`fa fa-${props.data.icon}`}
    title={trans(props.data.name, {}, 'tools')}
  />

ToolCard.propTypes = {
  data: T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired,
    icon: T.string
  }).isRequired
}

export {
  ToolCard
}
