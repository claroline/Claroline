import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {displayDateRange, trans} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Content, ContentSkeleton} from '#/main/app/components/content'
import {DetailsData} from '#/main/app/content/details'
import {selectors as securitySelectors} from '#/main/app/security'
import {selectors as contextSelectors} from '#/main/app/context'
import {actions as fetchActions, useFetch, constants as fetchConst} from '#/main/app/api/fetch'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {getActions} from '#/plugin/agenda/events/event/utils'

const STORE_NAME = 'agendaEventAbout'

const EventAboutModal = (props) => {
  const dispatch = useDispatch()

  const contextPath = useSelector(contextSelectors.path)
  const currentUser = useSelector(securitySelectors.currentUser)
  const [event, status] = useFetch(STORE_NAME, ['apiv2_event_get', {id: get(props.event, 'id')}])
  const loaded = fetchConst.STATUS_IDLE !== status && fetchConst.STATUS_PENDING !== status

  return (
    <Modal
      {...omit(props, 'event', 'reload')}
      poster={props.event.poster}
      title={props.event.name}
      subtitle={displayDateRange(get(props.event, 'start'), get(props.event, 'end'), true)}
      centered={true}
      toolbar="open edit | more"
      actions={event ? getActions([event], {
        add: () => dispatch(fetchActions.invalidate(STORE_NAME)),
        update: () => dispatch(fetchActions.invalidate(STORE_NAME)),
        delete: () => {
          dispatch(fetchActions.invalidate(STORE_NAME))

          props.fadeModal()
        }
      }, contextPath, currentUser, true) : undefined}
    >
      <div className="modal-body">
        {!loaded &&
          <ContentSkeleton length={1} />
        }

        {loaded &&
          <Content placeholder={trans('no_description')}>
            {get(event, 'description')}
          </Content>
        }

        <DetailsData
          className={classes(!loaded || get(event, 'description') ? 'mt-5' : undefined)}
          loaded={loaded}
          data={event || {}}
          definition={[
            {
              title: trans('general'),
              primary: true,
              fields: [
                {
                  name: 'meta.type',
                  type: 'translation',
                  label: trans('type'),
                  options: {domain: 'event'}
                }, {
                  name: 'location',
                  type: 'location',
                  label: trans('location'),
                  placeholder: trans('online')
                }
              ]
            }
          ]}
        />
      </div>
    </Modal>
  )
}

EventAboutModal.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  reload: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  EventAboutModal
}
