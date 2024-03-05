import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {ToolEditor as ToolEditorComponent} from '#/main/core/tool/editor/components/main'
import {reducer, selectors} from '#/main/core/tool/editor/store'

const ToolEditor = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({

    })
  )(ToolEditorComponent)
)

export {
  ToolEditor
}
