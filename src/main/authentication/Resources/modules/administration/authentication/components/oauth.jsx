import React, {useState} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {Collapse} from 'react-bootstrap'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {copy} from '#/main/app/clipboard'
import {Button, Toolbar} from '#/main/app/action'
import {ASYNC_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Badge} from '#/main/app/components/badge'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {MODAL_OAUTH_CREATION} from '#/main/authentication/oauth/modals/creation'
import {MODAL_OAUTH_FORM} from '#/main/authentication/oauth/modals/form'
import {actions, selectors} from '#/main/authentication/administration/authentication/store'

const AuthenticationOauthClient = ({
  provider,
  client,
  actions = []
}) => {
  return (
    <li className="list-group-item d-flex flex-row align-items-center gap-3">
      <span className={classes('fs-2 fa-fw', provider.icon)} aria-hidden={true} />

      <div className="flex-fill">
        <b className="fw-semibold h6 mb-1 d-inline-block">
          {client.name}
          {get(client, 'meta.disabled') &&
            <Badge className="ms-2" variant="warning">{trans('disabled')}</Badge>
          }
        </b>

        <div className="fs-sm d-flex flex-row gap-1 align-items-baseline">
          <b>{trans('client_id', {}, 'security')}</b>
          <code className="text-body-secondary">{client.clientId}</code>
          <span className="mx-1">-</span>
          <b>{trans('client_secret', {}, 'security')}</b>
          <code className="text-body-secondary">{client.clientSecretDisplay}</code>
        </div>
      </div>

      {!isEmpty(actions) &&
        <Toolbar
          className="me-n2"
          buttonName="btn btn-text-body px-2"
          tooltip="bottom"
          actions={actions}
        />
      }
    </li>
  )
}

const AuthenticationOauth = () => {
  const dispatch = useDispatch()

  const [showHelp, setShowHelp] = useState(false)
  const oauthProviders = useSelector(selectors.oauthProviders)
  const oauthClients = useSelector(selectors.oauthClients)
  const oauthRedirect = useSelector(selectors.oauthRedirect)

  return (
    <>
      <div className="d-flex flex-row gap-1" role="presentation">
        <Button
          className="btn btn-primary"
          type={MODAL_BUTTON}
          label={trans('add_oauth_client', {}, 'actions')}
          modal={[MODAL_OAUTH_CREATION, {
            providers: oauthProviders,
            onCreate: (createdClient) => dispatch(actions.addOauthClient(createdClient))
          }]}
        />

        <Button
          className="btn btn-text-body focus-ring"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-question-circle"
          label={trans(showHelp ? 'hide_help' : 'show_help', {}, 'actions')}
          callback={() => setShowHelp(!showHelp)}
        />
      </div>

      <Collapse in={showHelp}>
        <div className="p-4 mb-0 bg-body-tertiary rounded-3 gap-0">
          <h3 className="h5">{trans('oauth_create_client', {}, 'security')}</h3>
          <p>{trans('oauth_require_client_help', {}, 'security')}</p>
          <p>{trans('oauth_create_client_help', {}, 'security')}</p>

          <h3 className="h5 mt-2">{trans('oauth_configure_client', {}, 'security')}</h3>
          <p>{trans('oauth_configure_client_help', {}, 'security')}</p>

          <div className="p-2 px-3 d-flex flex-row align-items-center gap-2 rounded-2 border mb-3 bg-body">
            <b className="text-truncate">{oauthRedirect}</b>
            <Button
              className="btn btn-text-body p-2 foxus-ring p-1 ms-auto my-n1 me-n1"
              type={CALLBACK_BUTTON}
              icon="fa fa-copy"
              label={trans('clipboard_copy', {}, 'actions')}
              callback={() => copy(oauthRedirect)}
              tooltip="bottom"
            />
          </div>

          <h3 className="h5 mt-2">{trans('oauth_register_client', {}, 'security')}</h3>
          <p>{trans('oauth_register_client_help', {}, 'security')}</p>
        </div>
      </Collapse>

      {isEmpty(oauthClients) &&
        <ContentPlaceholder title={trans('no_oauth_client', {}, 'security')} />
      }

      {!isEmpty(oauthClients) &&
        <ul className="list-group">
          {oauthClients.map(client =>
            <AuthenticationOauthClient
              key={client.id}
              client={client}
              provider={oauthProviders.find(provider => provider.name === client.serviceProvider)}
              actions={[
                {
                  name: 'edit',
                  type: MODAL_BUTTON,
                  icon: 'fa fa-fw fa-pencil',
                  label: trans('edit', {}, 'actions'),
                  modal: [MODAL_OAUTH_FORM, {
                    isNew: false,
                    client: client,
                    provider: oauthProviders.find(provider => provider.name === client.serviceProvider),
                    onSave: (updatedClient) => dispatch(actions.updateOauthClient(updatedClient))
                  }]
                }, {
                  name: 'delete',
                  type: ASYNC_BUTTON,
                  icon: 'fa fa-fw fa-trash',
                  label: trans('delete', {}, 'actions'),
                  request: {
                    url: ['apiv2_authentication_oauth_client_delete', {id: client.id}],
                    request: {method: 'DELETE'},
                    success: () => dispatch(actions.deleteOauthClient(client))
                  },
                  confirm: true,
                  dangerous: true
                }
              ]}
            />
          )}
        </ul>
      }
    </>
  )
}

export {
  AuthenticationOauth
}
