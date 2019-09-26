import React from 'react'
import {PropTypes as T} from 'prop-types'

import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/card'

const AssertionCard = props =>
  <BadgeCard
    {...props}
    data={props.data.badge}
  />

AssertionCard.propTypes = {
  data: T.shape({

  }).isRequired
}

export {
  AssertionCard
}
