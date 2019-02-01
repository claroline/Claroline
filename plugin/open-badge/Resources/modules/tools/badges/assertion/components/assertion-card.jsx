import React from 'react'
import {DataCard} from '#/main/app/content/card/components/data'
import {PropTypes as T} from 'prop-types'

import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/badge-card'

const AssertionCard = props =>
  props.data.badge ?
    <BadgeCard data={props.data.badge}/>
    :<DataCard/>

AssertionCard.propTypes = {
  data: T.shape(
  ).isRequired
}

export {
  AssertionCard
}
