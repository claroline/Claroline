import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Badge as BadgeTypes} from '#/plugin/open-badge/prop-types'

const BadgeCard = props =>
  <DataCard
    id={props.data.id}
    poster={get(props.data, 'image')}
    color={get(props.data, 'color')}
    icon={!get(props.data, 'image') ? <>{props.data.name.charAt(0)}</> : null}
    title={props.data.name}
    contentText={props.data.description}
    meta={get(props.data, 'meta.archived', false) &&
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
