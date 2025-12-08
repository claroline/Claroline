import React, {useState} from 'react'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {url} from '#/main/app/api'
import {Html} from '#/main/app/components/html'
import {Button, pickAction, Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {route} from '#/main/app/context/routing'

import {selectors} from '#/main/app/context/store'

const Callout = ({
  className,
  title,
  content,
  type = 'primary',
  dismissible = true,
  actions = []
}) => {
  const [dismissed, setDismissed] = useState(false)

  if (dismissed) {
    return null
  }

  return (
    <div className={classes('rounded-2 p-3', `text-${type}-emphasis bg-${type}-subtle`, className)}>
      <div className="d-flex flex-row flex-nowrap align-items-baseline gap-1">
        {title &&
          <h6 className="mb-3 fs-sm flex-fill">
            {title}
          </h6>
        }

        {dismissible &&
          <Button
            className="btn btn-link p-1 text-reset mt-n1 me-n1"
            {...{
              name: 'close-callout',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-times',
              label: trans('hide', {}, 'actions'),
              tooltip: 'bottom',
              callback: () => setDismissed(true),
              displayed: dismissible
            }}
          />
        }
      </div>

      <Html as="p" className="fs-sm">{content}</Html>

      <Toolbar
        className="d-flex flex-column align-items-start gap-2"
        buttonName="fs-sm fw-bolder alert-link"
        actions={actions}
      />
    </div>
  )
}

const ContextCallout = ({className, actions}) => {
  const contextType = useSelector(selectors.type)
  const contextId = useSelector(selectors.id)
  const contextData = useSelector(selectors.data)
  const impersonated = useSelector(selectors.impersonated)
  const roles = useSelector(selectors.roles)

  if (impersonated) {
    return (
      <Callout
        className={className}
        type="warning"
        content={trans('workspace_impersonation', {role: roles[0] ? trans(roles[0].translationKey) : ''}, 'workspace')}
        dismissible={false}
        actions={[
          /*{
            name: 'change-role',
            type: CALLBACK_BUTTON,
            label: trans('Changer de rôle', {}, 'actions'),
            callback: () => true
          }, */{
            name: 'exit',
            type: URL_BUTTON,
            label: trans('exit', {}, 'actions'),
            target: url(['claro_index', {}], {view_as: 'exit'}) + '#' + route(contextType, contextId)
          }
        ]}
      />
    )
  }

  if (get(contextData, 'meta.archived')) {
    return (
      <Callout
        className={className}
        type="danger"
        title={trans('workspace_archive', {}, 'workspace')}
        content={trans('workspace_archive_desc', {}, 'workspace')}
        actions={Promise.all([
          pickAction('restore', actions),
          pickAction('delete', actions)
        ]).then(loadedActions => loadedActions
          .filter(a => !!a)
          .map(a => ({
            ...a,
            icon: undefined,
            label: <>{a.label} <span className="fa ms-1 fa-arrow-right" aria-hidden={true}/></>
          }))
        )}
      />
    )
  }

  if (get(contextData, 'meta.model', false)) {
    return (
      <Callout
        className={className}
        type="primary"
        title={trans('workspace_model', {}, 'workspace')}
        content={trans('workspace_model_desc', {}, 'workspace')}
        actions={Promise.all([
          pickAction('create-from-model', actions)
        ]).then(loadedActions => loadedActions
          .filter(a => !!a)
          .map(a => ({
            ...a,
            icon: undefined,
            label: <>{a.label} <span className="fa ms-1 fa-arrow-right" aria-hidden={true}/></>
          }))
        )}
      />
    )
  }
}

export {
  ContextCallout
}
