import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans, transChoice} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ContentLoader} from '#/main/app/content/components/loader'

import {constants} from '#/plugin/notification/modals/notifications/constants'
import {NotificationCard} from '#/plugin/notification/components/card'
import {route as accountRoute} from '#/main/core/account/routing'

const NotificationsModal = (props) =>
  <Modal
    {...omit(props, 'count', 'loaded', 'results', 'getNotifications')}
    icon="fa fa-fw fa-envelope"
    title={trans('notifications', {}, 'notification')}
    subtitle={transChoice('count_unread', props.count, {count: props.count}, 'notification')}
    onEntering={props.getNotifications}
  >

    {!props.loaded &&
      <ContentLoader
        size="lg"
        description={trans('notifications_loading', {}, 'notification')}
      />
    }

    {props.loaded && isEmpty(props.results) &&
      <div className="modal-body">
        <ContentPlaceholder
          size="lg"
          title={trans('empty_unread', {}, 'notification')}
          help={trans('empty_unread_help', {}, 'notification')}
        />
      </div>
    }

    {props.loaded && !isEmpty(props.results) &&
      <div className="data-cards-stacked data-cards-stacked-flush">
        {props.results.map(result =>
          <NotificationCard
            key={result.id}
            size="xs"
            direction="row"
            data={result}
          />
        )}
      </div>
    }

    {props.count > constants.LIMIT_RESULTS &&
      <div className="modal-footer justify-content-center">
        {transChoice('more_unread', props.count - constants.LIMIT_RESULTS, {count: props.count - constants.LIMIT_RESULTS}, 'notification')}
      </div>
    }

    <Button
      className="modal-btn"
      variant="btn"
      size="lg"
      type={LINK_BUTTON}
      label={trans('all_notifications', {}, 'notification')}
      target={accountRoute('notifications')}
      onClick={props.fadeModal}
      exact={true}
      primary={true}
    />
  </Modal>

NotificationsModal.propTypes = {
  count: T.number,
  results: T.array,
  loaded: T.bool.isRequired,
  getNotifications: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  NotificationsModal
}
