import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button, Toolbar} from '#/main/app/action'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {EditorPage} from '#/main/app/editor'

import {actions, selectors} from '#/plugin/claco-form/resources/claco-form/editor/store'
import {MODAL_CATEGORY_FORM} from '#/plugin/claco-form/modals/category'

const EditorCategories = () => {
  const dispatch = useDispatch()

  const clacoForm = useSelector(selectors.clacoForm)
  const categories = useSelector(selectors.categories)

  const addCategory = (category) => {
    const newCategories = [].concat(categories, [category])

    dispatch(actions.updateCategories(newCategories))
  }

  const updateCategory = (category) => {
    const newCategories = [].concat(categories)
    const categoryPos = newCategories.findIndex(cat => cat.id === category.id)

    if (-1 !== categoryPos) {
      newCategories[categoryPos] = category

      dispatch(actions.updateCategories(newCategories))
    }
  }

  const deleteCategory = (category) => {
    const newCategories = [].concat(categories)
    const categoryPos = newCategories.findIndex(cat => cat.id === category.id)

    if (-1 !== categoryPos) {
      newCategories.splice(categoryPos, 1)

      dispatch(actions.updateCategories(newCategories))
    }
  }

  return (
    <EditorPage
      title={trans('categories', {}, 'clacoform')}
      dataPart="resource"
      definition={[
        {
          id: 'general',
          title: trans('general'),
          fields: [
            {
              name: 'details.display_categories',
              type: 'boolean',
              label: trans('label_display_categories', {}, 'clacoform'),
              help: trans('display_categories_help', {}, 'clacoform')
            }
          ]
        }
      ]}
    >
      {isEmpty(categories) &&
        <ContentPlaceholder
          className="mb-3"
          title={trans('no_category', {}, 'clacoform')}
        />
      }

      {!isEmpty(categories) &&
        <ul className="list-group mb-3">
          {categories.map(category =>
            <li key={category.id} className="list-group-item d-flex gap-3 justify-content-between align-items-center">
              {category.name}

              <Toolbar
                buttonName="btn btn-link"
                toolbar="edit delete"
                size="sm"
                actions={[
                  {
                    name: 'edit',
                    label: trans('edit', 'actions'),
                    type: MODAL_BUTTON,
                    modal: [MODAL_CATEGORY_FORM, {
                      category: category,
                      fields: clacoForm.fields,
                      saveCategory: updateCategory
                    }]
                  }, {
                    name: 'delete',
                    label: trans('delete', 'actions'),
                    type: CALLBACK_BUTTON,
                    callback: () => deleteCategory(category)
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
        size="lg"
        label={trans('add_category', {}, 'clacoform')}
        modal={[MODAL_CATEGORY_FORM, {
          fields: clacoForm.fields,
          saveCategory: addCategory
        }]}
        primary={true}
      />
    </EditorPage>
  )
}

export {
  EditorCategories
}