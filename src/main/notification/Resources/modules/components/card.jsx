import React from 'react'
import {PropTypes as T} from 'prop-types'

import {displayDate} from '#/main/app/intl'
import {DataCard} from '#/main/app/data/components/card'

const NotificationCard = (props) =>
  <DataCard
    {...props}
    title={props.data.name}
    poster={props.data.thumbnail}
    contentText={displayDate(props.data.date)}
    asIcon={true}
  />

NotificationCard.propTypes = {
  data: T.shape({

  })
}

export {
  NotificationCard
}
