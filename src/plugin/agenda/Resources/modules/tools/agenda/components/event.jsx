import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentLoader} from '#/main/app/content/components/loader'
import {DetailsData} from '#/main/app/content/details/components/data'

import {Event as EventTypes} from '#/plugin/agenda/event/prop-types'
import {route} from '#/plugin/agenda/tools/agenda/routing'

const AgendaEvent = props => {
  if (!props.event) {
    return (
      <ContentLoader
        size="lg"
        description="Nous chargeons votre évènement..."
      />
    )
  }

  return (
    <Fragment>
      {props.event.thumbnail &&
        <img className="event-poster img-responsive" alt={props.event.title} src={asset(props.event.thumbnail.url)} />
      }

      <ContentTitle
        title={props.event.title}
        backAction={{
          type: LINK_BUTTON,
          target: route(props.path, 'month', props.referenceDate)
        }}
      />

      <DetailsData
        data={props.event}
        meta={true}
        sections={[{
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'dates',
              type: 'date-range',
              label: trans('date'),
              calculated: (event) => [event.start || null, event.end || null],
              options: {
                time: true
              }
            }, {
              name: 'description',
              type: 'html',
              label: trans('description')
            }, {
              name: 'guests',
              type: 'users',
              label: trans('guests')
            }
          ]
        }]}
      />
    </Fragment>
  )
}

AgendaEvent.propTypes = {
  path: T.string.isRequired,
  referenceDate: T.string,
  event: T.shape(
    EventTypes.propTypes
  )
}

export {
  AgendaEvent
}
