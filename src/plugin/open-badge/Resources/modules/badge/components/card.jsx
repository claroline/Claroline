import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Badge as BadgeTypes} from '#/plugin/open-badge/prop-types'
import {BadgeImage} from '#/plugin/open-badge/badge/components/image'

const BadgeCard = props =>
  <DataCard
    title={props.data.name}
    subtitle={get(props.data, 'workspace') ? get(props.data, 'workspace.name') : trans('platform')}
    {...props}
    id={props.data.id}
    className={classes('badge-card', props.className, {
      'data-card-muted': !get(props.data, 'meta.enabled')
    })}
    icon={
      <BadgeImage badge={props.data} size={classes({
        sm: 'xs' === props.size,
        md: 'sm' === props.size,
        lg: 'lg' === props.size
      })} />
    }
    contentText={props.data.description}
    flags={[
      !get(props.data, 'meta.enabled') && ['fa fa-fw fa-eye-slash', trans('disabled')]
    ].filter(flag => !!flag)}
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
