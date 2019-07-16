import React from 'react'
import {connect} from 'react-redux'
import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

import {
  ISSUING_MODE_ORGANIZATION,
  ISSUING_MODE_USER,
  ISSUING_MODE_GROUP,
  ISSUING_MODE_PEER,
  ISSUING_MODE_WORKSPACE,
  ISSUING_MODE_AUTO
} from '#/plugin/open-badge/tools/badges/badge/constants'

import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

// TODO : add tools
const BadgeFormComponent = (props) => {
  let modelChoice = {}

  if (props.models) {
    props.models.data.forEach(model => {
      modelChoice[model.code] = model.code
    })
  }

  const fields = [
    {
      name: 'name',
      type: 'string',
      label: trans('name'),
      required: true
    },
    {
      name: 'description',
      type: 'html',
      label: trans('description'),
      required: true
    },
    {
      name: 'criteria',
      type: 'html',
      label: trans('criteria'),
      required: true
    },
    {
      name: 'image',
      type: 'file',
      label: trans('image'),
      required: false
    },
    {
      name: 'issuer',
      type: 'organization',
      label: trans('issuer'),
      required: true
    },
    {
      name: 'duration',
      type: 'number',
      label: trans('duration'),
      required: false
    },
    {
      name: 'tags',
      type: 'string',
      required: false,
      label: trans('tags'),
      help: trans('tag_form_help', {}, 'forum')
    }
  ]

  if (props.workspace !== null) {
    fields.splice(4, 1)
  }

  return (
    <FormData
      {...props}
      name={selectors.STORE_NAME +'.badges.current'}
      meta={true}
      buttons={true}
      target={(badge, isNew) => isNew ?
        ['apiv2_badge-class_create'] :
        ['apiv2_badge-class_update', {id: badge.id}]
      }
      sections={[
        {
          title: trans('badge'),
          primary: true,
          fields
        },
        {
          title: trans('allowed_issuer'),
          primary: false,
          fields: [
            {
              name: 'issuingMode',
              type: 'choice',
              label: trans('issuing_mode'),
              options: {
                choices:  {
                  [ISSUING_MODE_ORGANIZATION]: trans(ISSUING_MODE_ORGANIZATION),
                  [ISSUING_MODE_USER]: trans(ISSUING_MODE_USER),
                  [ISSUING_MODE_GROUP]: trans(ISSUING_MODE_GROUP),
                  [ISSUING_MODE_PEER]: trans(ISSUING_MODE_PEER),
                  [ISSUING_MODE_WORKSPACE]: trans(ISSUING_MODE_WORKSPACE),
                  [ISSUING_MODE_AUTO]: trans(ISSUING_MODE_AUTO)
                },
                multiple: true
              }
            }, {
              name: 'allowedUsers',
              label: trans('users'),
              type: 'collection',
              displayed: badge =>  badge.issuingMode && badge.issuingMode.indexOf(ISSUING_MODE_USER) > -1,
              options: {
                type: 'user',
                placeholder: trans('no_user'),
                button: trans('add_user')
              }
            }, {
              name: 'allowedGroups',
              label: trans('groups'),
              type: 'collection',
              displayed: badge =>  badge.issuingMode && badge.issuingMode.indexOf(ISSUING_MODE_GROUP) > -1,
              options: {
                type: 'group',
                placeholder: trans('no_group'),
                button: trans('add_group')
              }
            }
          ]
        }
      ]}
    >
      {props.children}
    </FormData>)
}

const BadgeForm = connect(
  (state) => ({
    currentContext: state.currentContext,
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME +'.badges.current')),
    badge: formSelect.data(formSelect.form(state, selectors.STORE_NAME +'.badges.current')),
    workspace: state.workspace
  }),
  (dispatch, ownProps) =>({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(BadgeFormComponent)

export {
  BadgeForm
}
