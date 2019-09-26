import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {
  ISSUING_MODE_USER,
  ISSUING_MODE_GROUP,
  ISSUING_MODE_PEER,
  ISSUING_MODE_WORKSPACE,
  ISSUING_MODE_ORGANIZATION
} from '#/plugin/open-badge/tools/badges/badge/constants'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

// TODO : add tools
const BadgeFormComponent = (props) => {
  let modelChoice = {}

  if (props.models) {
    props.models.data.forEach(model => {
      modelChoice[model.code] = model.code
    })
  }

  const issuingChoices =  {
    [ISSUING_MODE_ORGANIZATION]: trans('issuing_mode_organization', {}, 'badge'),
    [ISSUING_MODE_USER]: trans('issuing_mode_user', {}, 'badge'),
    [ISSUING_MODE_GROUP]: trans('issuing_mode_group', {}, 'badge'),
    [ISSUING_MODE_PEER]: trans('issuing_mode_peer', {}, 'badge'),
    [ISSUING_MODE_WORKSPACE]: trans('issuing_mode_workspace', {}, 'badge')
  }

  return (
    <FormData
      {...props}
      name={selectors.STORE_NAME +'.badges.current'}
      buttons={true}
      target={(badge, isNew) => isNew ?
        ['apiv2_badge-class_create'] :
        ['apiv2_badge-class_update', {id: badge.id}]
      }
      cancel={{
        type: LINK_BUTTON,
        exact: true,
        target: props.path + '/badges'
      }}
      sections={[
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
              name: 'image',
              type: 'image',
              label: trans('image'),
              required: true
            }, {
              name: 'criteria',
              type: 'html',
              label: trans('criteria', {}, 'badge'),
              required: true
            }, {
              name: 'duration',
              type: 'number',
              label: trans('duration')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-info',
          title: trans('information'),
          fields: [
            {
              name: 'description',
              label: trans('description'),
              type: 'html'
            }, {
              name: 'tags',
              label: trans('tags'),
              type: 'tag'
            }, {
              name: 'issuer',
              type: 'organization',
              label: trans('issuer', {}, 'badge'),
              displayed: 'workspace' !== props.currentContext.type
            }
          ]
        }, {
          title: trans('allowed_issuers', {}, 'badge'),
          primary: false,
          fields: [
            {
              name: 'issuingMode',
              type: 'choice',
              label: trans('issuing_mode', {}, 'badge'),
              options: {
                choices: issuingChoices,
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
        }, {
          title: trans('automatic_award', {}, 'badge'),
          primary: false,
          fields:[
            {
              name: 'rules',
              label: trans('rules', {}, 'badge'),
              type: 'collection',
              options: {
                type: 'rule',
                placeholder: trans('no_rule', {}, 'badge'),
                button: trans('add_rule', {}, 'badge')
              }
            }
          ]
        }
      ]}
    >
      {props.children}
    </FormData>)
}

BadgeFormComponent.propTypes = {
  path: T.string.isRequired,
  currentContext: T.object.isRequired,
  new: T.bool.isRequired,
  badge: T.shape({
    // TODO : badge types
  }),
  updateProp: T.func.isRequired
}

const BadgeForm = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentContext: toolSelectors.context(state),
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.badges.current')),
    badge: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.badges.current'))
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
