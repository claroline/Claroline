import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import classes from 'classnames'

import {DataCard} from '#/main/app/data/components/card'
import {trans} from '#/main/app/intl'

const PluginCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    className={classes(props.className, {
      'data-card-muted': !get(props.data, 'enabled', false)
    })}
    icon="fa fa-puzzle-piece"
    title={trans(props.data.name, {}, 'plugin')}
    subtitle={get(props.data, 'meta.version')}
    contentText={trans(`${props.data.name}_desc`, {}, 'plugin')}
  />

PluginCard.propTypes = {
  className: T.string,
  data: T.shape({
    id: T.number,
    name: T.string,
    meta: T.shape({
      version: T.string
    })
  }).isRequired
}

export {
  PluginCard
}
