import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Badge as BadgeTypes} from '#/plugin/open-badge/prop-types'

const BadgeCard = props =>
  <DataCard
    title={props.data.name}
    id={props.data.id}
    className={classes(props.className, {
      'data-card-muted': !get(props.data, 'meta.enabled')
    })}
    poster={get(props.data, 'image')}
    color={get(props.data, 'color')}
    icon={!get(props.data, 'image') ? <>{props.data.name.charAt(0)}</> : null}
    contentText={props.data.description}
    meta={!get(props.data, 'meta.enabled') &&
      <span className="badge bg-secondary-subtle text-secondary-emphasis text-capitalize">{trans('disabled')}</span>
    }
    asIcon={true}
    {...props}
  />

BadgeCard.propTypes = {
  className: T.string,
  data: T.shape(
    BadgeTypes.propTypes
  ).isRequired
}

export {
  BadgeCard
}
