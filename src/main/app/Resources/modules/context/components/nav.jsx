import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {route} from '#/main/core/workspace/routing'
import {Thumbnail} from '#/main/app/components/thumbnail'

import {MODAL_CONTEXT_SEARCH} from '#/main/app/context/modals/search'
import {NotificationButton} from '#/main/notification/components/button'
import {PlatformOrganization} from '#/main/app/platform/components/organization'

const ContextNav = (props) => {
  if (!props.currentUser) {
    return null
  }

  let pinnedContexts = [].concat(props.favoriteContexts)
  if (!isEmpty(props.currentContext) && 'workspace' === props.currentContextType) {
    let currentPos = pinnedContexts.findIndex((context) => context.id === props.currentContext.id)
    if (-1 === currentPos) {
      pinnedContexts.unshift(props.currentContext)
    }
  }

  return (
    <section className="app-contexts">
      <PlatformOrganization />

      <Button
        type={LINK_BUTTON}
        className="app-context-btn position-relative"
        label={trans('desktop', {}, 'context')}
        tooltip="right"
        target="/desktop"
      >
        <Thumbnail
          size="sm"
          thumbnail={props.currentUser.picture}
          name={props.currentUser.name}
          square={true}
        />
      </Button>

      <NotificationButton
        className="app-context-btn"
        tooltip="right"
      />

      <Button
        type={MODAL_BUTTON}
        className="app-context-btn"
        icon="far fa-fw fa-compass"
        label={trans('search_and_history')}
        tooltip="right"
        modal={[MODAL_CONTEXT_SEARCH]}
      />

      <hr className="app-context-separator" />

      {pinnedContexts.map(pinnedContext => (
        <Button
          key={pinnedContext.id || trans('loading')}
          type={LINK_BUTTON}
          className="app-context-btn position-relative"
          label={pinnedContext.name || trans('loading')}
          tooltip="right"
          target={route(pinnedContext)}
        >
          <Thumbnail
            size="sm"
            thumbnail={pinnedContext.thumbnail}
            name={pinnedContext.name}
            square={true}
          />
        </Button>
      ))}
    </section>
  )
}

ContextNav.propTypes = {
  currentUser: T.shape({}),
  currentContext: T.shape({
    id: T.string
  }),
  currentContextType: T.string,

  availableContexts: T.arrayOf(T.shape({

  })),
  favoriteContexts: T.arrayOf(T.shape({

  }))
}

export {
  ContextNav
}
