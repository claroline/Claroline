import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Badge as BadgeTypes} from '#/plugin/open-badge/prop-types'
import {BadgeImage} from '#/plugin/open-badge/badge/components/image'
import {asset} from '#/main/app/config'

const BadgeCard = props =>
  <DataCard
    title={props.data.name}
    {...props}
    id={props.data.id}
    className={classes(props.className, {
      'data-card-muted': !get(props.data, 'meta.enabled')
    })}
    poster={get(props.data, 'image') ? asset(get(props.data, 'image')) : null}
    color={get(props.data, 'color')}
    icon={!get(props.data, 'image') ? <>{props.data.name.charAt(0)}</> : null}
    contentText={props.data.description}
    meta={!get(props.data, 'meta.enabled') &&
      <span className="badge bg-secondary-subtle text-secondary-emphasis text-capitalize">{trans('disabled')}</span>
    }
    asIcon={true}
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
