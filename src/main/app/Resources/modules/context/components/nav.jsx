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

      <ul className="list-unstyled d-flex flex-column gap-2 mb-0">
        <li>
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
        </li>

        <li>
          <NotificationButton
            className="app-context-btn"
            tooltip="right"
          />
        </li>

        <li>
          <Button
            type={MODAL_BUTTON}
            className="app-context-btn"
            icon="far fa-fw fa-compass"
            label={trans('search_and_history')}
            tooltip="right"
            modal={[MODAL_CONTEXT_SEARCH]}
          />
        </li>
      </ul>

      <hr className="app-context-separator mx-auto" aria-hidden={true} />

      {0 !== pinnedContexts.length &&
        <ul className="list-unstyled d-flex flex-column gap-2 mb-0">
          {pinnedContexts.map(pinnedContext => (
            <li key={pinnedContext.id || trans('loading')}>
              <Button
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
            </li>
          ))}
        </ul>
      }
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
