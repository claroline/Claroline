import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {Badge as BadgeType} from '#/plugin/open-badge/tools/badges/prop-types'
import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/card'

const BadgeDisplay = (props) => props.data ?
  <BadgeCard
    data={props.data}
    size="sm"
    orientation="col"
  /> :
  <EmptyPlaceholder
    size="lg"
    icon="fa fa-books"
    title={trans('no_badge', {}, 'badge')}
  />

BadgeDisplay.propTypes = {
  data: T.shape(BadgeType.propTypes)
}

export {
  BadgeDisplay
}
