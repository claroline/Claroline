import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'

import {Event as EventTypes} from '#/plugin/agenda/event/prop-types'

const AboutModal = props =>
  <Modal
    {...omit(props, 'event')}
    icon="fa fa-fw fa-info"
    title={props.event.title}
    subtitle={trans('about')}
    poster={props.event.thumbnail ? props.event.thumbnail.url : undefined}
  >
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

    {0 !== props.actions.length &&
      <Toolbar
        id={`event-${props.event.id}-actions`}
        buttonName="modal-btn btn"
        actions={props.actions.map(action => Object.assign({}, action, {
          onClick: () => props.fadeModal()
        }))}
      />
    }
  </Modal>

AboutModal.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  actions: T.arrayOf(T.shape({
    // TODO : action types
  })),
  fadeModal: T.func.isRequired
}

AboutModal.defaultProps = {
  actions: []
}

export {
  AboutModal
}
