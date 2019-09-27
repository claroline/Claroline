import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/content/card/components/data'

import {Badge as BadgeTypes} from '#/plugin/open-badge/tools/badges/prop-types'

const BadgeCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.image ? asset(props.data.image.url) : null}
    icon="fa fa-trophy"
    title={props.data.name}
    contentText={props.data.description}
    flags={[
      props.data.meta && props.data.meta.enabled && ['fa fa-fw fa-eye', trans('enabled')]
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
  data: T.shape(
    BadgeTypes.propTypes
  ).isRequired
}

export {
  BadgeCard
}
