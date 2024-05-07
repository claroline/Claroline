import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button, Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {EditorPage} from '#/main/app/editor'

import {actions, selectors} from '#/plugin/claco-form/resources/claco-form/editor/store'
import {MODAL_KEYWORD_FORM} from '#/plugin/claco-form/modals/keyword'

const EditorKeywords = () => {
  const dispatch = useDispatch()
  const clacoForm = useSelector(selectors.clacoForm)

  const keywords = useSelector(selectors.keywords)

  const addKeyword = (keyword) => {
    const newKeywords = [].concat(keywords, [keyword])

    dispatch(actions.updateKeywords(newKeywords))
  }

  const updateKeyword = (keyword) => {
    const newKeywords = [].concat(keywords)
    const keywordPos = newKeywords.findIndex(key => key.id === keyword.id)

    if (-1 !== keywordPos) {
      newKeywords[keywordPos] = keyword

      dispatch(actions.updateKeywords(newKeywords))
    }
  }

  const deleteKeyword = (keyword) => {
    const newKeywords = [].concat(keywords)
    const keywordPos = newKeywords.findIndex(key => key.id === keyword.id)

    if (-1 !== keywordPos) {
      newKeywords.splice(keywordPos, 1)

      dispatch(actions.updateKeywords(newKeywords))
    }
  }

  return (
    <EditorPage
      title={trans('keywords', {}, 'clacoform')}
      dataPart="resource"
      definition={[
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
    >
      {isEmpty(keywords) &&
        <ContentPlaceholder
          className="mb-3"
          title={trans('no_keyword', {}, 'clacoform')}
        />
      }

      {!isEmpty(keywords) &&
        <ul className="list-group mb-3">
          {keywords.map(keyword =>
            <li key={keyword.id} className="list-group-item d-flex gap-3 justify-content-between align-items-center">
              {keyword.name}

              <Toolbar
                buttonName="btn btn-link"
                toolbar="edit delete"
                size="sm"
                actions={[
                  {
                    name: 'edit',
                    label: trans('edit', 'actions'),
                    type: MODAL_BUTTON,
                    modal: [MODAL_KEYWORD_FORM, {
                      keyword: keyword,
                      clacoFormId: clacoForm.id,
                      saveKeyword: updateKeyword
                    }]
                  }, {
                    name: 'delete',
                    label: trans('delete', 'actions'),
                    type: CALLBACK_BUTTON,
                    callback: () => deleteKeyword(keyword)
                  }
                ]}
              />
            </li>
          )}
        </ul>
      }

      <Button
        type={MODAL_BUTTON}
        className="btn btn-primary w-100 mb-3"
        label={trans('add_keyword', {}, 'clacoform')}
        size="lg"
        modal={[MODAL_KEYWORD_FORM, {
          clacoFormId: clacoForm.id,
          saveKeyword: addKeyword
        }]}
        disabled={!get(clacoForm, 'details.keywords_enabled', false)}
        primary={true}
      />
    </EditorPage>
  )
}

export {
  EditorKeywords
}