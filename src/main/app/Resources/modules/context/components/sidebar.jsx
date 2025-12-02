import React, {useState, useEffect, useCallback, useId} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Button, constants as actionConstants, pickActionSet} from '#/main/app/action'
import {CALLBACK_BUTTON, CallbackButton, MenuButton, MODAL_BUTTON} from '#/main/app/buttons'
import {DataMicro} from '#/main/app/data/components/micro'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {actions as platformActions} from '#/main/app/platform/store'
import {MODAL_PLATFORM_ORGANIZATIONS} from '#/main/app/platform/modals/organizations'
import {selectors} from '#/main/app/context/store'
import {ContextFavourite} from '#/main/app/context/components/favorite'
import {useLocaleStorage} from '#/main/app/storage'
import {getActions} from '#/main/app/context/utils'
import {Thumbnail} from '#/main/app/components/thumbnail'

const ContextSidebarCallout = () => {
  const [dismissed, setDismissed] = useState(false)

  if (dismissed) {
    return null
  }

  return (
    <div className="rounded-2 text-primary-emphasis bg-primary-subtle p-3 m-2">
      <div className="d-flex flex-row flex-nowrap align-items-baseline gap-1">
        <h6 className="mb-3 fs-sm flex-fill">
          Modèle d'espace d'activités
        </h6>
        <Button
          className="btn btn-link p-1 text-reset mt-n1 me-n1"
          {...{
            name: 'close-banner',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-times',
            label: trans('hide', {}, 'actions'),
            tooltip: 'bottom',
            callback: () => setDismissed(true),
            //displayed: props.dismissible
          }}
        />
      </div>

      <p className="fs-sm">
        Cet espace est un modèle utilisé pour la création de nouveaux espaces d'activités.
      </p>
      <a href="" className="fs-sm fw-bolder alert-link">Créer <span className="fa ms-1 fa-arrow-right" /></a>
      {/*<Button
          className="btn btn-primary"
          type={CALLBACK_BUTTON}
          {...{
            name: 'create',
            type: CALLBACK_BUTTON,
            label: trans('Créer à partir du modèle', {}, 'actions'),
            callback: () => true
          }}
        />*/}
    </div>
  )
}

const ContextSidebarActions = ({contextName, contextType, contextData, contextPath}) => {
  const [actions, setActions] = useState([])

  const currentUser = useSelector(securitySelectors.currentUser)

  useEffect(() => {
    getActions(contextType, [contextData], {}, contextPath, currentUser).then((loadedActions) => {
      setActions(pickActionSet(actionConstants.ACTION_SET_DETAILS, loadedActions))
    })
  }, [contextType, contextData ? contextData.id : null])

  return (
    <div className="d-flex flex-column justify-content-stretch position-relative">
      {get(contextData, 'poster') &&
        <Thumbnail thumbnail={get(contextData, 'poster')} />
      }
      <div className={classes('d-flex flex-column justify-content-stretch w-100 py-1', {
        'position-absolute start-0 top-0': get(contextData, 'poster')
      })} style={{background: 'linear-gradient(rgba(0, 0, 0, 0.65) 0%, rgba(0, 0, 0, 0.35) 75%, rgba(0, 0, 0, 0) 100%)'}}>
        <MenuButton
          className={classes('p-3 py-2 d-flex flex-row gap-2 align-items-center justify-content-between w-100 fw-bold', {
            'text-subtitles': get(contextData, 'poster')
          })}
          menu={{
            className: 'w-100 mt-1',
            items: actions
            /*drop: 'down-centered'*/
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

  const notFound = useSelector(selectors.notFound)
  const hasErrors = useSelector(selectors.hasErrors)

  const contextName = useSelector(selectors.name)
  const contextPath = useSelector(selectors.path)
  const contextType = useSelector(selectors.type)
  const contextData = useSelector(selectors.data)
  const toolLinks = useSelector(selectors.toolLinks)
  // get context organizations
  const organizations = useSelector(selectors.organizations)

  const [pinedMenu, setPinedMenu] = useLocaleStorage('contextMenuPined', false)
  const toggleMenu = useCallback(() => {
    setPinedMenu(!pinedMenu)
    setTimeout(() => {
      document.querySelector('.app-context-menu-toggle').focus()
    }, 0)
  }, [contextPath])



  const menuId = useId()
  const menuTitleId = useId()
  const toolsTitleId = useId()
  const organizationsTitleId = useId()
  const organizationsDescId = useId()

  if (notFound || hasErrors) {
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
      <div className="d-flex flex-row align-items-center" role="presentation">
        <Button
          id="toggle-menu"
          type={CALLBACK_BUTTON}
          className="app-context-menu-toggle btn btn-text-body my-1 ms-2 focus-ring"
          icon="fa fa-angles-left"
          label={trans('close_context_menu', {}, 'actions')}
          tooltip="bottom"
          callback={toggleMenu}
          aria-expanded={true}
          aria-controls={menuId}
        />
        <ContextFavourite className="text-start" />
      </div>

      <ContextSidebarActions
        contextName={contextName}
        contextPath={contextPath}
        contextData={contextData}
        contextType={contextType}
      />

      <ContextSidebarCallout />

      {1 < toolLinks.length &&
        <div className="d-flex flex-column flex-fill" role="presentation">
          <h2 id={toolsTitleId} className="visually-hidden">{trans('tools')}</h2>
          <ul
            className="app-menu-items list-unstyled flex-fill px-0 mb-3 justify-content-start"
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
