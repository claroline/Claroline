import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config'
import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {UserAvatar} from '#/main/app/user/components/avatar'
import {route} from '#/main/core/workspace/routing'

import {MODAL_HISTORY} from '#/plugin/history/modals/history'
import {MODAL_CONTEXT_SEARCH} from '#/main/app/context/modals/search'
import {AppBrand} from '#/main/app/layout/components/brand'
import {MODAL_MY_NOTIFICATIONS} from '#/main/notification/modals/my-notifications'
import {NotificationButton} from '#/main/notification/components/button'

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
      <AppBrand className="menu-brand" />
      {false && []
        .concat(props.availableContexts)
        .filter(availableContext => availableContext.root)
        .sort((a, b) => {
          if (a.order < b.order) {
            return -1
          } else if (a.order > b.order) {
            return 1
          }
          return 0
        })
        .map((availableContext) => {
          if ('desktop' === availableContext.name) {
            return (
              <Button
                key={availableContext.name}
                type={LINK_BUTTON}
                className="app-context-btn position-relative"
                label={trans(availableContext.name, {}, 'context')}
                tooltip="right"
                target="/desktop"
              >
                <UserAvatar user={props.currentUser} size="md" noStatus={true}/>
              </Button>
            )
          }

          return (
            <Button
              key={availableContext.name}
              type={LINK_BUTTON}
              className="app-context-btn"
              icon={`fa fa-fw fa-${availableContext.icon}`}
              label={trans(availableContext.name, {}, 'context')}
              tooltip="right"
              target={'/'+availableContext.name}
            />
          )
        })
      }

      <Button
        type={LINK_BUTTON}
        className="app-context-btn position-relative"
        label={trans('desktop', {}, 'context')}
        tooltip="right"
        target="/desktop"
      >
        <UserAvatar user={props.currentUser} size="md" noStatus={true}/>
      </Button>

      <NotificationButton
        className="app-context-btn"
        tooltip="right"
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
          <div
            className="app-context-icon"
            style={!isEmpty(pinnedContext.thumbnail) ? {
              backgroundImage: `url(${asset(pinnedContext.thumbnail)})`,
              backgroundSize: 'cover',
              backgroundPosition: 'center'
            } : undefined}
          />
        </Button>
      ))}

      <Button
        className="app-context-btn"
        tooltip="right"
        {...{
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-history',
          label: trans('history', {}, 'history'),
          modal: [MODAL_HISTORY]
        }}
      />

      <Button
        type={MODAL_BUTTON}
        className="app-context-btn"
        icon="fa fa-fw fa-search"
        label={trans('search')}
        tooltip="right"
        modal={[MODAL_CONTEXT_SEARCH]}
      />
    </section>
  )
}

ContextNav.propTypes = {
  currentUser: T.shape({}),
  currentContext: T.shape({}),
  currentContextType: T.string,

  availableContexts: T.arrayOf(T.shape({

  })),
  favoriteContexts: T.arrayOf(T.shape({

  }))
}

export {
  ContextNav
}
