import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {ClacoForm as ClacoFormTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'

import {MODAL_KEYWORD_FORM} from '#/plugin/claco-form/modals/keyword'

const EditorKeywords = props =>
  <Fragment>
    <FormData
      level={2}
      title={trans('keywords', {}, 'clacoform')}
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
          primary: true,
          fields: [
            {
              name: 'details.keywords_enabled',
              type: 'boolean',
              label: trans('label_keywords_enabled', {}, 'clacoform'),
              linked: [
                {
                  name: 'details.new_keywords_enabled',
                  type: 'boolean',
                  label: trans('label_new_keywords_enabled', {}, 'clacoform'),
                  displayed: (clacoForm) => get(clacoForm, 'details.keywords_enabled')
                }
              ]
            }, {
              name: 'details.display_keywords',
              type: 'boolean',
              label: trans('label_display_keywords', {}, 'clacoform')
            }
          ]
        }
      ]}
    />

    <ListData
      name={selectors.STORE_NAME+'.clacoFormForm.keywords'}
      fetch={{
        url: ['apiv2_clacoformkeyword_list', {clacoForm: props.clacoForm.id}],
        autoload: !!props.clacoForm.id
      }}
      definition={[
        {
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true
        }
      ]}
      actions={(rows) => [
        {
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_KEYWORD_FORM, {
            keyword: rows[0],
            clacoFormId: props.clacoForm.id,
            onSave: (keyword) => props.updateKeyword(keyword)
          }],
          scope: ['object']
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete', {}, 'actions'),
          dangerous: true,
          confirm: {
            title: trans('objects_delete_title'),
            message: transChoice('objects_delete_question', rows.length, {count: rows.length}, 'platform')
          },
          callback: () => props.deleteKeywords(rows)
        }
      ]}
    />

    <Button
      type={MODAL_BUTTON}
      className="btn btn-block btn-emphasis component-container"
      label={trans('create_a_keyword', {}, 'clacoform')}
      modal={[MODAL_KEYWORD_FORM, {
        clacoFormId: props.clacoForm.id,
        onSave: (keyword) => props.addKeyword(keyword)
      }]}
      disabled={!props.clacoForm.details || !props.clacoForm.details.keywords_enabled}
      primary={true}
    />
  </Fragment>

EditorKeywords.propTypes = {
  path: T.string.isRequired,
  clacoForm: T.shape(
    ClacoFormTypes.propTypes
  ),
  addKeyword: T.func.isRequired,
  updateKeyword: T.func.isRequired,
  deleteKeywords: T.func.isRequired
}

export {
  EditorKeywords
}