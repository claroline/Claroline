import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'
import {UserMessage} from '#/main/core/user/message/components/user-message'

const LastMessages = (props) =>
  <section>
    <h3 className="h2">{trans('last_messages', {}, 'forum')}</h3>
    <ul className="posts">
      {props.lastMessages.map(message =>
        <li key={message.id} className="post">
          <h4>{message.subject.title}
            <Button
              label={trans('see_subject', {}, 'forum')}
              type={LINK_BUTTON}
              target={`${props.path}/subjects/show/${message.subject.id}`}
              className="btn-link mx-2"
              primary={true}
            />
          </h4>
          <UserMessage
            user={message.meta.creator}
            date={message.meta.created}
            content={message.content}
            allowHtml={true}
          />
        </li>
      )}
    </ul>
  </section>

LastMessages.propTypes = {
  path: T.string.isRequired,
  lastMessages: T.array.isRequired
}

export {
  LastMessages
}
