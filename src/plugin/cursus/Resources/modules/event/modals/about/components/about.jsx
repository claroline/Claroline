import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'

const AboutModal = props =>
  <Modal
    {...omit(props, 'event')}
    icon="fa fa-fw fa-circle-info"
    title={trans('about')}
    subtitle={props.event.name}
    poster={props.event.poster ? props.event.poster : undefined}
  >
    <DetailsData
      meta={true}
      data={props.event}
      sections={[
        {
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
              label: trans('description'),
              type: 'html'
            }, {
              name: 'location',
              type: 'location',
              label: trans('location'),
              placeholder: trans('online_session', {}, 'cursus')
            }, {
              name: 'code',
              label: trans('code'),
              type: 'string'
            }, {
              name: 'session',
              label: trans('session', {}, 'cursus'),
              type: 'training_session'
            }
          ]
        }
      ]}
    />
  </Modal>

AboutModal.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ).isRequired
}

export {
  AboutModal
}
