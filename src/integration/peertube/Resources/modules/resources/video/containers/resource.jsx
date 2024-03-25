import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {VideoResource as VideoResourceComponent} from '#/integration/peertube/resources/video/components/resource'
import {selectors, reducer} from '#/integration/peertube/resources/video/store'

const VideoResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      video: selectors.video(state)
    }),
    (dispatch) => ({
      resetForm(formData) {
        dispatch(formActions.resetForm(selectors.FORM_NAME, formData))
      }
    })
  )(VideoResourceComponent)
)

export {
  VideoResource
}
