import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {displayDateRange} from '#/main/app/intl/date'
import {DataCard} from '#/main/app/data/components/card'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {EventIcon} from '#/plugin/agenda/event/components/icon'
import {getAddressString} from '#/main/app/data/types/address/utils'
import {trans} from '#/main/app/intl'
import classes from 'classnames'
import {Badge} from '#/main/app/components/badge'

const EventCard = (props) =>
  <DataCard
    {...props}
    icon={get(props.data, 'meta.type') ?
      <EventIcon type={get(props.data, 'meta.type')} /> : undefined
    }
    name={props.data.name}
    title={
      <div className="d-flex flex-row gap-2 align-items-baseline" role="presentation">
        {props.data.name}
        {'row' === props.orientation &&
          <Badge className="ms-auto" variant="primary" subtle={true}>
            {trans(get(props.data, 'meta.type'), {}, 'event')}
          </Badge>
        }
      </div>
    }
    poster={props.data.poster}
    contentText={
      <span className={classes('d-flex gap-2', 'col' === props.orientation && 'flex-column h-100 justify-content-center')} role="presentation">
        <span role="presentation">
          <span className="fa fa-calendar me-2" aria-hidden={true} />
          {displayDateRange(get(props.data, 'start'), get(props.data, 'end'), true)}
        </span>

        {'row' === props.orientation &&
          <span aria-hidden={true}>-</span>
        }

        <span role="presentation">
          <span className="fa fa-map-marker-alt me-2" aria-hidden={true} />
          {props.data.location ?
            (getAddressString(get(props.data, 'location.address'), true) || get(props.data, 'location.name')) :
            trans('online')
          }
        </span>
      </span>
    }
    meta={
      <Badge className="ms-auto" variant="primary" subtle={true}>
        {trans(get(props.data, 'meta.type'), {}, 'event')}
      </Badge>
    }
  />

EventCard.propTypes = {
  data: T.shape(
    EventTypes.propTypes
  ).isRequired,
  orientation: T.string
}

export {
  EventCard
}
