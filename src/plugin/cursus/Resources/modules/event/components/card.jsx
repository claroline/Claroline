import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {displayDateRange, trans} from '#/main/app/intl'
import {DataCard} from '#/main/app/data/components/card'
import {getAddressString} from '#/main/app/data/types/address/utils'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'
import {AvailableSeats} from '#/plugin/cursus/components/available-seats'

const EventCard = props =>
  <DataCard
    {...props}
    poster={props.data.poster}
    name={props.data.name}
    icon="fa fa-calendar-day"
    title={
      <div className="d-flex flex-row gap-2 align-items-baseline" role="presentation">
        {props.data.name}
        {'row' === props.orientation &&
          <AvailableSeats session={props.data} className="ms-auto" />
        }
      </div>
    }
    contentText={(
      <div className={classes('d-flex gap-2', 'col' === props.orientation && 'flex-column h-100 justify-content-center')} role="presentation">
        <div role="presentation">
          <span className="fa fa-calendar me-2" aria-hidden={true} />
          {displayDateRange(get(props.data, 'start'), get(props.data, 'end'), true)}
        </div>

        {'row' === props.orientation &&
          <span aria-hidden={true}>-</span>
        }

        <div role="presentation">
          <span className="fa fa-map-marker-alt me-2" aria-hidden={true} />
          {props.data.location ?
            (getAddressString(get(props.data, 'location.address'), true) || get(props.data, 'location.name')) :
            trans('online_session', {}, 'cursus')
          }
        </div>
      </div>
    )}
    meta={
      <AvailableSeats session={props.data} className="ms-auto" />
    }
  />

EventCard.propTypes = {
  orientation: T.string.isRequired,
  data: T.shape(
    EventTypes.propTypes
  ).isRequired
}

export {
  EventCard
}
