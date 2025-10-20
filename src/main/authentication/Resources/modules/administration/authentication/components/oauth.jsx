import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {Button, Toolbar} from '#/main/app/action'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
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

  const oauthProviders = useSelector(selectors.oauthProviders)
  const oauthClients = useSelector(selectors.oauthClients)

  return (
    <>
      <Button
        className="btn btn-primary me-auto"
        type={MODAL_BUTTON}
        icon="fa fa-fw fa-plus"
        label={trans('add_oauth_client', {}, 'actions')}
        modal={[MODAL_OAUTH_CREATION, {
          providers: oauthProviders,
          onCreate: (createdClient) => dispatch(actions.addOauthClient(createdClient))
        }]}
      />

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
