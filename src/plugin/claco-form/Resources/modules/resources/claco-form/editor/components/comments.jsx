import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {ClacoForm as ClacoFormTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {constants} from '#/plugin/claco-form/resources/claco-form/constants'


const EditorComments = props =>
  <FormData
    level={2}
    title={trans('comments', {}, 'clacoform')}
    name={selectors.STORE_NAME+'.clacoFormForm'}
    buttons={true}
    target={(clacoForm) => ['apiv2_clacoform_update', {id: clacoForm.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        id: 'general',
        title: trans('general'),
        fields: [
          {
            name: 'details.comments_enabled',
            type: 'boolean',
            label: trans('label_comments_enabled', {}, 'clacoform'),
            linked: [
              {
                name: 'details.comments_roles',
                type: 'choice',
                label: trans('enable_comments_for_roles', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.comments_enabled'),
                options: {
                  multiple: true,
                  condensed: true,
                  choices: props.roles.reduce((acc, r) => Object.assign(acc, {
                    [r.name]: trans(r.translationKey)
                  }), {})
                }
              }, {
                name: 'details.moderate_comments',
                type: 'choice',
                label: trans('label_moderate_comments', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.comments_enabled'),
                required: true,
                options: {
                  noEmpty: true,
                  condensed: true,
                  choices: constants.MODERATE_COMMENTS_CHOICES
                }
              }
            ]
          }, {
            name: 'details.display_comments',
            type: 'boolean',
            label: trans('label_display_comments', {}, 'clacoform'),
            linked: [
              {
                name: 'details.comments_display_roles',
                type: 'choice',
                label: trans('display_comments_for_roles', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.display_comments'),
                options: {
                  multiple: true,
                  condensed: true,
                  choices: props.roles.reduce((acc, r) => Object.assign(acc, {
                    [r.name]: trans(r.translationKey)
                  }), {})
                }
              }, {
                name: 'details.open_comments',
                type: 'boolean',
                label: trans('label_open_panel_by_default', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.display_comments')
              }, {
                name: 'details.display_comment_author',
                type: 'boolean',
                label: trans('label_display_comment_author', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.display_comments')
              }, {
                name: 'details.display_comment_date',
                type: 'boolean',
                label: trans('label_display_comment_date', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.display_comments')
              }
            ]
          }
        ]
      }
    ]}
  />

EditorComments.propTypes = {
  path: T.string.isRequired,
  clacoForm: T.shape(
    ClacoFormTypes.propTypes
  ),
  roles: T.array
}

export {
  EditorComments
}
