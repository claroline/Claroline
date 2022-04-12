import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {asset} from '#/main/app/config'
import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

const ToolCard = props =>
  <DataCard
    {...props}
    className={classes(props.className, {
      'data-card-muted': get(props.data, 'restrictions.hidden', false)
    })}
    id={props.data.id}
    icon={`fa fa-${props.data.icon}`}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    title={trans(props.data.name, {}, 'tools')}
  />

ToolCard.propTypes = {
  className: T.string,
  data: T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired,
    icon: T.string.isRequired,
    thumbnail: T.shape({
      url: T.string
    })
  }).isRequired
}

export {
  ToolCard
}
