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

import {route as toolRoute} from '#/main/core/tool/routing'

import {constants} from '#/plugin/message/modals/messages/constants'
import {MessageCard} from '#/plugin/message/data/components/message-card'

const MessagesModal = (props) =>
  <Modal
    {...omit(props, 'count', 'loaded', 'results', 'getMessages')}
    icon="fa fa-fw fa-envelope"
    title={trans('messages', {}, 'message')}
    subtitle={transChoice('count_unread', props.count, {count: props.count}, 'message')}
    onEntering={props.getMessages}
  >

    {!props.loaded &&
      <ContentLoader
        size="lg"
        description={trans('messages_loading', {}, 'message')}
      />
    }

    {props.loaded && isEmpty(props.results) &&
      <div className="modal-body">
        <ContentPlaceholder
          size="lg"
          title={trans('empty_unread', {}, 'message')}
          help={trans('empty_unread_help', {}, 'message')}
        />
      </div>
    }

    {props.loaded && !isEmpty(props.results) &&
      <div className="data-cards-stacked data-cards-stacked-flush">
        {props.results.map(result =>
          <MessageCard
            key={result.id}
            size="xs"
            direction="row"
            data={result}
            primaryAction={{
              type: LINK_BUTTON,
              label: trans('open', {}, 'actions'),
              target: toolRoute('messaging') + '/message/' + result.id,
              onClick: props.fadeModal
            }}
          />
        )}
      </div>
    }

    {props.count > constants.LIMIT_RESULTS &&
      <div className="modal-footer justify-content-center">
        {transChoice('more_unread', props.count - constants.LIMIT_RESULTS, {count: props.count - constants.LIMIT_RESULTS}, 'message')}
      </div>
    }

    <Button
      className="modal-btn"
      variant="btn"
      size="lg"
      type={LINK_BUTTON}
      label={trans('all_messages', {}, 'message')}
      target={toolRoute('messaging')}
      onClick={props.fadeModal}
      exact={true}
      primary={true}
    />
  </Modal>

MessagesModal.propTypes = {
  count: T.number,
  results: T.array,
  loaded: T.bool.isRequired,
  getMessages: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  MessagesModal
}
