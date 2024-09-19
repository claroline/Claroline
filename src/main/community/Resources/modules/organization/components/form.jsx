import React from 'react'
import { useHistory } from 'react-router-dom'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {route} from '#/main/community/organization/routing'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {constants} from '#/main/community/organization/constants'

const OrganizationFormComponent = props => {
  const history = useHistory()

  return (
    <FormData
      className={props.className}
      level={3}
      name={props.name}
      buttons={true}
      save={{
        type: CALLBACK_BUTTON,
        callback: () => props.save(props.organization, props.isNew, props.name).then(organization => {
          history.push(route(organization, props.path))
        })
      }}
      cancel={{
        type: LINK_BUTTON,
        target: props.isNew ? props.path + '/organizations' : route(props.organization, props.path),
        exact: true
      }}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'code',
              type: 'string',
              label: trans('code'),
              required: true
            }
          ]
        }, {
          icon: 'fa fa-fw fa-circle-info',
          title: trans('information'),
          fields: [
            {
              name: 'meta.description',
              type: 'string',
              label: trans('description'),
              options: {
                long: true
              }
            }, {
              name: 'email',
              type: 'email',
              label: trans('email')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'poster',
              type: 'image',
              label: trans('poster')
            }, {
              name: 'thumbnail',
              type: 'image',
              label: trans('thumbnail')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          fields: [
            {
              name: 'restrictions.public',
              type: 'boolean',
              label: trans('make_organization_public', {}, 'community'),
              help: [
                trans('make_organization_public_help1', {}, 'community'),
                trans('make_organization_public_help2', {}, 'community')
              ]
            }
          ]
        }
      ]}
    >
      {props.children}
    </FormData>
  )
}

OrganizationFormComponent.propTypes = {
  className: T.string,
  path: T.string.isRequired,
  name: T.string.isRequired,
  isNew: T.bool.isRequired,
  organization: T.object.isRequired,
  save: T.func.isRequired,
  updateProp: T.func.isRequired,
  children: T.any
}

const OrganizationForm = connect(
  (state, ownProps) =>({
    isNew: formSelectors.isNew(formSelectors.form(state, ownProps.name)),
    organization: formSelectors.data(formSelectors.form(state, ownProps.name))
  }),
  (dispatch, ownProps) => ({
    save(organization, isNew, name) {
      return dispatch( formActions.saveForm(name, isNew ?
        ['apiv2_organization_create'] :
        ['apiv2_organization_update', {id: organization.id}])
      )
    },
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(OrganizationFormComponent)

export {
  OrganizationForm
}
