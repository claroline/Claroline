import React, {useState, useEffect, useId} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Button, constants as actionConstants, pickActionSet} from '#/main/app/action'
import {CallbackButton, MenuButton, MODAL_BUTTON} from '#/main/app/buttons'
import {DataMicro} from '#/main/app/data/components/micro'
import {Thumbnail} from '#/main/app/components/thumbnail'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {route} from '#/main/app/context/routing'

import {actions as platformActions} from '#/main/app/platform/store'
import {MODAL_PLATFORM_ORGANIZATIONS} from '#/main/app/platform/modals/organizations'
import {actions, selectors} from '#/main/app/context/store'
import {ContextFavourite} from '#/main/app/context/components/favorite'
import {getActions} from '#/main/app/context/utils'
import {ContextCallout} from '#/main/app/context/components/callout'

const ContextSidebarActions = ({contextName, contextData, actions}) => {
  return (
    <div className={classes('d-flex flex-column justify-content-stretch position-relative mb-2', {
      'border-bottom': !get(contextData, 'poster')
    })}>
      {get(contextData, 'poster') &&
        <Thumbnail thumbnail={get(contextData, 'poster')} />
      }
      <div className={classes('d-flex flex-row flex-nowrap justify-content-stretch w-100 py-1 px-2', {
        'position-absolute start-0 top-0': get(contextData, 'poster')
      })} style={get(contextData, 'poster') && {background: 'linear-gradient(rgba(0, 0, 0, 0.65) 0%, rgba(0, 0, 0, 0.35) 75%, rgba(0, 0, 0, 0) 100%)'}}>
        <ContextFavourite className={classes('p-2 border-0', {
          'text-subtitles': get(contextData, 'poster')
        })} tooltip="bottom" />

        <MenuButton
          containerClassName="flex-fill"
          containerStyle={{minWidth: '1px'}}
          className={classes('p-2 d-flex flex-row gap-2 align-items-center justify-content-between w-100 fw-semibold focus-ring btn btn-text-body border-0', {
            'text-subtitles': get(contextData, 'poster')
          })}
          menu={{
            className: 'mt-1',
            style: {minWidth: '100%'},
            items: actions
          }}
        >
          <span className="text-truncate">
            {contextName}
          </span>
          <small aria-hidden="true" className="fa fa-fw fa-chevron-down" />
        </MenuButton>
      </div>
    </div>
  )
}

const ContextSidebar = ({
  className
}) => {
  const dispatch = useDispatch()
  const history = useHistory()

  const hasErrors = useSelector(selectors.hasErrors)

  const contextName = useSelector(selectors.name)
  const contextPath = useSelector(selectors.path)
  const contextType = useSelector(selectors.type)
  const contextData = useSelector(selectors.data)
  const toolLinks = useSelector(selectors.toolLinks)
  // get context organizations
  const organizations = useSelector(selectors.organizations)

  const [contextActions, setContextActions] = useState([])

  const currentUser = useSelector(securitySelectors.currentUser)
  const refresher = {
    add: () => dispatch(actions.reload()),
    update: () => dispatch(actions.reload()),
    delete: () => {
      history.push(route('desktop', null, 'workspaces'))
    }
  }
  useEffect(() => {
    getActions(contextType, [contextData], refresher, contextPath, currentUser).then((loadedActions) => {
      setContextActions(pickActionSet(actionConstants.ACTION_SET_DETAILS, loadedActions))
    })
  }, [contextType, contextData ? contextData.id : null])

  const menuId = useId()
  const menuTitleId = useId()
  const toolsTitleId = useId()
  const organizationsTitleId = useId()
  const organizationsDescId = useId()

  if (hasErrors) {
    return null
  }

  return (
    <div
      id={menuId}
      className={classes('app-context-menu app-menu d-flex flex-column flex-shrink-0 justify-content-stretch border-end', className)}
      role="navigation"
      aria-labelledby={menuTitleId}
    >
      <h1 id={menuTitleId} className="visually-hidden">{trans('context_menu')}</h1>

      <ContextSidebarActions
        contextName={contextName}
        contextData={contextData}
        actions={contextActions}
      />

      <ContextCallout className="mx-2" actions={contextActions} />

      {1 < toolLinks.length &&
        <div className="d-flex flex-column flex-fill" role="presentation">
          <h2 id={toolsTitleId} className="visually-hidden">{trans('tools')}</h2>
          <ul
            className="app-menu-items list-unstyled flex-fill px-0 mb-3 justify-content-start mt-2"
            aria-labelledby={toolsTitleId}
          >
            {toolLinks.map((toolLink, i) =>
              <li key={toolLink.name} className={classes('parameters' === toolLink.name && i === toolLinks.length - 1 && 'mt-auto')}>
                <Button
                  {...omit(toolLink, 'label')}
                  className="app-menu-item focus-ring"
                >
                  <span className="text-truncate w-100" role="presentation">{toolLink.label}</span>
                </Button>
              </li>
            )}
          </ul>
        </div>
      }

      {1 < organizations.length &&
        <div className="bg-body-tertiary p-4 d-flex flex-column mt-auto" role="presentation">
          <h2 id={organizationsTitleId} className="fs-sm text-body-secondary text-uppercase">
            {trans('organizations', {}, 'community')}
          </h2>
          <p id={organizationsDescId} className="visually-hidden">{trans('change_organization_help', {}, 'community')}</p>

          <ul
            className="list-unstyled d-flex flex-column gap-2 m-n1 mb-0"
            aria-labelledby={organizationsTitleId}
            aria-describedby={organizationsDescId}
          >
            {organizations.slice(0, 3).map(organization => (
              <li key={organization.id}>
                <CallbackButton
                  className="fw-bolder btn btn-link text-reset p-1 w-100 fs-sm"
                  callback={() => dispatch(platformActions.changeOrganization(organization))}
                >
                  <DataMicro object={organization} />
                </CallbackButton>
              </li>
            ))}
          </ul>

          {3 < organizations.length &&
            <Button
              className="btn btn-link ms-auto mt-3 mb-n1 me-n2"
              type={MODAL_BUTTON}
              label={trans('see_all', {}, 'actions')}
              modal={[MODAL_PLATFORM_ORGANIZATIONS, {
                organizations: organizations
              }]}
              size="sm"
            >
              <span className="fa fa-arrow-right ms-2" aria-hidden={true} />
            </Button>
          }
        </div>
      }
    </div>
  )
}

ContextSidebar.propTypes = {
  className: T.string
}

export {
  ContextSidebar
}
