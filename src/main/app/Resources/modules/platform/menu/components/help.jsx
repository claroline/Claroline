import React, {useId, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import {CloseButton} from 'react-bootstrap'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {LINK_BUTTON, MENU_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {Thumbnail} from '#/main/app/components/thumbnail'
import {Menu} from '#/main/app/overlays/menu'
import {Html} from '#/main/app/components/html'
import {Contact} from '#/main/app/components/contact'

import {selectors} from '#/main/app/platform/store'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/modals/terms-of-service'
import {MODAL_PRIVACY} from '#/main/privacy/modals/privacy'
import {MODAL_PLATFORM_ABOUT} from '#/main/app/platform/modals/about'

const HelpMenu = (props) => {
  const currentOrganization = useSelector(selectors.currentOrganization)

  const version = useSelector(selectors.version)
  const helpUrl = useSelector(selectors.helpUrl)

  const links = [
    {
      name: 'help',
      type: URL_BUTTON,
      label: trans('help'),
      target: helpUrl,
      children: (
        <>
          <span className="ms-2 fa fa-arrow-up-right-from-square fs-sm" aria-hidden={true} />
          <span className="visually-hidden" role="presentation">{trans('external_link')}</span>
        </>
      ),
      displayed: !!helpUrl
    }, {
      name: 'sitemap',
      type: LINK_BUTTON,
      label: trans('Plan du site'),
      target: '/',
      exact: true,
      displayed: false
    }, {
      name: 'accessibility',
      type: LINK_BUTTON,
      label: trans('Accessibilité : Non conforme'),
      target: '/',
      exact: true,
      displayed: false
    }, {
      name: 'terms-of-service',
      type: MODAL_BUTTON,
      label: trans('terms_of_service', {}, 'privacy'),
      modal: [MODAL_TERMS_OF_SERVICE]
    }, {
      name: 'privacy',
      type: MODAL_BUTTON,
      label: trans('privacy_policy', {}, 'privacy'),
      modal: [MODAL_PRIVACY]
    }
  ]

  return (
    <Menu id={props.id} className="app-user-menu flyout-menu p-0 position-fixed">
      <div className="flyout-menu-content d-flex flex-column flex-fill p-3" role="presentation">
        <div className="flyout-menu-close fs-sm rounded-pill bg-body position-absolute end-0 top-0" role="presentation">
          <CloseButton onClick={props.closeMenu} className="rounded-pill" aria-label={trans('close', {}, 'actions')} />
        </div>

        <Thumbnail
          thumbnail={get(currentOrganization, 'thumbnail')}
          name={get(currentOrganization, 'name')}
          square={true}
          size="md"
        />

        <h2 className="h5 mb-1 mt-3">
          {get(currentOrganization, 'name')}
        </h2>

        {get(currentOrganization, 'meta.description') &&
          <Html className="text-body-secondary mb-3">
            {get(currentOrganization, 'meta.description')}
          </Html>
        }

        <Contact
          className="mb-4"
          email={get(currentOrganization, 'email')}
          phone={get(currentOrganization, 'phone')}
          address={get(currentOrganization, 'address')}
        />

        <div className="list-group mb-4">
          {links
            .filter(link => undefined === link.displayed || link.displayed)
            .map((link) => (
              <Button
                key={link.name}
                {...link}
                className="list-group-item list-group-item-action focus-ring"
                onClick={props.closeMenu}
              />
            ))
          }
        </div>

        <Button
          className="mt-auto me-auto btn btn-link text-body-secondary"
          type={MODAL_BUTTON}
          modal={[MODAL_PLATFORM_ABOUT]}
          label={version}
          size="sm"
          onClick={props.closeMenu}
        />
      </div>
    </Menu>
  )
}

HelpMenu.propTypes = {
  id: T.string.isRequired,
  closeMenu: T.func.isRequired
}

const PlatformMenuHelp = (props) => {
  const menuId = useId()
  const [menuOpened, setMenuOpened] = useState(false)

  return (
    <div role="contentinfo">
      <Button
        type={MENU_BUTTON}
        icon="fa fa-question"
        label={trans('help_center')}
        tooltip={props.vertical ? 'right' : 'top'}
        className="app-context-btn focus-ring rounded-circle"
        opened={menuOpened}
        onToggle={setMenuOpened}
        menu={{
          drop: 'end',
          render: () => (
            <HelpMenu
              id={menuId}
              closeMenu={() => setMenuOpened(false)}
            />
          )
        }}
        aria-controls={menuId}
      />
    </div>
  )
}

export {
  PlatformMenuHelp
}
