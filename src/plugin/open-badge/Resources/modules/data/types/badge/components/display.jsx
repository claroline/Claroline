import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Badge as BadgeType} from '#/plugin/open-badge/prop-types'
import {BadgeCard} from '#/plugin/open-badge/badge/components/card'

const BadgeDisplay = (props) => props.data ?
  <BadgeCard
    data={props.data}
    size="sm"
    orientation="col"
  /> :
  <ContentPlaceholder
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
