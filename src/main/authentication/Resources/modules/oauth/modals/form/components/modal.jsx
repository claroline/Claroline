import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import classes from 'classnames'
import capitalize from 'lodash/capitalize'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {selectors as platformSelectors} from '#/main/app/platform/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {FormModal} from '#/main/app/data/modals/form'
import {OauthClient} from '#/main/authentication/oauth/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {DataInput} from '#/main/app/data/components/input'
import {getOauthApp} from '#/main/authentication/oauth'

const STORE_NAME = 'oauthForm'

const OauthFormFields = ({update, fieldsMapping = {}}) => {
  const definedFields = [
    'username',
    'email',
    'name',
    'lastName',
    'firstName'
  ]

  return (
    <FormGroup
      label={trans('oauth_fields_mapping', {}, 'security')}
      help={trans('oauth_fields_mapping_help', {}, 'security')}
    >
      <ul className="list-group mb-0">
        {definedFields.map((field) => (
          <li key={field} className={classes('list-group-item d-flex flex-row align-items-center gap-3')}>
            <label htmlFor={field} className="form-label w-50 mb-0">
              {trans(field)}
            </label>
            <DataInput
              className="flex-fill"
              id={field}
              type="string"
              size="sm"
              value={get(fieldsMapping, field)}
              onChange={(value) => update(field, value)}
              required={true}
            />
          </li>
        ))}
      </ul>
    </FormGroup>
  )
}

OauthFormFields.propTypes = {
  fieldsMapping: T.object,
  update: T.func.isRequired
}

const OauthFormModal = props => {
  const dispatch = useDispatch()
  const [oauthDefinition, setOauthDefinition] = useState()

  const currentOrganization = useSelector(platformSelectors.currentOrganization)
  const formData = useSelector((state) => formSelectors.data(formSelectors.form(state, STORE_NAME)))

  useEffect(() => {
    if (get(props.provider, 'name')) {
      getOauthApp(props.provider.name).then(app => {
        setOauthDefinition(app.default)
      })
    }
  }, [get(props.provider, 'name')])

  return (
    <FormModal
      {...omit(props, 'client')}
      name={STORE_NAME}
      title={trans(props.isNew ? 'new_oauth' : 'oauth', {}, 'security')}
      data={props.client || {
        serviceProvider: props.provider.name,
        name: capitalize(props.provider.name),
        fieldsMapping: props.provider.defaultMapping,
        organization: currentOrganization,
        button: {
          displayed: true
        }
      }}
      target={props.isNew ?
        ['apiv2_authentication_oauth_client_create'] :
        ['apiv2_authentication_oauth_client_update', {id: props.client.id}]
      }
      saveLabel={trans('save', {}, 'actions')}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'serviceProvider',
              label: trans('type'),
              type: 'type',
              hideLabel: true,
              calculated: () => ({
                icon: <span className={classes('fs-2', props.provider.icon)} aria-hidden={true} />,
                name: trans(props.provider.name+'_oauth', {}, 'security'),
                description: trans(props.provider.name+'_oauth_desc', {}, 'security')
              })
            }, {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'organization',
              type: 'organization',
              label: trans('organization', {}, 'community'),
              options: {
                multiple: false
              }
            }, {
              name: 'meta.disabled',
              type: 'boolean',
              label: trans('disable', {}, 'actions'),
              help: trans('disable_oauth_help', {}, 'security')
            }, {
              name: 'button.displayed',
              type: 'boolean',
              label: trans('display_on_login_page', {}, 'security'),
              help: trans('oauth_display_help', {}, 'security')
            }
          ]
        }, {
          title: trans('client'),
          hideTitle: true,
          primary: true,
          fields: [
            {
              name: 'clientId',
              type: 'string',
              label: trans('client_id', {}, 'security'),
              help: trans('client_id_help', {}, 'security'),
              required: true
            }, {
              name: 'clientSecret',
              type: 'string',
              label: trans('client_secret', {}, 'security'),
              help: trans('client_secret_help', {}, 'security'),
              required: true,
              placeholder: props.client ? props.client.clientSecretDisplay : ''
            }
          ].concat(oauthDefinition && !!oauthDefinition.configure ? oauthDefinition.configure(formData, (key, value) => dispatch(formActions.updateProp(STORE_NAME, key, value))) : [], [
            {
              name: 'fieldsMapping',
              type: 'string',
              displayed: oauthDefinition && oauthDefinition.fieldsMapping,
              render: () => (
                <OauthFormFields
                  fieldsMapping={formData ? formData.fieldsMapping : {}}
                  update={(key, value) => dispatch(formActions.updateProp(STORE_NAME, 'fieldsMapping', Object.assign({}, formData.fieldsMapping, {[key]: value})))}
                />
              )
            }
          ])
        }, {
          title: trans('parameters'),
          hideTitle: true,
          primary: true,
          fields: [
            {
              name: 'createOnLogin',
              type: 'boolean',
              label: trans('create_on_login', {}, 'security'),
              help: trans('create_on_login_help', {}, 'security')
            }, {
              name: 'reactivateOnLogin',
              type: 'boolean',
              label: trans('reactivate_on_login', {}, 'security'),
              help: trans('reactivate_on_login_help', {}, 'security')
            }
          ]
        }, {
          title: trans('oauth_button', {}, 'security'),
          description: trans('oauth_button_help', {}, 'security'),
          primary: true,
          displayed: (client) => get(client, 'button.displayed', false),
          fields: [
            {
              name: 'button.icon',
              type: 'icon',
              label: trans('icon')
            }, {
              name: 'button.label',
              type: 'string',
              label: trans('label'),
              placeholder: formData ? formData.name : ''
            }, {
              name: 'button.confirm',
              type: 'html',
              label: trans('oauth_button_confirm', {}, 'security'),
              help: trans('oauth_button_confirm_help', {}, 'security')
            }
          ]
        }
      ]}
    />
  )
}

OauthFormModal.propTypes = {
  isNew: T.bool,
  provider: T.shape({
    icon: T.string,
    name: T.string.isRequired,
    defaultMapping: T.object
  }).isRequired,
  client: T.shape(
    OauthClient.propTypes
  ),
  onSave: T.func
}

export {
  OauthFormModal
}
