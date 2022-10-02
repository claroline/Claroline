import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Badge as BadgeTypes} from '#/plugin/open-badge/prop-types'

const BadgeCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    className={classes('badge-card', props.className, {
      'data-card-muted': !get(props.data, 'meta.enabled')
    })}
    icon={
      <img src={get(props.data, 'image') ? asset(get(props.data, 'image')) : null} />
    }
    title={props.data.name}
    color={props.data.color}
    contentText={props.data.description}
    flags={[
      !get(props.data, 'meta.enabled') && ['fa fa-fw fa-eye-slash', trans('disabled')]
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
