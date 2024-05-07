import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'
import {EditorPage} from '#/main/app/editor'

const ToolEditorOverview = (props) =>
  <EditorPage
    title={trans('overview')}
    disabled={props.disabled}
    definition={[
      {
        name: 'main',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'data.poster',
            label: trans('poster'),
            type: 'poster',
            hideLabel: true
          }
        ]
      }
    ].concat(props.definition)}
  >
    {props.children}
  </EditorPage>

ToolEditorOverview.propTypes = {
  disabled: T.bool,
  definition: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )),
  children: T.any
}

ToolEditorOverview.defaultProps = {
  definition : []
}

export {
  ToolEditorOverview
}
