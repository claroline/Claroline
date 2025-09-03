import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans, displayDateRange} from '#/main/app/intl'
import {DataCard} from '#/main/app/data/components/card'

import {Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {AvailableSeats} from '#/plugin/cursus/components/available-seats'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {getAddressString} from '#/main/app/data/types/address/utils'

/**
 * A session card focused on the course data.
 */
const SessionCourseCard = props =>
  <DataCard
    {...props}
    poster={props.data.poster}
    icon="fa fa-graduation-cap"
    name={props.data.name}
    title={props.data.name}
    contentText={displayDateRange(get(props.data, 'dates[0]'), get(props.data, 'dates[1]'))}
  />

/**
 * A session card focused on the dates and planing data.
 */
const SessionDateCard = (props) =>
  <DataCard
    {...props}
    poster={props.data.poster}
    icon="fa fa-calendar-week"
    name={props.data.name}
    title={
      <div className="d-flex flex-row gap-2 align-items-baseline" role="presentation">
        {displayDateRange(get(props.data, 'dates[0]'), get(props.data, 'dates[1]'))}

        {'row' === props.orientation &&
          <AvailableSeats session={props.data} className="ms-auto" />
        }
      </div>
    }
    contentText={
      <>
        <span className="fa fa-map-marker-alt me-2" aria-hidden={true} />
        {props.data.location ?
          (getAddressString(get(props.data, 'location.address'), true) || get(props.data, 'location.name')) :
          trans('online_session', {}, 'cursus')
        }
      </>
    }
    meta={
      <AvailableSeats session={props.data} />
    }
  />

/**
 * A session card focused on the session data.
 */
const SessionCard = props =>
  <DataCard
    {...props}
    poster={props.data.poster}
    icon="fa fa-calendar-week"
    name={props.data.name}
    title={
      <div className="d-flex flex-row gap-2 align-items-baseline" role="presentation">
        {(get(props.data, 'registration.selfRegistration') || get(props.data, 'registration.autoRegistration')) &&
          <TooltipOverlay
            id={'session-type'+props.data.id}
            position="top"
            tip={trans('public_registration')}
          >
            <span className="fa fa-fw fa-globe" aria-hidden={true} />
          </TooltipOverlay>
        }

        {props.data.name}

        {'row' === props.orientation &&
          <AvailableSeats session={props.data} className="ms-auto" />
        }
      </div>
    }
    contentText={(
      <span className={classes('d-flex gap-2', 'col' === props.orientation && 'flex-column h-100 justify-content-center')} role="presentation">
        <span role="presentation">
          <span className="fa fa-calendar me-2" aria-hidden={true} />
          {displayDateRange(get(props.data, 'dates[0]'), get(props.data, 'dates[1]'))}
        </span>

        {'row' === props.orientation &&
          <span aria-hidden={true}>-</span>
        }

        <span role="presentation">
          <span className="fa fa-map-marker-alt me-2" aria-hidden={true} />
          {props.data.location ?
            (getAddressString(get(props.data, 'location.address'), true) || get(props.data, 'location.name')) :
            trans('online_session', {}, 'cursus')
          }
        </span>
      </span>
    )}
    meta={
      <AvailableSeats session={props.data} />
    }
  />

SessionCard.propTypes = {
  orientation: T.string.isRequired,
  data: T.shape(
    SessionTypes.propTypes
  ).isRequired
}

export {
  SessionCourseCard,
  SessionDateCard,
  SessionCard
}
