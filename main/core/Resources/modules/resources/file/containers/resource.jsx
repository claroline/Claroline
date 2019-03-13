import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {FileResource as FileResourceComponent} from '#/main/core/resources/file/components/resource'
import {reducer, selectors} from '#/main/core/resources/file/store'

const FileResource = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        file: selectors.file(state),
        url: selectors.url(state)
      }),
      (dispatch) => ({
        resetForm(formData = null) {
          dispatch(formActions.resetForm(selectors.STORE_NAME+'.fileForm', formData))
        }
      })
    )(FileResourceComponent)
  )
)

export {
  FileResource
}
