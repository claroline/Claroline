import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as formSelectors} from '#/main/app/content/form/store'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/tag/tools/tags/store'
import {TaggedObjectCard} from '#/plugin/tag/card/components/tagged-object'

const TagFormComponent = (props) =>
  <FormData
    name={selectors.STORE_NAME + '.tag.form'}
    buttons={true}
    target={(tag, isNew) => isNew ?
      ['apiv2_tag_create'] :
      ['apiv2_tag_update', {id: tag.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
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
          }
        ]
      }, {
        icon: 'fa fa-fw fa-info',
        title: trans('information'),
        fields: [
          {
            name: 'meta.description',
            type: 'string',
            label: trans('description'),
            options: {
              long: true
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'color',
            label: trans('color'),
            type: 'color'
          }
        ]
      }
    ]}
  >
    <ListData
      name={selectors.STORE_NAME + '.tag.objects'}
      fetch={{
        url: ['apiv2_tag_list_objects', {id: props.tagId}],
        autoload: props.tagId && !props.new
      }}
      delete={{
        url: ['apiv2_tag_remove_objects', {id: props.tagId}]
      }}
      definition={[
        {
          name: 'name',
          label: trans('name'),
          type: 'string',
          displayed: true
        }
      ]}
      card={TaggedObjectCard}
    />
  </FormData>

TagFormComponent.propTypes = {
  path: T.bool.isRequired,
  new: T.bool.isRequired,
  tagId: T.string
}

const TagForm = connect(
  (state) => ({
    path: toolSelectors.path(state),
    new: formSelectors.isNew(formSelectors.form(state, selectors.STORE_NAME + '.tag.form')),
    tagId: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME + '.tag.form')).id
  })
)(TagFormComponent)

export {
  TagForm
}
