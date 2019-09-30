import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/content/card/components/data'

import {Badge as BadgeTypes} from '#/plugin/open-badge/tools/badges/prop-types'

const BadgeCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    className={classes(props.className, {
      'data-card-muted': !get(props.data, 'meta.enabled')
    })}
    poster={get(props.data, 'image.url') ? asset(get(props.data, 'image.url')) : null}
    icon="fa fa-trophy"
    title={props.data.name}
    color={props.data.color}
    contentText={props.data.description}
    flags={[
      get(props.data, 'meta.enabled') && ['fa fa-fw fa-check', trans('enabled')]
    ].filter(flag => !!flag)}
    footer={
      <span>
        {props.data.issuer &&
          <span>Issued by <b>{props.data.issuer.name}</b></span>
        }
      </span>
    }
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
